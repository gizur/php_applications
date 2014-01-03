<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of NoSQL
 *
 * @author prabhat
 */

require_once __DIR__ . '/FactoryConfig.php';
require_once __DIR__ . '/NoSQL/AmazonDynamoDBClass.php';
require_once __DIR__ . '/NoSQL/RedisNoSQLClass.php';

class NoSQLFactory
{
    function __construct()
    {
        
    }

    public function getInstance()
    {
        switch (FactoryConfig::$driversInUse['NoSQL']) {
            case 'Redis':
                return (new RedisNoSQLClass(
                    FactoryConfig::$params[FactoryConfig::$driversInUse['NoSQL']]['host'], 
                    FactoryConfig::$params[FactoryConfig::$driversInUse['NoSQL']]['port'])
                );
                break;
            case 'Memcache':
                return (new AmazonDynamoDBClass());
                break;
        }
    }
}

?>
