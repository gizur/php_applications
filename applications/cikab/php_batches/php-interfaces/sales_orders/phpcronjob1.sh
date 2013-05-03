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
    if (!$salesOrders) {
        throw new Exception(
        "Error executing sales order query : " .
        "($vTigerConnect->errno) - $vTigerConnect->error"
        );
        syslog(
            LOG_WARNING, "Error executing sales order query : ($vTigerConnect->errno) - " .
            "$vTigerConnect->error"
        );
    }

    /*
     * If no pending sales orders found
     * throw exception. 
     */
    if ($salesOrders->num_rows == 0) {
        throw new Exception("No Sales Order Found!");
        syslog(
            LOG_INFO, "No Sales Order Found!"
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
        LOG_INFO, "Iterate through sales orders"
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
                LOG_INFO, "Disabling auto commit"
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
                LOG_INFO, "Fetched products ($salesOrder->salesorder_no): " .
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
                    LOG_INFO, "Inserting product: " . $interfaceQuery
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
                        LOG_WARNING, "Error inserting product " .
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
                LOG_INFO, "Updating sales order ($salesOrder->salesorder_no) Delivered"
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
                    LOG_WARNING, "Error updating sales order " .
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

class PhpBatchOne
{

    private $vTigerConnect;
    private $integrationConnect;
    private $messages = array();

    public function __construct()
    {
        openlog(
            "phpcronjob1", LOG_PID | LOG_PERROR, LOG_LOCAL0
        );

        /*
         * Trying to connect to vTiger database
         */
        syslog(
            LOG_INFO, "Trying to connect to vTiger database"
        );

        $this->vTigerConnect = new mysqli(
            Config::$dbconfigVtiger['db_server'], 
            Config::$dbconfigVtiger['db_username'], 
            Config::$dbconfigVtiger['db_password'], 
            Config::$dbconfigVtiger['db_name'], 
            Config::$dbconfigVtiger['db_port']
        );

        if ($this->vTigerConnect->connect_errno)
            throw new Exception('Unable to connect with vTiger DB');

        syslog(
            LOG_INFO, "Connected to vTiger database"
        );

        syslog(
            LOG_INFO, "Trying to connect to integration database"
        );

        /*
         * Trying to connect to integration database
         */
        $this->integrationConnect = new mysqli(
            Config::$dbconfigIntegration['db_server'], 
            Config::$dbconfigIntegration['db_username'], 
            Config::$dbconfigIntegration['db_password'], 
            Config::$dbconfigIntegration['db_name'], 
            Config::$dbconfigIntegration['db_port']
        );

        if ($this->integrationConnect->connect_errno)
            throw new Exception('Unable to connect with integration DB');
        
        syslog(
            LOG_INFO, "Connected with integration db"
        );
    }

    protected function getSalesOrders()
    {
        syslog(LOG_INFO, "In getSalesOrders() : Preparing sales order query");
        
        $salesOrdersQuery = "SELECT SO.salesorderid, SO.salesorder_no, 
            ACCF.cf_664, ACCF.cf_665, ACCO.accountname, SO.contactid,
            SO.duedate, SO.sostatus, ACCO.accountid
            FROM vtiger_salesorder SO 
            LEFT JOIN vtiger_account ACCO on ACCO.accountid = SO.accountid
            LEFT JOIN vtiger_accountscf ACCF on ACCF.accountid = ACCO.accountid
            WHERE SO.sostatus IN ('Created','Approved')
            AND (ACCF." . Config::$customFields['setFiles'] .
            " = 'Yes' OR ACCF." . Config::$customFields['mosFiles'] .
            " = 'Yes') LIMIT 0, " . Config::$batchVariable;

        syslog(
            LOG_INFO,
            "In getSalesOrders() : Executing Query: " . $salesOrdersQuery
        );

        $salesOrders = $this->vTigerConnect->query($salesOrdersQuery);

        if (!$salesOrders) {
            throw new Exception(
                "In getSalesOrders() : Error executing sales order query : " .
                "($this->vTigerConnect->errno) - $this->vTigerConnect->error"
            );
            syslog(
                LOG_WARNING, 
                "In getSalesOrders() : Error executing sales order query :" .
                " ($this->vTigerConnect->errno) - " .
                "$this->vTigerConnect->error"
            );
        }

        if ($salesOrders->num_rows == 0) {
            throw new Exception("In getSalesOrders() : No Sales Order Found!");
            syslog(
                LOG_WARNING, "In getSalesOrders() : No Sales Order Found!"
            );
        }

        return $salesOrders;
    }

    protected function getProductsBySalesOrders($salesOrderNo)
    {
        /*
         * Fetch current sales order products.
         */
        $salesOrderProducts = $this->vTigerConnect->query(
            "SELECT " .
            "SO.salesorderid, SO.salesorder_no, SO.contactid," .
            "SO.duedate, SO.sostatus, ACCO.accountname, " .
            "ACCO.accountid, PRO.productid, " .
            "PRO.productname,IVP.quantity " .
            "FROM vtiger_salesorder SO " .
            "INNER JOIN vtiger_account ACCO on ACCO.accountid = SO.accountid " .
            "INNER JOIN vtiger_inventoryproductrel IVP on IVP.id = " .
            "SO.salesorderid " .
            "INNER JOIN vtiger_products PRO on PRO.productid = IVP.productid " .
            "WHERE SO.salesorder_no = '$salesOrderNo'"
        );

        syslog(
            LOG_INFO, "Total number of products ($salesOrderNo): " .
            $salesOrderProducts->num_rows
        );

        return $salesOrderProducts;
    }

    protected function createIntegrationSalesOrder($salesOrder)
    {
        $batchNo = $salesOrder->salesorder_no . '-' . Config::$batchVariable;

        /*
         * Insert sales order into integration table.
         */
        $interfaceQuery = $this->integrationConnect->query(
            "INSERT INTO sales_orders
            SET id = NULL, 
            salesorderid = $salesOrder->salesorderid, 
            salesorder_no = '$salesOrder->salesorder_no',
            contactid = $salesOrder->contactid, 
            duedate = '$salesOrder->duedate',
            accountname = '$salesOrder->accountname',
            accountid = $salesOrder->accountid,            
            batchno = '$batchNo', 
            set = '" . $salesOrder->{Config::$customFields['setFiles']} . "',
            mos = '" . $salesOrder->{Config::$customFields['mosFiles']} . "',
            status = '$salesOrder->sostatus', 
            created = now()"
        );

        syslog(
            LOG_INFO, "Inserting sales order: " . $interfaceQuery
        );

        /*
         * If insertion failed, Close resultset and raise exception.
         */
        if (!$interfaceQuery) {
            throw new Exception(
            "Error inserting salesorder $salesOrder->salesorder_no in " .
            "integration db ($this->integrationConnect->errno) - " .
            "$this->integrationConnect->error"
            );
            syslog(
                LOG_WARNING, "Error inserting salesorder $salesOrder->salesorder_no in " .
                "integration db ($this->integrationConnect->errno) - " .
                "$this->integrationConnect->error"
            );
        }

        return $interfaceQuery;
    }

    protected function createIntegrationProduct($salesOrderId, $salesOrderProduct)
    {
        /*
         * Insert sales order into integration table.
         */
        $interfaceQuery = $this->integrationConnect->query(
            "INSERT INTO sales_order_products
            SET id = NULL, 
            productname = '$salesOrderProduct->productname',
            productid = $salesOrderProduct->productid,
            productquantity = $salesOrderProduct->quantity,
            featurdate = NULL,
            sales_order_id = $salesOrderId,
            created = now()"
        );

        syslog(
            LOG_INFO, "Inserting product: " . $interfaceQuery
        );

        /*
         * If insertion failed, Close resultset and raise exception.
         */
        if (!$interfaceQuery) {
            throw new Exception(
                "Error inserting product $salesOrderProduct->productname in " .
                "integration db ($this->integrationConnect->errno) - " .
                "$this->integrationConnect->error"
            );
            syslog(
                LOG_WARNING, 
                "Error inserting product $salesOrderProduct->productname in " .
                "integration db ($this->integrationConnect->errno) - " .
                "$this->integrationConnect->error"
            );
        }

        return $interfaceQuery;
    }

    protected function updateVtigerSalesOrder($salesOrderID)
    {
        syslog(
            LOG_INFO, "Updating sales order ($salesOrderID) Delivered"
        );

        $updateSaleOrder = $this->vTigerConnect->query(
            "UPDATE vtiger_salesorder SET " .
            "sostatus = 'Delivered' WHERE salesorderid = " .
            "'$salesOrderID'"
        );

        return $updateSaleOrder;
    }

    public function init()
    {
        /*
         * Open try to catch exceptions
         */

        try {
            $salesOrders = $this->getSalesOrders();

            while ($salesOrder = $salesOrders->fetch_object()) {
                try {
                    /*
                     * Disable auto commit.
                     */
                    syslog(
                        LOG_INFO, "Disabling auto commit"
                    );
                    $this->vTigerConnect->autocommit(FALSE);
                    $this->integrationConnect->autocommit(FALSE);

                    if ($this->createIntegrationSalesOrder($salesOrder)) {

                        $salesOrderId = $this->integrationConnect->insert_id;

                        $salesOrderProducts = $this->getProductsBySalesOrders(
                            $salesOrder->salesorder_no
                        );

                        $flag = true;

                        while ($flag && $salesOrderProduct = $salesOrderProducts->fetch_object()) {
                            $flag = $flag && $this->createIntegrationProduct(
                                    $salesOrderId, $salesOrderProduct
                            );
                        }

                        if ($flag) {
                            /*
                             * Commit the databases.
                             */
                            $this->integrationConnect->commit();
                            $this->vTigerConnect->commit();
                        }
                    }
                } catch (Exception $e) {
                    /*
                     * Store the messages
                     */
                    $mess['error'] = $e->getMessage();
                    $mess['products'][$salesOrderProduct->productname] = false;
                    /*
                     * Rollback the connections
                     */
                    $this->integrationConnect->rollback();
                    $this->vTigerConnect->rollback();
                }
            }
        } catch (Exception $e) {
            /*
             * Store the message and rollbach the connections.
             */
            $this->messages['message'] = $e->getMessage();
            /*
             * Rollback the connections
             */
            $this->integrationConnect->rollback();
            $this->vTigerConnect->rollback();
        }
    }

}