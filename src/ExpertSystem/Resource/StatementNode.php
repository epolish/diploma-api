<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 10.05.2018
 * Time: 18:17
 */

namespace ExpertSystem\Resource;

use GraphAware\Neo4j\OGM\Common\Collection;
use GraphAware\Neo4j\OGM\Annotations as OGM;
use ExpertSystem\Exception\StatementRelationshipHasNoValueException;

/**
 * @OGM\Node(label="Statement")
 */
class StatementNode
{
    /**
     * @OGM\GraphId()
     * @var int
     */
    protected $id;

    /**
     * @OGM\Property(type="string")
     * @var string
     */
    protected $value;

    /**
     * @var StatementNode[]|Collection
     *
     * @OGM\Relationship(
     *     relationshipEntity="StatementRelationship",
     *     type="HAS_CHILD_STATEMENT",
     *     direction="OUTGOING",
     *     collection=true,
     *     mappedBy="childNodes",
     *     targetEntity="StatementNode"
     * )
     */
    protected $childNodes;

    /**
     * @param string|null $value
     */
    public function __construct($value = null)
    {
        $this->setValue($value);
        $this->setChildNodes(new Collection());
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return StatementRelationship[]|Collection
     */
    public function getChildNodes()
    {
        return $this->childNodes;
    }

    /**
     * @param Collection $nodes
     */
    public function setChildNodes($nodes)
    {
        $this->childNodes = $nodes;
    }

    /**
     * @param StatementNode $childNode
     * @param string $relationValue
     * @throws StatementRelationshipHasNoValueException
     */
    public function addChildNode($childNode, $relationValue)
    {
        if (!$relationValue) {
            throw new StatementRelationshipHasNoValueException();
        }

        $this->getChildNodes()->add(
            new StatementRelationship($childNode, $this, $relationValue)
        );
    }
}
