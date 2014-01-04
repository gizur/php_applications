<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CacheFactoryTest
 *
 * @author prabhat
 */
include __DIR__ . '/../NoSQLFactory.php';

class NoSQLFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testNoSQLClass()
    {
        $cFact = new NoSQLFactory();
        $cIns = $cFact->getInstance();
        
        $this->assertEquals(true, $cIns instanceof AmazonDynamoDBClass);
    }
    
    public function testScan()
    {
        $cFact = new NoSQLFactory();
        $cIns = $cFact->getInstance();
        
        $keysToGet = array('id', 'clientid', 'name_1');
        
        $client = $cIns->scan('GIZUR_ACCOUNTS', $keysToGet, "test");
        
        $this->assertEquals(count($keysToGet), count($client));
    }
}
