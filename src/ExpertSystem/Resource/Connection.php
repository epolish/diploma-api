<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 10.05.2018
 * Time: 18:17
 */

namespace ExpertSystem\Resource;

use ExpertSystem\ConnectionInterface;
use GraphAware\Neo4j\OGM\EntityManager;

class Connection implements ConnectionInterface
{
    /**
     * @var EntityManager
     */
    private $client;

    /**
     * @return EntityManager
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param array $options
     */
    public function configure($options)
    {
        $this->client = EntityManager::create($options['full_url']);
    }
}
