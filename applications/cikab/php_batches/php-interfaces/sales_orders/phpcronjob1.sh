#!/usr/bin/php
<?php
/**
 * @category   Cronjobs
 * @package    Integration
 * @subpackage CronJob
 * @author     Prabhat Khera <prabhat.khera@essindia.co.in>
 * @version    SVN: $Id$
 * @link       href="http://gizur.com"
 * @license    Commercial license
 * @copyright  Copyright (c) 2012, Gizur AB, 
 * <a href="http://gizur.com">Gizur Consulting</a>, All rights reserved.
 *
 * purpose : Connect to Amazon SQS through aws-php-sdk
 * Coding standards:
 * http://pear.php.net/manual/en/standards.php
 *
 * PHP version 5.3
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
    LOG_INFO, "Try to connect to vTiger database as " .
        "per setting defined in config files."
);

$vTigerConnect = new Connect(
    $dbconfigVtiger['db_server'],
    $dbconfigVtiger['db_username'],
    $dbconfigVtiger['db_password'],
    $dbconfigVtiger['db_name']
);


syslog(
    LOG_INFO, "Try to connect to integration database as " .
        "per setting defined in config files."
);

/*
 * Try to connect to integration database as per 
 * setting defined in config files.
 */
$integrationConnect = new Connect(
    $dbconfigIntegration['db_server'],
    $dbconfigIntegration['db_username'],
    $dbconfigIntegration['db_password'],
    $dbconfigIntegration['db_name']
);

/*
 * Open try to catch exceptions
 */

try {
    /*
     * Try to fetch pending sales orders fron vTiger database 
     */
    $salesOrdersQuery = "SELECT SO.salesorderid, SO.salesorder_no 
        FROM " . $dbconfigVtiger['db_name'] . ".vtiger_salesorder SO 
        WHERE SO.sostatus IN ('Created','Approved') LIMIT 0," .
        $dbconfigBatchVariable['batch_variable'] . "";

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

        /*
         * Open try to catch sales order specific errors.
         */
        try {
            /*
             * Disable auto commit.
             */
            syslog(
                LOG_INFO, 
                "Disabling auto commit"
            );
            $vTigerConnect->autocommit(FALSE);
            $integrationConnect->autocommit(FALSE);

            $mess = array();

            /*
             * Fetch current sales order products.
             */
            $salesOrderProducts = $vTigerConnect->query(
                "SELECT " .
                "SO.salesorderid, SO.salesorder_no, SO.contactid,
                SO.duedate, SO.sostatus, ACCO.accountname, " .
                "ACCO.accountid, PRO.productid, " .
                "PRO.productname,IVP.quantity " . 
                "FROM " . $dbconfigVtiger['db_name'] . ".vtiger_salesorder SO 
                INNER JOIN vtiger_account ACCO on ACCO.accountid = " . 
                "SO.accountid INNER JOIN vtiger_inventoryproductrel IVP " . 
                "on IVP.id=SO.salesorderid INNER JOIN vtiger_products PRO " .
                "on PRO.productid=IVP.productid
                WHERE SO.salesorder_no = '$salesOrder->salesorder_no'"
            );

            syslog(
                LOG_INFO, 
                "Fetched products ($salesOrder->salesorder_no): " . 
                $salesOrderProducts->num_rows
            );
            /*
             * Iterate through products.
             */
            while ($salesOrderProduct = $salesOrderProducts->fetch_object()) {

                $batchNo = $salesOrderProduct->salesorder_no . '-' . 
                    $dbconfigBatchVariable['batch_variable'];

                /*
                 * Insert product into integration table.
                 */
                $interfaceQuery = $integrationConnect->query(
                    "INSERT 
                    INTO salesorder_interface
                    SET id = NULL, 
                    salesorderid = $salesOrderProduct->salesorderid, 
                        salesorder_no = '$salesOrderProduct->salesorder_no',
                        contactid = $salesOrderProduct->contactid, 
                        productname = '$salesOrderProduct->productname',
                        productid = $salesOrderProduct->productid,
                        productquantity = '$salesOrderProduct->quantity', 
                        duedate = '$salesOrderProduct->duedate',
                        accountname = '$salesOrderProduct->accountname',
                        accountid = $salesOrderProduct->accountid,
                        sostatus = '$salesOrderProduct->sostatus', 
                        batchno = '$batchNo', createdate = now()"
                );
                
                syslog(
                    LOG_INFO, 
                    "Inserting product: " . $interfaceQuery
                );

                /*
                 * If insertion failed, Close resultset and raise exception.
                 */
                if (!$interfaceQuery) {
                    $salesOrderProducts->close();
                    throw new Exception(
                        "Error inserting product " .
                        "$salesOrderProduct->productname in " . 
                        "interface table. " . 
                        "($integrationConnect->errno) - " . 
                        "$integrationConnect->error"
                    );
                    syslog(
                        LOG_WARNING, 
                        "Error inserting product " .
                        "$salesOrderProduct->productname in " . 
                        "interface table. " . 
                        "($integrationConnect->errno) - " . 
                        "$integrationConnect->error"
                    );
                }

                /*
                 * Update message array with the inserted product.
                 */
                $mess['products'][$salesOrderProduct->productname] = true;

                /*
                 * Iterate till either no exception raise or all 
                 * sales order product get inserted into integration database.
                 */
            }

            /*
             * Update sales order in case of 
             * all products get inserted.
             */
            
            syslog(
                LOG_INFO, 
                "Updating sales order $salesOrder->salesorderid Delivered"
            );
            
            $updateSaleOrder = $vTigerConnect->query(
                "UPDATE vtiger_salesorder SET " .
                "sostatus = 'Delivered' WHERE salesorderid = " . 
                "'$salesOrder->salesorderid'"
            );

            /*
             * If updation fails throw exception.
             */
            if (!$updateSaleOrder) {
                $salesOrderProducts->close();
                throw new Exception(
                    "Error updating sales order " . 
                    "$salesOrder->salesorder_no in vTiger " . 
                    "($vTigerConnect->errno) - $vTigerConnect->error."
                );
                syslog(
                    LOG_WARNING, 
                    "Error updating sales order " . 
                    "$salesOrder->salesorder_no in vTiger " . 
                    "($vTigerConnect->errno) - $vTigerConnect->error."
                );
            }

            /*
             * Close the products resultset.
             */
            $salesOrderProducts->close();

            /*
             * Set sales status true, since it has processed without error.
             */
            $mess['status'] = true;

            /*
             * Commit the databases.
             */
            $integrationConnect->commit();
            $vTigerConnect->commit();
        } catch (Exception $e) {
            /*
             * Store the messages
             */
            $mess['error'] = $e->getMessage();
            $mess['products'][$salesOrderProduct->productname] = false;
            /*
             * Rollback the connections
             */
            $integrationConnect->rollback();
            $vTigerConnect->rollback();
        }
        
        $messages['sales_orders'][$salesOrder->salesorder_no] = $mess;
        unset($mess);
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