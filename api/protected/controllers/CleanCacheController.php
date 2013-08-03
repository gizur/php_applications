<?php

/**
 * Yii Controller to clean cache
 * 
 * PHP version 5
 *
 * @category   Controller
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

spl_autoload_unregister(array('YiiBase', 'autoload'));
Yii::import('application.vendors.*');
require_once 'aws-php-sdk/sdk.class.php';
spl_autoload_register(array('YiiBase', 'autoload'));

class CacheCleanController extends Controller
{

    /**
     * Filters executable action on permission basis
     * 
     * @return array action filters
     */
    public function filters()
    {
        return array();
    }

    public function beforeAction($action)
    {
            
    }
    
    public function actionRemove()
    {
        $ec2 = new AmazonEC2();
        $ec2->set_region(constant("AmazonEC2::" . Yii::app()->params->awsDynamoDBRegion));
        
        print_r($ec2->describe_instances());
    }
}