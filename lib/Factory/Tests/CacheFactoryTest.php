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
include __DIR__ . '/../CacheFactory.php';

class CacheFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testRedisClass()
    {
        $cFact = new CacheFactory();
        $cIns = $cFact->getInstance();
        
        $this->assertEquals(true, $cIns instanceof RedisClass);
    }
    
    public function testSet()
    {
        $cFact = new CacheFactory();
        $cIns = $cFact->getInstance();
        
        $this->assertEquals(true, $cIns->set("prabhat", "khera"));
    }
    
    public function testGet()
    {
        $cFact = new CacheFactory();
        $cIns = $cFact->getInstance();
        
        $val = $cIns->get("prabhat");
        
        $this->assertEquals("khera", $val);
    }
}
