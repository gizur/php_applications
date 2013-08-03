<?php

class Memcache
{

    private $memcache_url = '10.58.226.192';
    private static $memcacheObj = null;

    public function __construct()
    {
        $this->memcache = new Memcache;
        self::$memcacheObj = $this->memcache->connect($this->memcache_url, 11211);
    }

    public static function getMemcache()
    {
        return self::$memcacheObj;
    }

}

?>
