#!/usr/bin/php
<?php
/**
 * @category   Cronjobs
 * @package    Reports
 * @subpackage SalesORders
 * @author     Jonas Colmsjö <jonas@gizur.com>
 * @version    SVN: $Id$
 * @link       href="http://gizur.com"
 * @license    Commercial license
 * @copyright  Copyright (c) 2013, Gizur AB, 
 * <a href="http://gizur.com">Gizur AB</a>, All rights reserved.
 *
 * Purpose: Mail report with Sales Ordres
 * Coding standards:
 * http://pear.php.net/manual/en/standards.php
 *
 * PHP version 5.3
 *
 *
 */

/*
 * Load configuration files
 */
require_once __DIR__ . '/../config.inc.php';
require_once __DIR__ . '/../config.database.php';

/*
 * Open connection to system logger
 */
openlog(
    "phpcronjob1", LOG_PID | LOG_PERROR, LOG_LOCAL0
);

/*
 * Try to connect to vTiger database as per setting 
 * defined in config files.
 */
syslog(
    LOG_INFO, "Try to connect to vTiger database"
);

$vTigerConnect = new Connect(
    $dbconfigVtiger['db_server'],
    $dbconfigVtiger['db_username'],
    $dbconfigVtiger['db_password'],
    $dbconfigVtiger['db_name']
);

syslog(
    LOG_INFO, "Connected to vTiger database"
);


/*
 * Open try to catch exceptions
 */

try {
    /*
     * Try to fetch pending sales orders fron vTiger database 
     */

    $salesOrdersQuery = "SELECT SO.salesorderid, SO.salesorder_no, SO.subject," .
                        "SO.sostatus, SO.contactid, SO.duedate, SO.sostatus," .
                        "ACCO.accountname, ACCO.accountid, PRO.productid," .
                        "PRO.productname, IVP.quantity" . 
                        "FROM vtiger_salesorder SO" .
                        "INNER JOIN vtiger_account ACCO on ACCO.accountid = SO.accountid" .
                        "INNER JOIN vtiger_inventoryproductrel IVP on IVP.id=SO.salesorderid" .
                        "INNER JOIN vtiger_products PRO on PRO.productid=IVP.productid" .
                        "WHERE SO.sostatus<>'Closed' AND lower(SO.subject)<>'initial push'" .
                        "    AND lower(SO.subject)<>'Intial Push'" .
                        "ORDER BY SO.salesorder_no";

    syslog(LOG_INFO, "Executing Query: " . $salesOrdersQuery);
    
    $salesOrders = $vTigerConnect->query($salesOrdersQuery);

    /*
     * Message array to store error / success messages
     * through out end.
     */
    $messages = array();

    /*
     * In case of unable to fetch sales orders
     * throw exception.
     */
    if (!$salesOrders){
        throw new Exception(
            "Error executing sales order query : " . 
            "($vTigerConnect->errno) - $vTigerConnect->error"
        );
        syslog(
            LOG_WARNING, 
            "Error executing sales order query : ($vTigerConnect->errno) - " .
                "$vTigerConnect->error"
        );
    }

    /*
     * If no pending sales orders found
     * throw exception. 
     */
    if ($salesOrders->num_rows == 0){
        throw new Exception("No Sales Order Found!");
        syslog(
            LOG_INFO, 
            "No Sales Order Found!"
        );
    }

    /*
     * Update message array with number of sales orders.
     */
    $messages['no_sales_orders'] = $salesOrders->num_rows;

    /*
     * Iterate through sales orders
     */
    syslog(
        LOG_INFO, 
        "Iterate through sales orders"
    );
    
    while ($salesOrder = $salesOrders->fetch_object()) {

        print   "$salesOrder->SO.salesorderid;" .
                "$salesOrder->SO.salesorder_no;" .
                "$salesOrder->SO.subject;" .
                "$salesOrder->SO.sostatus;" .
                "$salesOrder->SO.contactid;" .
                "$salesOrder->SO.duedate;" .
                "$salesOrder->SO.sostatus;" .
                "$salesOrder->ACCO.accountname;" .
                "$salesOrder->ACCO.accountid;" .
                "$salesOrder->PRO.productid;" .
                "$salesOrder->PRO.productname;" .
                "$salesOrder->IVP.quantity\n";
    }
} catch (Exception $e) {
    /*
     * Store the message and rollbach the connections.
     */
    $messages['message'] = $e->getMessage();
    $integrationConnect->rollback();
    $vTigerConnect->rollback();
}

/*
 * Close the connections
 */
$vTigerConnect->close();
$integrationConnect->close();

/*
 * Log the message
 */
syslog(LOG_WARNING, json_encode($messages));
echo json_encode($messages);
