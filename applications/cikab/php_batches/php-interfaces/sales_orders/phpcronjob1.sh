#!/usr/bin/php

<?php
/**
 * 
 * 
 * Created date : 09/04/2012
 * Created By : Anil Singh
 * @author Anil Singh <anil-singh@essindia.co.in>
 * Flow : The basic flow of this page is extract all salesorder record from the vtigercrm and insert into the interface table. 
 * 		if any issues in the query then auto roleback.
 * Modify date : 27/04/2012
 */
/**
 * using of $salesordertable
 * The purpuse of using first query '$salesordertable' of the all fetch record into the master table when status will be Created 
 * and Approved.
 */
/**
 * Use of While 
 * While use for multiple Rows
 */
/**
 * Use of mysql_error () 
 * The purpose of use mysql_error function because we can track which type error in this query.
 */
/**
 * using of Query $salesordermaptable into the first while loop
 * The purpose of using of the query '$salesordermaptable' when fetched saleoredrid and saleorder_no above query from the master table
 * and get product name,productid,accountname,duedate,product quantity from above saleorderid.
 * we know that one salesorderid or salesorder_no has been multiple product or items.
 * if any issues in related saleorderid query then it will be rollback all record(related to saleorderid). 
 * After rollback we can see error report into the syslog file.
 * path : /home/var/log/syslog
 */
/**
 * For Use GLOBAL Valiable
 */
require_once __DIR__ . '/../config.inc.php';
/**
 * For Use Databse Connection
 */
require_once __DIR__ . '/../config.database.php';

/* * ***
 * Set autocommit OFF
 */
mysql_query("SET autocommit = 0");

/* * ***
 * Set satrt trasaction ON
 */
mysql_query("START TRANSACTION");

/* * ***
 * Ready state of syslog
 */
openlog("phpcronjob1", LOG_PID | LOG_PERROR, LOG_LOCAL0);

$salesordertable = "SELECT SO.salesorderid,SO.salesorder_no 
        FROM " . $dbconfig_vtiger['db_name'] . ".vtiger_salesorder SO 
        WHERE SO.sostatus IN ('Created','Approved') LIMIT 0," . $dbconfig_batchvaliable['batch_valiable'] . "";
$exequery = mysql_query($salesordertable, $obj2->link);

