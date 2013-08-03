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

require_once 'aws-php-sdk/sdk.class.php';

class CacheClean
{
    public function __construct()
    {
        
    }


    public function remove()
    {
        $ec2 = new AmazonEC2();
        $ec2->set_region(constant("AmazonEC2::" . Yii::app()->params->awsDynamoDBRegion));

        print_r($ec2->describe_instances());
    }

}

$cc = new CacheClean();
$cc->remove();