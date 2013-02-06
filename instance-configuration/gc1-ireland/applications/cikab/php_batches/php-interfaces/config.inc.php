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
 * @copyright  Copyright (c) 2012, Gizur AB, <a href="http://gizur.com">Gizur Consulting</a>, All rights reserved.
 *
 * Coding standards:
 * http://pear.php.net/manual/en/standards.php
 *
 * PHP version 5
 *
 */
/* * *************************************** INTEGRATION DATABASE *********************************** */

/**
 * DNS of database server to use 
 * @global string $dbconfig_integration['db_server']
 */
$dbconfig_integration['db_server'] = 'gc1-mysql1.cjd3zjo5ldyz.eu-west-1.rds.amazonaws.com';

/**
 * The port of the database server
 * @global string $dbconfig_integration['db_port']       
 */
$dbconfig_integration['db_port'] = 3306;

/**
 * The usename to use when logging into the database
 * @global string $dbconfig_integration['db_username']  
 */
$dbconfig_integration['db_username'] = 'vtiger_integrati';

/**
 * The password to use when logging into the database
 * @global string $dbconfig_integration['db_password']
 */
$dbconfig_integration['db_password'] = 'ALaXEryCwSFyW5jQ';

/**
 * The name of the database
 * @global string $dbconfig_integration['db_name']
 */
$dbconfig_integration['db_name'] = 'vtiger_integration';

/**
 * The type of database (currently is only mysql supported)
 * @global string $dbconfig_integration['db_type']
 */
$dbconfig_integration['db_type'] = 'mysql';


/* * *************************************** VTIGER DATABASE *********************************** */


/**
 * DNS of database server to use 
 * @global string $dbconfig_vtiger['db_server']
 */
$dbconfig_vtiger['db_server'] = 'gc1-mysql1.cjd3zjo5ldyz.eu-west-1.rds.amazonaws.com';

/**
 * The port of the database server
 * @global string $dbconfig_vtiger['db_port']       
 */
$dbconfig_vtiger['db_port'] = 3306;

/**
 * The usename to use when logging into the database
 * @global string $dbconfig_vtiger['db_username']  
 */
$dbconfig_vtiger['db_username'] = 'user_2059ff6a';

/**
 * The password to use when logging into the database
 * @global string $dbconfig_vtiger['db_password']
 */
$dbconfig_vtiger['db_password'] = 'c059ff6a3f05';

/**
 * The name of the database
 * @global string $dbconfig_vtiger['db_name']
 */
$dbconfig_vtiger['db_name'] = 'vtiger_5159ff6a';

/**
 * The type of database (currently is only mysql supported)
 * @global string $dbconfig_vtiger['db_type']
 */
$dbconfig_vtiger['db_type'] = 'mysql';



/* * ************************** BATCH CONFIGURATION *************************** */

/**
 *  Set Batch Valiable
 * 
 * 
 */
$dbconfig_batchvaliable['batch_valiable'] = 10;

/* * *************************** FTP CONFIGURATION **************************** */


/**
 *  @FTP Host Name 
 */
$dbconfig_ftphost['Host'] = "ftp-hp.coop.se";

/**
 *  @FTP User Name 
 */
$dbconfig_ftpuser['User'] = "ftpSETGizur";


/**
 *  @FTP Password
 */
$dbconfig_ftppassword['Password'] = "Sk4nsk4113";

/**
 *  @FTP Local files path
 */
$dbconfig_ftplocalpath['localpath'] = "cronsetfiles/";

/**
 *  @FTP Server files path
 */
$dbconfig_ftpserverpath['serverpath'] = "in/";


/** * ******************* Amazon SQS Configuration ********************** * */
/**
 * Queue URL
 */
$amazonqueue_config['_url'] = 'https://sqs.eu-west-1.amazonaws.com/791200854364/cikab_queue';
?>
