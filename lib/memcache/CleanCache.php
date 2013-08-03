<?php

/**
 * Class to clean cache
 * 
 * PHP version 5
 * 
 * @package    GizurCloud
 * @subpackage Controller
 * @author     Prabhat Khera <prabhat.khera@essindia.co.in>
 * 
 * @license    Gizur Private Licence
 * @link       http://api.gizur.com/api/index.php
 * 
 * */
/*
 * Including Amazon classes
 */

ini_set('display_errors', 'On');
error_reporting(E_ALL);
require_once '../aws-php-sdk/sdk.class.php';
require_once './config.php';

class CleanCache
{

    private $keyToDelete;
    private $memcache = null;

    public function __construct($keys)
    {
        $this->keyToDelete = $keys;
        $this->memcache = new Memcache;
        $this->memcache->connect(MemcacheG::$memcache_url, MemcacheG::$port);
    }

    public function remove()
    {
        $ec2 = new AmazonEC2();
        $ec2->set_region(AmazonEC2::REGION_EU_W1);
        $result = $ec2->describe_instances();
        $res = array();
        
        if ($result->status === 200) {
            $items = $result->body->reservationSet;
            foreach ($items->item as $item) {
                $instanceId = (string) $item->instancesSet->item->instanceId;

                foreach ($this->keyToDelete as $key) {
                    $key = str_replace("INSTANCE_ID", $instanceId, $key);
                    if ($this->memcache) {
                        $this->memcache->delete($key);
                        $res[] = $key . " deleted";
                    }
                }
            }
        }
        return json_encode($res);
    }

}

if (isset($_GET['keys'])) {
    $cc = new CleanCache($_GET['keys']);
    echo $cc->remove();
} else {
    echo "KEYS NOT EXIST.";
}