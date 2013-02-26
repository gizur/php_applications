#!/usr/bin/php

<?php
include '../config.inc.php';
include '../config.database.php';

openlog("phpcronjob1", LOG_PID | LOG_PERROR, LOG_LOCAL0);
/* turn autocommit false */
$vTigerConnect = new Connect(
        $dbconfig_vtiger['db_server'],
        $dbconfig_vtiger['db_username'],
        $dbconfig_vtiger['db_password'],
        $dbconfig_vtiger['db_name']);

$integrationConnect = new Connect(
        $dbconfig_integration['db_server'],
        $dbconfig_integration['db_username'],
        $dbconfig_integration['db_password'],
        $dbconfig_integration['db_name']);

$salesOrdersQuery = "SELECT SO.salesorderid,SO.salesorder_no 
        FROM " . $dbconfig_vtiger['db_name'] . ".vtiger_salesorder SO 
        WHERE SO.sostatus IN ('Created','Approved') LIMIT 0," . $dbconfig_batchvaliable['batch_valiable'] . "";

$salesOrders = $vTigerConnect->query($salesOrdersQuery);

$_errors = array();
$_messages = array();
    
if ($salesOrders) {    
    
    if ($salesOrders->num_rows > 0) {
        
        $_messages['salesOrders'] = $salesOrders->num_rows;
        // Cycle through results
        while ($salesOrder = $salesOrders->fetch_object()) {

            $vTigerConnect->autocommit(FALSE);
            $integrationConnect->autocommit(FALSE);
            $flag = true;
            
            $_messages[$salesOrder->salesorder_no] = array();
            
            $salesOrderProducts = $vTigerConnect->query("SELECT SO.salesorderid, SO.salesorder_no, SO.contactid,
                    SO.duedate, SO.sostatus, ACCO.accountname, ACCO.accountid, PRO.productid,
                    PRO.productname,IVP.quantity 
                FROM " . $dbconfig_vtiger['db_name'] . ".vtiger_salesorder SO 
                    INNER JOIN " . $dbconfig_vtiger['db_name'] . ".vtiger_account ACCO on ACCO.accountid=SO.accountid
                    INNER JOIN " . $dbconfig_vtiger['db_name'] . ".vtiger_inventoryproductrel IVP on IVP.id=SO.salesorderid
                    INNER JOIN " . $dbconfig_vtiger['db_name'] . ".vtiger_products PRO on PRO.productid=IVP.productid
                WHERE SO.salesorder_no = '" . $salesOrder->salesorder_no . "'");

            while ($salesOrderProduct = $salesOrderProducts->fetch_object()) {

                $_batch_no = $salesOrderProduct->salesorder_no . '-' . $dbconfig_batchvaliable['batch_valiable'];
                
                $interfaceQuery = $integrationConnect->query("INSERT 
                    INTO salesorder_interface
                    SET id = NULL, salesorderid = $salesOrderProduct->salesorderid, 
                        salesorder_no = '$salesOrderProduct->salesorder_no',
                        contactid = $salesOrderProduct->contactid, 
                        productname = '$salesOrderProduct->productname',
                        productid = '$salesOrderProduct->productid,
                        productquantity = '$salesOrderProduct->quantity', 
                        duedate = '$salesOrderProduct->duedate',
                        accountname = '$salesOrderProduct->accountname',
                        accountid = $salesOrderProduct->accountid,
                        sostatus = '$salesOrderProduct->sostatus', 
                        batchno = '$_batch_no', createdate = now()");
                
                if ($interfaceQuery) {
                    
                    $_messages[$salesOrder->salesorder_no]['products'][$salesOrderProduct->productname] = true;
                    
                    $updateSaleOrder = $vTigerConnect->query("UPDATE " .
                        "vtiger_salesorder SET " .
                        "sostatus = 'Delivered' WHERE salesorderid = '$salesOrder->salesorder_no'");

                    if ($updateSaleOrder)
                        $flag = $flag && true;
                    else
                        $flag = $flag && false;
                } else {
                    
                    $_messages[$salesOrder->salesorder_no]['error'] = "($integrationConnect->errno) - $integrationConnect->error";
                    $_messages[$salesOrder->salesorder_no]['products'][$salesOrderProduct->productname] = false;
                    
                    $flag = $flag && false;
                    // ERROR INSERTING PRODUCTS
                }
            }

            $salesOrderProducts->close();
                
            if ($flag) {
                
                $_messages[$salesOrder->salesorder_no]['status'] = true;
                
                $integrationConnect->commit();
                $vTigerConnect->commit();
            } else {
                
                $_messages[$salesOrder->salesorder_no]['status'] = false;
                
                $integrationConnect->rollback();
                $vTigerConnect->rollback();
            }
        }
    } else {
        array_push($_errors, "No Sales Order Found!");
    }
} else {
    array_push($_errors, "Error executing sales order query : ($vTigerConnect->errno) - $vTigerConnect->error");
}

$vTigerConnect->close();
$integrationConnect->close();

if(!empty($_errors)){
    syslog(LOG_WARNING, json_encode($_errors));
    echo json_encode($_errors);
}else{
    syslog(LOG_WARNING, json_encode($_messages));
    echo json_encode($_messages);
}
    
?>