if (!$exequery) {
    /**
     * Write which type Error into the query 
     */
    $queryerror = mysql_error();
    $access = date("Y/m/d H:i:s");

    /**
     * Set Syslog Message
     */
    $message = "Sorry ! - Some Problem into the query No.1. Query Error is : " . $queryerror . " ";

    /**
     *  Write Error Message into the syslog	
     */
    syslog(LOG_WARNING, "" . $message . ": " . $access . "");
} else {
    $NumRows = @mysql_num_rows($exequery);
}
if (!empty($NumRows)) {

    while ($CrmOrderId = mysql_fetch_array($exequery)) {

        /**
         *  Get productname,productid,duedate,quantity data into master table with related saleorder_no  
         */
        $salesordermaptable = " SELECT SO.salesorderid,SO.salesorder_no,SO.contactid,SO.duedate,
            SO.sostatus,ACCO.accountname,ACCO.accountid,PRO.productid,PRO.productname,IVP.quantity 
            FROM " . $dbconfig_vtiger['db_name'] . ".vtiger_salesorder SO 
            INNER JOIN " . $dbconfig_vtiger['db_name'] . ".vtiger_account ACCO on ACCO.accountid=SO.accountid
            INNER JOIN " . $dbconfig_vtiger['db_name'] . ".vtiger_inventoryproductrel IVP on IVP.id=SO.salesorderid
            INNER JOIN " . $dbconfig_vtiger['db_name'] . ".vtiger_products PRO on PRO.productid=IVP.productid
            WHERE SO.salesorder_no='" . $CrmOrderId['salesorder_no'] . "'";

        $findproblemsalesorderid = "";
        $queryerror = "";
        $allOK = true;
        $ExecuteQuery = @mysql_query($salesordermaptable, $obj2->link);

        /**
         *   Above Query not success then write Error into syslog 
         */
        if (!$ExecuteQuery) {
            /**
             * Write which type Error into the query 
             */
            $queryerror = mysql_error();

            /**
             * 	Set Syslog Message
             */
            $message = "Sorry ! - Some Problem into the query No.2. Query Error is : " . $queryerror . " ";

            /**
             * Set $allOK is false
             */
            $allOK = false;
        }

        while ($CrmRows = @mysql_fetch_array($ExecuteQuery)) {

            /**
             *  Insert data in interface table 
             */
            $interfase_query = "INSERT INTO `" . $dbconfig_integration['db_name'] . "`.`salesorder_interface` 
                SET salesorderid='" . $CrmRows['salesorderid'] . "',salesorder_no='" . $CrmRows['salesorder_no'] . "',
                contactid='" . $CrmRows['contactid'] . "',
                productname='" . $CrmRows['productname'] . "',productid='" . $CrmRows['productid'] . "',
                productquantity='" . $CrmRows['quantity'] . "',duedate='" . $CrmRows['duedate'] . "',
                accountname='" . $CrmRows['accountname'] . "',accountid='" . $CrmRows['accountid'] . "',
                sostatus='" . $CrmRows['sostatus'] . "',
                batchno='" . $CrmRows['salesorder_no'] . "-" . $dbconfig_batchvaliable['batch_valiable'] . "'";

            $interfaceExequery = @mysql_query($interfase_query, $obj1->link);

            if (!$interfaceExequery) {
                /**
                 * Write Error Messasge into syslog file   
                 */
                $findproblemsalesorderid = $CrmRows['salesorderid'] . " And Batch No  is : '" 
                    . $CrmRows['salesorder_no'] . "-" . $dbconfig_batchvaliable['batch_valiable'] . "' ";

                /**
                 *  Write which type Error into the query 
                 */
                $queryerror = mysql_error();

                /**  Set $allOK is false
                 */
                $allOK = false;

                /**
                 * Set Syslog Message 
                 */
                $message = "Sorry ! - Some problem in salesorder id :" . $findproblemsalesorderid 
                    . " record. Error is : " . $queryerror . "";
            }
            /**
             * Change Status after data suessfully updated into interface table
             */ else {
                $updatemastertablestatus = "UPDATE `" . $dbconfig_vtiger['db_name'] 
                    . "`.`vtiger_salesorder` SET sostatus='Delivered' WHERE salesorderid='" 
                    . $CrmRows['salesorderid'] . "'";
                $Updatemasterstatus = @mysql_query($updatemastertablestatus, $obj2->link);
                if (!$Updatemasterstatus) {
                    $allOK = false;
                    $queryerror = mysql_error();
                    $message = "Sorry ! - Some problem in master table vtiger_salesorder  :" 
                    . $findproblemsalesorderid . " record. Error is : " . $queryerror . "";
                }
            }

            /**
             * for all row succussfully inserted into the interface table 
             * then autometic commit commance execute other wise rollback 
             * command execute
             */
            $allOK = ($allOK && $interfaceExequery);
        }
        /**
         * If the query successfull then COMMIT comand Execute here 
         */
        if ($allOK) {
            mysql_query("COMMIT");
            echo "Succussfilly inserted \n";
        }

        /**
         * if the Query not successfull the ROLLBACK command Execute here
         */ else {
            mysql_query("ROLLBACK");
            $access = date("Y/m/d H:i:s");

            /**
             *  Write Error Message into the syslog	
             */
            syslog(LOG_WARNING, "" . $message . ": " . $access . "");
        }
    }
}
/**
 *  if record will be empty 
 */ else {
    echo "No Record Found!!!!";
}
?>


