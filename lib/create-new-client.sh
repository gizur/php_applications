#!/usr/bin/php

<?php

/**
 * This file contains common functions used throughout the Integration package.
 *
 * @package    php_applications
 * @subpackage setup
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


include("vtiger-5.4.0/config.inc.php");
require_once 'MDB2.php';

/**
 *  The Pear PHP dagtabase API MDB2 will is used
 *  http://pear.php.net/manual/en/package.database.mdb2.php
 */


/**
 * Database connection string
 * @global string $dsn
 *
 * Example 'mysql://root:mysecret@localhost/mysql'
 */
$dsn = "mysql://" . $dbconfig['db_username'] . ":" . $dbconfig['db_password'] . "@" . $dbconfig['db_server'] . $dbconfig['db_port'] . "/" . $dbconfig['db_name'];


/**
 * Database connection options
 * @global string $options
 */
$options = array(
    'persistent' => true,
);

/**
 * Database MDB2 connection object 
 * @global mixed $mdb2
 */
$mdb2 =& MDB2::factory($dsn, $options);

if (PEAR::isError($mdb2)) {
    echo ($mdb2->getMessage().' - '.$mdb2->getUserinfo());
}


/**
 * Create the saleorder_interface table 
 *
 * @param mixed $mdb2
 * @return int
 */
function createTable($mdb2) {

   /**
    * First drop the table if it exists
    */
    $query = <<<EOT
        DROP TABLE IF EXISTS `salesorder_interface` ;
EOT;

    // Execute the query
    $result = $mdb2->exec($query);

    // check if the query was executed properly
    if (PEAR::isError($result)) {
        echo ($result->getMessage().' - '.$result->getUserinfo());
        exit();
    }
    
    /**
    * First drop the table if it exists
    */
    
    $query2 = <<<EOT
        DROP TABLE IF EXISTS `saleorder_msg_que` ;
EOT;

    // Execute the query
    $result = $mdb2->exec($query2);

    // check if the query was executed properly
    if (PEAR::isError($result)) {
        echo ($result->getMessage().' - '.$result->getUserinfo());
        exit();
    }

   /**
    * Then create the table
    */
    $query = <<<EOT
        CREATE TABLE `salesorder_interface` (
                     `id` int(19) NOT NULL AUTO_INCREMENT,
                     `salesorderid` int(19) NOT NULL DEFAULT '0',
                     `salesorder_no` varchar(100) DEFAULT NULL,
                     `contactid` int(19) DEFAULT NULL,
                     `productname` varchar(100) DEFAULT NULL,
                     `productid` int(11) DEFAULT NULL,
                     `productquantity` int(5) DEFAULT NULL,
                     `duedate` date DEFAULT NULL,
                     `featurdate` date DEFAULT NULL,
                     `accountname` varchar(100) DEFAULT NULL,
                     `accountid` int(19) DEFAULT NULL,
                     `sostatus` varchar(200) DEFAULT NULL,
                     `batchno` varchar(20) NOT NULL,
                     `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
EOT;

    // Execute the query
    $result = $mdb2->exec($query);

    // check if the query was executed properly
    if (PEAR::isError($result)) {
        echo ($result->getMessage().' - '.$result->getUserinfo());
        exit();
    }
    
    /**
    * Then create the table saleorder_msg_que
    */
    $query2 = <<<EOT
        CREATE TABLE `saleorder_msg_que` (
                     `id` int(19) NOT NULL AUTO_INCREMENT,
                     `accountname` varchar(100) DEFAULT NULL,
                     `ftpfilename` varchar (200) DEFAULT NULL,
                     `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                     `status` int(1) DEFAULT '0',
        PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
EOT;

    // Execute the query
    $result = $mdb2->exec($query2);

    // check if the query was executed properly
    if (PEAR::isError($result)) {
        echo ($result->getMessage().' - '.$result->getUserinfo());
        exit();
    }
   

    // Disconnect from the database
    $mdb2->disconnect();

    return 0;
}


// Run the query
$result = createTable($mdb2);

print "Table created successfully!\n";

?>
