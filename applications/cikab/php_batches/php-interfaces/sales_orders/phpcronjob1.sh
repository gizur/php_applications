#!/usr/bin/php

<?php
/*
 * Load configuration files
 */
require_once __DIR__ . '/../config.inc.php';
require_once __DIR__ . '/../config.database.php';

/*
 * Open connection to system logger
 */
openlog("phpcronjob1", LOG_PID | LOG_PERROR, LOG_LOCAL0);

/*
 * Try to connect to vTiger database as per setting 
 * defined in config files.
 */
$vTigerConnect = new Connect(
        $dbconfig_vtiger['db_server'],
        $dbconfig_vtiger['db_username'],
        $dbconfig_vtiger['db_password'],
        $dbconfig_vtiger['db_name']);

/*
 * Try to connect to integration database as per 
 * setting defined in config files.
 */
$integrationConnect = new Connect(
        $dbconfig_integration['db_server'],
        $dbconfig_integration['db_username'],
        $dbconfig_integration['db_password'],
        $dbconfig_integration['db_name']);

/*
 * Open try to catch exceptions
 */

try {
    /*
     * Try to fetch pending sales orders fron vTiger database 
     */
    $salesOrdersQuery = "SELECT SO.salesorderid, SO.salesorder_no 
        FROM " . $dbconfig_vtiger['db_name'] . ".vtiger_salesorder SO 
        WHERE SO.sostatus IN ('Created','Approved') LIMIT 0," .
        $dbconfig_batchvaliable['batch_valiable'] . "";

    $salesOrders = $vTigerConnect->query($salesOrdersQuery);

    /*
     * Message array to store error / success messages
     * through out end.
     */
    $_messages = array();

    /*
     * In case of unable to fetch sales orders
     * throw exception.
     */
    if (!$salesOrders)
        throw new Exception("Error executing sales order query : ($vTigerConnect->errno) - $vTigerConnect->error");

    /*
     * If no pending sales orders found
     * throw exception. 
     */
    if ($salesOrders->num_rows == 0)
        throw new Exception("No Sales Order Found!");

    /*
     * Update message array with number of sales orders.
     */
    $_messages['no_sales_orders'] = $salesOrders->num_rows;

    /*
     * Iterate through sales orders
     */
    while ($salesOrder = $salesOrders->fetch_object()) {

        /*
         * Open try to catch sales order specific errors.
         */
        try {
            /*
             * Disable auto commit.
             */
            $vTigerConnect->autocommit(FALSE);
            $integrationConnect->autocommit(FALSE);

            $_messages['sales_orders'][$salesOrder->salesorder_no] = array();

            /*
             * Fetch current sales order products.
             */
            $salesOrderProducts = $vTigerConnect->query("SELECT SO.salesorderid, SO.salesorder_no, SO.contactid,
                    SO.duedate, SO.sostatus, ACCO.accountname, ACCO.accountid, PRO.productid,
                    PRO.productname,IVP.quantity 
                FROM " . $dbconfig_vtiger['db_name'] . ".vtiger_salesorder SO 
                    INNER JOIN " . $dbconfig_vtiger['db_name'] . ".vtiger_account ACCO on ACCO.accountid=SO.accountid
                    INNER JOIN " . $dbconfig_vtiger['db_name'] . ".vtiger_inventoryproductrel IVP on IVP.id=SO.salesorderid
                    INNER JOIN " . $dbconfig_vtiger['db_name'] . ".vtiger_products PRO on PRO.productid=IVP.productid
                WHERE SO.salesorder_no = '" . $salesOrder->salesorder_no . "'");

            /*
             * Iterate through products.
             */
            while ($salesOrderProduct = $salesOrderProducts->fetch_object()) {

                $_batch_no = $salesOrderProduct->salesorder_no . '-' . $dbconfig_batchvaliable['batch_valiable'];

                /*
                 * Insert product into integration table.
                 */
                $interfaceQuery = $integrationConnect->query("INSERT 
                    INTO salesorder_interface
                    SET id = NULL, salesorderid = $salesOrderProduct->salesorderid, 
                        salesorder_no = '$salesOrderProduct->salesorder_no',
                        contactid = $salesOrderProduct->contactid, 
                        productname = '$salesOrderProduct->productname',
                        productid = $salesOrderProduct->productid,
                        productquantity = '$salesOrderProduct->quantity', 
                        duedate = '$salesOrderProduct->duedate',
                        accountname = '$salesOrderProduct->accountname',
                        accountid = $salesOrderProduct->accountid,
                        sostatus = '$salesOrderProduct->sostatus', 
                        batchno = '$_batch_no', createdate = now()");

                /*
                 * If insertion failed, Close resultset and raise exception.
                 */
                if (!$interfaceQuery) {
                    $salesOrderProducts->close();
                    throw new Exception("Error inserting product $salesOrderProduct->productname in interface table. ($integrationConnect->errno) - $integrationConnect->error");
                }

                /*
                 * Update message array with the inserted product.
                 */
                $_messages['sales_orders'][$salesOrder->salesorder_no]['products'][$salesOrderProduct->productname] = true;
                
                /*
                 * Iterate till either no exception raise or all 
                 * sales order product get inserted into integration database.
                 */
            }

            /*
             * Update sales order in case of all products get inserted.
             */
            $updateSaleOrder = $vTigerConnect->query("UPDATE " .
                "vtiger_salesorder SET " .
                "sostatus = 'Delivered' WHERE salesorderid = '$salesOrder->salesorderid'");

            /*
             * If updation fails throw exception.
             */
            if (!$updateSaleOrder) {
                $salesOrderProducts->close();
                throw new Exception("Error updating sales order $salesOrder->salesorder_no in vTiger ($vTigerConnect->errno) - $vTigerConnect->error.");
            }
            
            /*
             * Close the products resultset.
             */
            $salesOrderProducts->close();

            /*
             * Set sales status true, since it has processed without error.
             */
            $_messages['sales_orders'][$salesOrder->salesorder_no]['status'] = true;

            /*
             * Commit the databases.
             */
            $integrationConnect->commit();
            $vTigerConnect->commit();
        } catch (Exception $e) {
            /*
             * Store the messages
             */
            $_messages['sales_orders'][$salesOrder->salesorder_no]['error'] = $e->getMessage();
            $_messages['sales_orders'][$salesOrder->salesorder_no]['products'][$salesOrderProduct->productname] = false;
            /*
             * Rollback the connections
             */
            $integrationConnect->rollback();
            $vTigerConnect->rollback();
        }
    }
} catch (Exception $e) {
    /*
     * Store the message and rollbach the connections.
     */
    $_messages['message'] = $e->getMessage();
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
syslog(LOG_WARNING, json_encode($_messages));
echo json_encode($_messages);
?>