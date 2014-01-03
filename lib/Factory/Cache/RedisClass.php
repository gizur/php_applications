<?php

require_once __DIR__ . '/CacheInterface.php';

require_once __DIR__ . '/../../../vendor/predis/predis/autoload.php';

class RedisClass implements CacheInterface
{

    private $redis = null;

    function __construct($server = '127.0.0.1', $port = 6379)
    {
        Predis\Autoloader::register();
        $this->redis = new Predis\Client(array(
            'scheme' => 'tcp',
            'host' => $server,
            'port' => $port,
        ));
    }

    public function get($key)
    {
        $val = $this->redis->get($key);
        return $val ? $val : false;
    }

    public function set($key, $value)
    {
        return $this->redis->set($key, $value);
    }

    public function isExist($key)
    {
        $val = $this->redis->get($key);
        return $val ? true : false;
    }

}
