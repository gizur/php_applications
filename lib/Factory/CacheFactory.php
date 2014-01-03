<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Cache
 *
 * @author prabhat
 */
require_once __DIR__ . '/FactoryConfig.php';
require_once __DIR__ . '/Cache/MemcacheClass.php';
require_once __DIR__ . '/Cache/RedisClass.php';

class CacheFactory
{

    function __construct()
    {
        
    }

    public function getInstance()
    {
        switch (FactoryConfig::$driversInUse['Cache']) {
            case 'Redis':
                return (new RedisClass('localhost', 6379));
                break;
            case 'Memcache':
                return (new MemcacheClass('localhost', 11211));
                break;
        }
    }

}

?>
