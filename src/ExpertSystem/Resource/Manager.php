<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 10.05.2018
 * Time: 18:17
 */

namespace ExpertSystem\Resource;

use ExpertSystem\ManagerInterface;
use ExpertSystem\ConnectionInterface;
use GraphAware\Neo4j\OGM\EntityManager;
use GraphAware\Neo4j\OGM\Repository\BaseRepository;
use ExpertSystem\Exception\StatementNodeDoesNotExistException;
use ExpertSystem\Exception\StatementNodeAlreadyExistsException;
use ExpertSystem\Exception\StatementNodeHasChildNodesException;
use ExpertSystem\Exception\StatementRootNodeCannotBeMovedException;
use ExpertSystem\Exception\StatementRootNodeAlreadyExistsException;
use ExpertSystem\Exception\StatementRelationshipHasNoValueException;
use ExpertSystem\Exception\StatementNodeDisconnectedFromRootException;
use ExpertSystem\Exception\StatementRelationshipHasNoSupportLevelValueException;

class Manager implements ManagerInterface
{
    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * Manager destructor.
     */
    public function __destruct()
    {
        $this->getClient()->flush();
    }

    /**
     * @return EntityManager
     */
    public function getClient()
    {
        return $this->connection->getClient();
    }

    /**
     * @param ConnectionInterface $connection
     */
    public function setConnection(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return BaseRepository
     */
    public function getRepository()
    {
        return $this->connection->getClient()
            ->getRepository(StatementNode::class);
    }

    /**
     * @param array|null $orderBy
     * @return array|StatementNode[]
     */
    public function getAllNodes($orderBy = null)
    {
        return $this->getRepository()->findBy([], $orderBy);
    }

    /**
     * @param string $value
     * @return StatementNode|null|object
     * @throws StatementNodeDoesNotExistException
     */
    public function getNode($value)
    {
        $node = $this->getRepository()->findOneBy([
            'value' => $value
        ]);

        if (!$node) {
            throw new StatementNodeDoesNotExistException(
                "Statement [$value] does not exists."
            );
        }

        return $node;
    }

    /**
     * @param string $value
     * @return StatementNode|null|object
     */
    public function getParentNode($value)
    {
        $alias = 'p';
        $query = $this->getClient()->createQuery(
            "MATCH (n)<-[]-($alias) WHERE n.value = {value} RETURN $alias"
        );

        $query->setParameter('value', $value);
        $query->addEntityMapping($alias, StatementNode::class);

        $result = $query->getOneOrNullResult();

        return is_array($result) ? reset($result) : $result;
    }

    /**
     * @param string $value
     * @return StatementRelationship|null|object
     * @throws StatementNodeDoesNotExistException
     */
    public function getRelationship($value)
    {
        $relationValue = null;
        $parentRelationSupportLevelValue = null;
        $childNode = $this->getNode($value);
        $parentNode = $this->getParentNode($value);

        if ($parentNode) {
            foreach ($parentNode->getChildNodes() as $childRelationship) {
                if ($childRelationship->getChildNode()->getValue() == $childNode->getValue()) {
                    $relationValue = $childRelationship->getValue();
                    $parentRelationSupportLevelValue = $childRelationship->getSupportLevelValue();
                }
            }
        }

        return new StatementRelationship(
            $childNode,
            $parentNode,
            $relationValue,
            $parentRelationSupportLevelValue
        );
    }

    /**
     * @param string $oldValue
     * @param string $newValue
     * @return mixed|void
     * @throws StatementNodeAlreadyExistsException
     * @throws StatementNodeDoesNotExistException
     */
    public function updateNodeValue($oldValue, $newValue)
    {
        $node = $this->getNode($oldValue);

        $this->validateNode($newValue);

        $node->setValue($newValue);
    }

    /**
     * @param $value
     * @throws \Exception
     */
    public function clearNodeParentLink($value)
    {
        $query = $this->getClient()->createQuery(
            'MATCH (n)<-[r]-() WHERE n.value = {value} DELETE r'
        );

        $query->setParameter('value', $value);
        $query->execute();
    }

    /**
     * @param string $value
     * @param string $newParentValue
     * @param string $parentRelationshipValue
     * @param string $parentRelationSupportLevelValue
     * @return mixed|void
     * @throws StatementNodeDoesNotExistException
     * @throws \Exception
     */
    public function updateNodeLink(
        $value,
        $newParentValue,
        $parentRelationshipValue,
        $parentRelationSupportLevelValue
    ) {
        if (!$parentRelationshipValue) {
            throw new StatementRelationshipHasNoValueException();
        }

        if (!is_int($parentRelationSupportLevelValue)) {
            throw new StatementRelationshipHasNoSupportLevelValueException();
        }

        $node = $this->getNode($value);
        $rootNode = $this->getRootNode();
        $parentNode = $this->getParentNode($value);
        $newParentNode = $this->getNode($newParentValue);

        if ($value == $rootNode->getValue()) {
            throw new StatementRootNodeCannotBeMovedException();
        }

        $this->clearNodeParentLink($value);
        $newParentNode->addChildNode(
            $node,
            $parentRelationshipValue,
            $parentRelationSupportLevelValue
        );
        $this->getClient()->flush();

        if (!$this->traceRoot($rootNode->getValue(), $value)) {
            $this->clearNodeParentLink($value);
            $parentNode->addChildNode(
                $node,
                $parentRelationshipValue,
                $parentRelationSupportLevelValue
            );
            $this->getClient()->flush();

            throw new StatementNodeDisconnectedFromRootException();
        }
    }

    /**
     * @param $startValue
     * @param $endValue
     * @return array|mixed
     * @throws \Exception
     */
    public function traceRoot($startValue, $endValue)
    {
        $query = $this->getClient()->createQuery(
            'MATCH n = (s)-[*]->(e) WHERE s.value = {start_value} '.
                'AND e.value = {end_value} RETURN n'
        );

        $query->setParameter('end_value', $endValue);
        $query->setParameter('start_value', $startValue);

        return $query->execute();
    }

    /**
     * @return StatementNode|mixed
     */
    public function getRootNode()
    {
        $alias = 'n';
        $query = $this->getClient()->createQuery(
            "MATCH ($alias) WHERE NOT ($alias)<-[]-() RETURN $alias"
        );

        $query->addEntityMapping($alias, StatementNode::class);

        $result = $query->getOneOrNullResult();

        return is_array($result) ? reset($result) : $result;
    }

    /**
     * @param string $value
     * @param bool $withChildren
     * @return mixed|void
     * @throws StatementNodeDoesNotExistException
     * @throws StatementNodeHasChildNodesException
     */
    public function removeNode($value, $withChildren = false)
    {
        $node = $value instanceof StatementNode ? $value : $this->getNode($value);

        if (!$withChildren && $node->getChildNodes()) {
            throw new StatementNodeHasChildNodesException();
        }

        if ($withChildren) {
            foreach ($node->getChildNodes() as $childNodeRelationship) {
                $childNode = $childNodeRelationship->getChildNode();

                if ($childNode->getChildNodes()) {
                    $this->removeNode($childNode, $withChildren);
                }

                $this->getClient()->remove($childNode, true);
            }
        }

        $this->getClient()->remove($node, true);
    }

    /**
     * @param string $value
     * @param null $parentValue
     * @param null $parentRelationshipValue
     * @param null $parentRelationSupportLevelValue
     * @return mixed|void
     * @throws StatementNodeAlreadyExistsException
     * @throws StatementNodeDoesNotExistException
     * @throws StatementRootNodeAlreadyExistsException
     * @throws \Exception
     */
    public function createNode(
        $value,
        $parentValue = null,
        $parentRelationshipValue = null,
        $parentRelationSupportLevelValue = null
    ) {
        if (!$parentValue && $this->getRootNode()) {
            throw new StatementRootNodeAlreadyExistsException();
        }

        if ($parentValue && !$parentRelationshipValue) {
            throw new StatementRelationshipHasNoValueException();
        }

        if ($parentValue && !is_int($parentRelationSupportLevelValue)) {
            throw new StatementRelationshipHasNoSupportLevelValueException();
        }

        $this->validateNode($value);

        $node = new StatementNode($value);

        if ($parentValue) {
            $parentNode = $this->getNode($parentValue);

            $parentNode->addChildNode(
                $node,
                $parentRelationshipValue,
                $parentRelationSupportLevelValue
            );
        }

        $this->getClient()->persist($node);
    }

    /**
     * @param $url
     * @param $options
     * @return array|mixed
     * @throws \Exception
     */
    public function import($url, $options)
    {
        if (!$options['append_mode']) {
            $query = $this->getClient()->createQuery('MATCH (n) DETACH DELETE n');

            $query->execute();
        }

        $query = $this->getClient()->createQuery(
            'LOAD CSV WITH HEADERS FROM {url} AS line ' .
            'MERGE (n:Statement {value:line.statement_value}) ' .
            'MERGE (p:Statement {value:line.parent_statement_value}) ' .
            'MERGE (p)-[:HAS_CHILD_STATEMENT {value:line.parent_relationship_value, '.
                'supportLevelValue:line.parent_relationship_support_level_value}]->(n)'
        );

        $query->setParameter('url', $url);

        $query->execute();

        $query = $this->getClient()->createQuery(
            'MATCH (n:Statement {value:"\'\'"}) DETACH DELETE n'
        );

        return $query->execute();
    }

    /**
     * @return array|mixed
     * @throws \Exception
     */
    public function getAllNodesValuesWithParents()
    {
        $query = $this->getClient()->createQuery(
            'MATCH (n) OPTIONAL MATCH (n)<-[]-(m) RETURN ' .
                'n.value AS statement_value, ' .
                'm.value AS parent_statement_value'
        );

        return $query->execute();
    }

    /**
     * @param $value
     * @throws StatementNodeAlreadyExistsException
     */
    public function validateNode($value)
    {
        $node = null;

        try {
            $node = $this->getNode($value);
        } catch (StatementNodeDoesNotExistException $ex) {

        }

        if ($node) {
            throw new StatementNodeAlreadyExistsException();
        }
    }
}
