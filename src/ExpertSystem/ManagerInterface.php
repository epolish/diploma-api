<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 10.05.2018
 * Time: 18:17
 */

namespace ExpertSystem;

use ExpertSystem\Resource\StatementNode;
use ExpertSystem\Resource\StatementRelationship;

interface ManagerInterface
{
    /**
     * @return StatementNode
     */
    public function getRootNode();

    /**
     * @return StatementNode[]
     */
    public function getAllNodes();

    /**
     * @param string $value
     * @return StatementNode
     */
    public function getNode($value);

    /**
     * @param string $value
     * @return StatementNode
     */
    public function getParentNode($value);

    /**
     * @param string $value
     * @return StatementRelationship
     */
    public function getRelationship($value);

    /**
     * @param $value
     * @return mixed
     */
    public function clearNodeParentLink($value);

    /**
     * @param $startValue
     * @param $endValue
     * @return mixed
     */
    public function traceRoot($startValue, $endValue);

    /**
     * @param string $oldValue
     * @param string $newValue
     * @return mixed
     */
    public function updateNodeValue($oldValue, $newValue);

    /**
     * @param string $value
     * @param string|bool $withChildren
     * @return mixed
     */
    public function removeNode($value, $withChildren = false);

    /**
     * @param string $value
     * @param string $newParentValue
     * @param string $parentRelationshipValue
     * @param string $parentRelationSupportLevelValue
     * @return mixed
     */
    public function updateNodeLink(
        $value,
        $newParentValue,
        $parentRelationshipValue,
        $parentRelationSupportLevelValue
    );

    /**
     * @param string $value
     * @param string|null $parentValue
     * @param string|null $parentRelationshipValue
     * @param string|null $parentRelationSupportLevelValue
     * @return mixed
     */
    public function createNode(
        $value,
        $parentValue = null,
        $parentRelationshipValue = null,
        $parentRelationSupportLevelValue = null
    );
}
