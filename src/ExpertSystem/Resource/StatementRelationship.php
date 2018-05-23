<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 10.05.2018
 * Time: 18:17
 */

namespace ExpertSystem\Resource;

use GraphAware\Neo4j\OGM\Annotations as OGM;

/**
 * @OGM\RelationshipEntity(type="HAS_CHILD_STATEMENT")
 */
class StatementRelationship
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
     * @OGM\Property(type="int")
     * @var int
     */
    protected $supportLevelValue;

    /**
     * @var StatementNode
     *
     * @OGM\EndNode(targetEntity="StatementNode")
     */
    protected $childNode;

    /**
     * @var StatementNode
     *
     * @OGM\StartNode(targetEntity="StatementNode")
     */
    protected $parentNode;

    /**
     * StatementRelationship constructor.
     * @param StatementNode $childNode
     * @param StatementNode $parentNode
     * @param null $value
     * @param null $supportLevelValue
     */
    public function __construct(
        StatementNode $childNode,
        StatementNode $parentNode = null,
        $value = null,
        $supportLevelValue = null
    ) {
        $this->setValue($value);
        $this->setSupportLevelValue($supportLevelValue);
        $this->setChildNode($childNode);
        $this->setParentNode($parentNode);
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
     * @return int
     */
    public function getSupportLevelValue()
    {
        return $this->supportLevelValue;
    }

    /**
     * @param int $supportLevelValue
     */
    public function setSupportLevelValue($supportLevelValue)
    {
        $this->supportLevelValue = $supportLevelValue;
    }

    /**
     * @return StatementNode
     */
    public function getChildNode()
    {
        return $this->childNode;
    }

    /**
     * @param StatementNode $node
     */
    public function setChildNode($node)
    {
        $this->childNode = $node;
    }

    /**
     * @return StatementNode
     */
    public function getParentNode()
    {
        return $this->parentNode;
    }

    /**
     * @param StatementNode $node
     */
    public function setParentNode($node)
    {
        $this->parentNode = $node;
    }
}
