<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 10.05.2018
 * Time: 18:17
 */

namespace ExpertSystem;

use GraphAware\Neo4j\OGM\EntityManager;

interface ConnectionInterface
{
    /**
     * @return EntityManager
     */
    public function getClient();
}
