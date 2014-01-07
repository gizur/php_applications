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

        $this->assertEquals(true, $cIns instanceof RedisNoSQLClass);
    }

    public function testScan()
    {
        $cFact = new NoSQLFactory();
        $cIns = $cFact->getInstance();

        $keysToGet = array('id', 'clientid', 'name_1');

        $client = $cIns->scan('GIZUR_ACCOUNTS', $keysToGet, "test");

        $this->assertEquals(count($keysToGet), count($client));
    }

    public function testGetItem()
    {
        $cFact = new NoSQLFactory();
        $cIns = $cFact->getInstance();

        $keysToGet = array('id', 'clientid', 'name_1');

        $client = $cIns->get_item('GIZUR_ACCOUNTS', $keysToGet, 'id', "test");

        $this->assertEquals(count($keysToGet), count($client));
    }
    
    public function testCreate()
    {
        $cFact = new NoSQLFactory();
        $cIns = $cFact->getInstance();

        $toSet = array(
            'name_1' => 'prabhat',
            'name_2' => 'khera',
            'id' => 'prabhat@prabhat.com',
            'clientid' => 'prabhat1'
        );

        $result = $cIns->create('GIZUR_ACCOUNTS', $toSet['clientid'], $toSet);

        $this->assertEquals(true, $result);
    }

}
