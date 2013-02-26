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

                $interfaceQuery = $integrationConnect->prepare("INSERT 
                    INTO `" . $dbconfig_integration['db_name'] . "`.`salesorder_interface` 
                    SET salesorderid = ?, salesorder_no = ?,
                        contactid = ?, productname = ?, productid = ?,
                        productquantity = ?, duedate = ?,
                        accountname = ?, accountid = ?,
                        sostatus = ?, batchno = ?");
                $interfaceQuery->bind_param("i", $salesOrderProduct->salesorderid);
                $interfaceQuery->bind_param("s", $salesOrderProduct->salesorder_no);
                $interfaceQuery->bind_param("i", $salesOrderProduct->contactid);
                $interfaceQuery->bind_param("s", $salesOrderProduct->productname);
                $interfaceQuery->bind_param("i", $salesOrderProduct->productid);
                $interfaceQuery->bind_param("i", $salesOrderProduct->quantity);
                $interfaceQuery->bind_param("s", (string) $salesOrderProduct->duedate);
                $interfaceQuery->bind_param("s", $salesOrderProduct->accountname);
                $interfaceQuery->bind_param("i", $salesOrderProduct->accountid);
                $interfaceQuery->bind_param("s", $salesOrderProduct->sostatus);
                $interfaceQuery->bind_param("s", $salesOrderProduct->salesorder_no . '-' . $dbconfig_batchvaliable['batch_valiable']);
                //batchno : $CrmRows['salesorder_no'] . "-" . $dbconfig_batchvaliable['batch_valiable']

                if ($interfaceQuery->execute()) {
                    
                    $_messages[$salesOrder->salesorder_no][$salesOrderProduct->productname] = true;
                    
                    $updateSaleOrder = $vTigerConnect->prepare("UPDATE " .
                        "vtiger_salesorder SET " .
                        "sostatus = ? WHERE salesorderid = ?");

                    $updateSaleOrder->bind_param("s", "Delivered");
                    $updateSaleOrder->bind_param("s", $salesOrder->salesorder_no);

                    if ($updateSaleOrder->execute())
                        $flag = $flag && true;
                    else
                        $flag = $flag && false;
                } else {
                    
                    $_messages[$salesOrder->salesorder_no][$salesOrderProduct->productname] = false;
                    
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