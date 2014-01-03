<?php

require_once __DIR__ . '/CacheInterface.php';

class MemcacheClass implements CacheInterface
{

    private $memcache = null;
    private $connect = null;
    
    function __construct($server = 'localhost', $port = 11211)
    {

        $this->memcache = new Memcache;
        $this->connect = $this->memcache->connect($server, $port);
    }

    public function get($key)
    {
        if ($this->connect) {
            return $this->memcache->get($key);
        } else {
            return false;
        }
    }

    public function set($key, $value)
    {
        if ($this->connect) {
            return $this->memcache->set($key, $value);
        } else {
            return false;
        }
    }

    public function isExist($key)
    {
        return true;
    }

}

?>
