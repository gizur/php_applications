<?php

/**
 * This file contains common functions used throughout the Integration package.
 *
 * @package    Integration
 * @subpackage Config
 * @author     Jonas Colmsjö <jonas.colmsjo@gizur.com>
 * @version    SVN: $Id$
 *
 * @license    Commercial license
 * @copyright  Copyright (c) 2012, Gizur AB, 
 * <a href="http://gizur.com">Gizur Consulting</a>, All rights reserved.
 *
 * Coding standards:
 * http://pear.php.net/manual/en/standards.php
 *
 * PHP version 5
 *
 */
/* * *************** INTEGRATION DATABASE ******************** */

/**
 * DNS of database server to use 
 * @global string $dbconfigIntegration['db_server']
 */
$dbconfigIntegration['db_server'] = 'gizurcloud.colm85rhpnd4.eu-west-1.' .
    'rds.amazonaws.com';

/**
 * The port of the database server
 * @global string $dbconfigIntegration['db_port']       
 */
$dbconfigIntegration['db_port'] = 3306;

/**
 * The usename to use when logging into the database
 * @global string $dbconfigIntegration['db_username']  
 */
$dbconfigIntegration['db_username'] = 'vtiger_integrati';

/**
 * The password to use when logging into the database
 * @global string $dbconfigIntegration['db_password']
 */
$dbconfigIntegration['db_password'] = 'ALaXEryCwSFyW5jQ';

/**
 * The name of the database
 * @global string $dbconfigIntegration['db_name']
 */
$dbconfigIntegration['db_name'] = 'vtiger_integration';

/**
 * The type of database (currently is only mysql supported)
 * @global string $dbconfigIntegration['db_type']
 */
$dbconfigIntegration['db_type'] = 'mysql';


/* * **************** VTIGER DATABASE *************** */


/**
 * DNS of database server to use 
 * @global string $dbconfigVtiger['db_server']
 */
$dbconfigVtiger['db_server'] = 'gizurcloud.colm85rhpnd4.eu-west-1.' .
    'rds.amazonaws.com';

/**
 * The port of the database server
 * @global string $dbconfigVtiger['db_port']       
 */
$dbconfigVtiger['db_port'] = 3306;

/**
 * The usename to use when logging into the database
 * @global string $dbconfigVtiger['db_username']  
 */
$dbconfigVtiger['db_username'] = 'user_6bd70dc3';

/**
 * The password to use when logging into the database
 * @global string $dbconfigVtiger['db_password']
 */
$dbconfigVtiger['db_password'] = 'fbd70dc30c05';

/**
 * The name of the database
 * @global string $dbconfigVtiger['db_name']
 */
$dbconfigVtiger['db_name'] = 'vtiger_7cd70dc3';

/**
 * The type of database (currently is only mysql supported)
 * @global string $dbconfigVtiger['db_type']
 */
$dbconfigVtiger['db_type'] = 'mysql';



/* * ************* BATCH CONFIGURATION ************* */

/**
 *  Set Batch Variable
 * 
 * 
 */
$dbconfigBatchVariable['batch_variable'] = 10;

/* * *************** FTP CONFIGURATION ************* */


/**
 *  @FTP Host Name 
 */
$dbconfigFtp['Host'] = "10.58.226.192";

/**
 *  @FTP Host Port 
 */
$dbconfigFtp['port'] = 21;

/**
 *  @FTP User Name 
 */
$dbconfigFtp['User'] = "gizur";


/**
 *  @FTP Password
 */
$dbconfigFtp['Password'] = "gizur";

/**
 *  @FTP Local files path
 */
$dbconfigFtp['localpath'] = "cronsetfiles/";

/**
 *  @FTP Server files path
 */
$dbconfigFtp['serverpath'] = "files/";


/** * ******************* Amazon SQS Configuration ********************** * */
/**
 * Queue URL
 */
$amazonqueueConfig['_url'] = 'https://sqs.eu-west-1.amazonaws.com/' .
    '065717488322/cikab_queue';

/*
 * Amazon S3 Bucket
 */
$amazonSThree['bucket'] = "gc3-archive";
$amazonSThree['fileFolder'] = "seasonportal/SET-files/";

class Config
{

    public static $dbIntegration = array(
        'db_server' => 'gizurcloud.colm85rhpnd4.eu-west-1.rds.amazonaws.com',
        'db_port' => 3306,
        'db_username' => 'vtiger_integrati',
        'db_password' => 'ALaXEryCwSFyW5jQ',
        'db_name' => 'vtiger_integration',
        'db_type' => 'mysql'
    );
    public static $dbVtiger = array(
        'db_server' => 'gizurcloud.colm85rhpnd4.eu-west-1.rds.amazonaws.com',
        'db_port' => 3306,
        'db_username' => 'user_6bd70dc3',
        'db_password' => 'fbd70dc30c05',
        'db_name' => 'vtiger_7cd70dc3',
        'db_type' => 'mysql'
    );
    public static $batchVariable = 10;
    public static $setFtp = array(
        'host' => "10.58.226.192",
        'port' => 21,
        'username' => "gizur",
        'password' => "gizur",
        'serverpath' => "files/"
    );
    public static $mosFtp = array(
        'host' => "10.58.226.192",
        'port' => 21,
        'username' => "gizur",
        'password' => "gizur",
        'serverpath' => "files/"
    );
    public static $amazonQ = array(
        'url' => 'https://sqs.eu-west-1.amazonaws.com/065717488322/cikab_queue'
    );
    public static $amazonSThree = array(
        'setBucket' => "gc3-archive",
        'setFolder' => "seasonportal/SET-files/",
        'mosBucket' => "gc3-archive",
        'mosFolder' => "seasonportal/SET-files/"
    );
    public static $customFields = array(
        'setFiles' => 'cf_664',
        'mosFiles' => 'cf_665',
        'basProductId' => 'cf_666'
    );
    public static $lineBreak = '\n';

}