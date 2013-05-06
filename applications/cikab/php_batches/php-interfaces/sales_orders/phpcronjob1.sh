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
                "({$this->vTigerConnect->errno}) - " .
                "{$this->vTigerConnect->error}"
            );
            syslog(
                LOG_WARNING, 
                "In getSalesOrders() : Error executing sales order query :" .
                " ({$this->vTigerConnect->errno}) - " .
                "{$this->vTigerConnect->error}"
            );
        }

        /*
         * Update message array with number of sales orders.
         */
        $this->messages['no_sales_orders'] = $salesOrders->num_rows;
    
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

        syslog(
            LOG_INFO, "In createIntegrationSalesOrder() " .
            "Preparing insert sales order query ($salesOrder->salesorder_no)"
        );
        /*
         * Insert sales order into integration table.
         */
        $interfaceQuery = $this->integrationConnect->query(
            "INSERT INTO sales_orders
            SET `id` = NULL, 
            `salesorderid` = $salesOrder->salesorderid, 
            `salesorder_no` = '$salesOrder->salesorder_no',
            `contactid` = $salesOrder->contactid, 
            `duedate` = '$salesOrder->duedate',
            `accountname` = '$salesOrder->accountname',
            `accountid` = $salesOrder->accountid,            
            `batchno` = '$batchNo', 
            `set` = '" . $salesOrder->{Config::$customFields['setFiles']} . "',
            `mos` = '" . $salesOrder->{Config::$customFields['mosFiles']} . "',
            `status` = '$salesOrder->sostatus', 
            `created` = now()"
        );

        syslog(
            LOG_INFO, 
            "In createIntegrationSalesOrder() : " .
            "Inserting sales order: " . $interfaceQuery
        );

        /*
         * If insertion failed, Close resultset and raise exception.
         */
        if (!$interfaceQuery) {
            syslog(
                LOG_WARNING, 
                "Error inserting salesorder $salesOrder->salesorder_no in " .
                "integration db ({$this->integrationConnect->errno}) - " .
                "{$this->integrationConnect->error}"
            );
            throw new Exception(
                "Error inserting salesorder $salesOrder->salesorder_no in " .
                "integration db ({$this->integrationConnect->errno}) - " .
                "$this->integrationConnect->error"
            );
        }

        return $interfaceQuery;
    }

    protected function createIntegrationProduct($salesOrderId, $salesOrderProduct)
    {
        syslog(
            LOG_INFO, 
            "In createIntegrationProduct($salesOrderId)" .
            " Preparing insert product query.: "
        );
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
            LOG_INFO, 
            "In createIntegrationProduct($salesOrderId) " .
            "Inserting product: " . $interfaceQuery
        );

        /*
         * If insertion failed, Close resultset and raise exception.
         */
        if (!$interfaceQuery) {
            syslog(
                LOG_WARNING, 
                "Error inserting product $salesOrderProduct->productname in " .
                "integration db ({$this->integrationConnect->errno}) - " .
                "{$this->integrationConnect->error}"
            );
            throw new Exception(
                "Error inserting product $salesOrderProduct->productname in " .
                "integration db ({$this->integrationConnect->errno}) - " .
                "{$this->integrationConnect->error}"
            );
        }

        return $interfaceQuery;
    }

    protected function updateVtigerSalesOrder($salesOrderID, $status = 'Delivered')
    {
        syslog(
            LOG_INFO, "Updating sales order ($salesOrderID) $status"
        );

        $updateSaleOrder = $this->vTigerConnect->query(
            "UPDATE vtiger_salesorder SET " .
            "sostatus = '$status' WHERE salesorderid = " .
            "'$salesOrderID'"
        );

        if (!$updateSaleOrder) {
            syslog(
                LOG_WARNING,
                "In updateVtigerSalesOrder($salesOrderID, $status) : " .
                "Error updating salesorder"
            );
            throw new Exception(
                "In updateVtigerSalesOrder($salesOrderID, $status) : " .
                "Error updating salesorder"
            );
        }
        
        return $updateSaleOrder;
    }

    public function init()
    {
        try {
            $salesOrders = $this->getSalesOrders();
            $numberSalesOrders = $salesOrders->num_rows;
            
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

                    $this->messages[$salesOrder->salesorder_no] = 
                        array();
                    
                    $this->createIntegrationSalesOrder($salesOrder);

                    $soId = $this->integrationConnect->insert_id;

                    $this->messages[$salesOrder->salesorder_no]['id'] = $soId;
                    $mess = &$this->messages[$salesOrder->salesorder_no];
                    
                    $salesOrderProducts = $this->getProductsBySalesOrders(
                        $salesOrder->salesorder_no
                    );

                    $flag = true;

                    while ($flag && $salesOrderProduct = $salesOrderProducts->fetch_object()) {
                        $mess['products'][$salesOrderProduct->productname] 
                            = true;
                        $flag = $flag && $this->createIntegrationProduct(
                                $soId, $salesOrderProduct
                        );
                    }

                    if ($flag)
                        $this->updateVtigerSalesOrder(
                            $salesOrder->salesorderid,
                            'Delivered'
                        );
                        
                    /*
                     * Commit the databases.
                     */
                    $this->integrationConnect->commit();
                    $this->vTigerConnect->commit();
                    
                } catch (Exception $e) {
                    $numberSalesOrders--;
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
            
            $this->messages['message'] = "$numberSalesOrders number " .
                        "of sales orders processed.";
            
            syslog(
                LOG_INFO, 
                json_encode($this->messages)
            );
            echo json_encode($this->messages);
            
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

$phpBatchOne = new PhpBatchOne();
$phpBatchOne->init();