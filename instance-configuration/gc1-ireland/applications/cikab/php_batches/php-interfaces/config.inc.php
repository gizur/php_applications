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
/* * **************** INTEGRATION DATABASE *********** */

/**
 * DNS of database server to use 
 * @global string $dbconfigIntegration['db_server']
 */
$dbconfigIntegration['db_server'] = 'gc1-mysql1.cjd3zjo5ldyz.eu-west-1.' . 
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


/* * ******************** VTIGER DATABASE ********************** */


/**
 * DNS of database server to use 
 * @global string $dbconfigVtiger['db_server']
 */
$dbconfigVtiger['db_server'] = 'gc1-mysql1.cjd3zjo5ldyz.eu-west-1.' . 
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
$dbconfigVtiger['db_username'] = 'user_2059ff6a';

/**
 * The password to use when logging into the database
 * @global string $dbconfigVtiger['db_password']
 */
$dbconfigVtiger['db_password'] = 'c059ff6a3f05';

/**
 * The name of the database
 * @global string $dbconfigVtiger['db_name']
 */
$dbconfigVtiger['db_name'] = 'vtiger_5159ff6a';

/**
 * The type of database (currently is only mysql supported)
 * @global string $dbconfigVtiger['db_type']
 */
$dbconfigVtiger['db_type'] = 'mysql';



/* * ***************** BATCH CONFIGURATION **************** */

/**
 *  Set Batch Valiable
 * 
 * 
 */
$dbconfigBatchVariable['batch_valiable'] = 10;

/* * ********** FTP CONFIGURATION ************ */


/**
 *  @FTP Host Name 
 */
$dbconfigFtp['Host'] = "ftp-hp.coop.se";

/**
 *  @FTP User Name 
 */
$dbconfigFtp['User'] = "ftpSETGizur";


/**
 *  @FTP Password
 */
$dbconfigFtp['Password'] = "Sk4nsk4113";

/**
 *  @FTP Local files path
 */
$dbconfigFtp['localpath'] = "cronsetfiles/";

/**
 *  @FTP Server files path
 */
$dbconfigFtp['serverpath'] = "in/";


/** * ******************* Amazon SQS Configuration ********************** * */
/**
 * Queue URL
 */
$amazonqueueConfig['_url'] = 'https://sqs.eu-west-1.amazonaws.com/' . 
    '791200854364/cikab_queue';
