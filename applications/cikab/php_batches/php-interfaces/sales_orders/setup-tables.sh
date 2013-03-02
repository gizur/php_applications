#!/usr/bin/php

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
include("../config.inc.php");

/**
 * Connect to MySQL database. 
 */
$_mysqli = new mysqli($dbconfigIntegration['db_server'],
        $dbconfigIntegration['db_username'],
        $dbconfigIntegration['db_password'],
        $dbconfigIntegration['db_name'],
        $dbconfigIntegration['db_port']);

/**
 * Print error message in case of connection error.
 */
if ($_mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" .
    $_mysqli->connect_errno . ") " . $_mysqli->connect_error;
    exit();
}else{
    echo "Connected with MySQL : " . $dbconfigIntegration['db_server'] . '\n';
}

/**
 * Create the saleorder_interface table 
 *
 * @param mixed $_mysqli
 * @return int
 */
function createTable(&$mysqli)
{

    echo "In createTable function.\n";
    /**
     * First drop the table if it exists
     */
    $query = "DROP TABLE IF EXISTS `salesorder_interface`";

    // Execute the query
    $result = $mysqli->query($query);
    
    // check if the query was executed properly
    if ($result !== TRUE) {
        echo ($result . ' : ' . $mysqli->error);
        exit();
    }

    /**
     * First drop the table if it exists
     */
    $query2 = "DROP TABLE IF EXISTS `saleorder_msg_que`";

    // Execute the query
    $result = $mysqli->query($query2);

    // Check if the query was executed properly
    if ($result !== TRUE) {
        echo ($result . ' : ' . $mysqli->error);
        exit();
    }

    /**
     * Then create the table
     */
    $query = "CREATE TABLE `salesorder_interface` (
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
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

    // Execute the query
    $result = $mysqli->query($query);

    // check if the query was executed properly
    if ($result !== TRUE) {
        echo ($result . ' : ' . $mysqli->error);
        exit();
    }

    /**
     * Then create the table saleorder_msg_que
     */
    $query2 = "CREATE TABLE `saleorder_msg_que` (
                     `id` int(19) NOT NULL AUTO_INCREMENT,
                     `accountname` varchar(100) DEFAULT NULL,
                     `ftpfilename` varchar (200) DEFAULT NULL,
                     `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                     `status` int(1) DEFAULT '0',
        PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

    // Execute the query
    $result = $mysqli->query($query2);

    // check if the query was executed properly
    if ($result !== TRUE) {
        echo ($result . ' : ' . $mysqli->error);
        exit();
    }
    return 0;
}

// Call the function to crete the tables
$result = createTable($_mysqli);

// Close the connnection
$_mysqli->close();

print "Table created successfully!\n";
?>
