#!/usr/bin/php
<?php
include '../config.inc.php';
include '../config.database.php';

openlog("phpcronjob1", LOG_PID | LOG_PERROR, LOG_LOCAL0);

$integrationConnect = new Connect(
        $dbconfig_integration['db_server'],
        $dbconfig_integration['db_username'],
        $dbconfig_integration['db_password'],
        $dbconfig_integration['db_name']);

$_messages = array();

$salesOrders = $integrationConnect->query("SELECT salesorder_no, accountname 
           FROM salesorder_interface
           WHERE sostatus IN ('created', 'approved') 
           GROUP BY salesorder_no, accountname LIMIT 0, " . $dbconfig_batchvaliable['batch_valiable']);

if ($salesOrders) {
    if ($salesOrders->num_rows > 0) {

        $_messages['salesOrders'] = $salesOrders->num_rows;
        // Cycle through results
        while ($salesOrder = $salesOrders->fetch_object()) {

            $integrationConnect->autocommit(FALSE);
            $flag = true;
            $_messages[$salesOrder->salesorder_no] = array();

            $createdDate = date("YmdHi");

            $fileName = "SET.GZ.FTP.IN.BST.$createdDate.$salesOrder->accountname";

            $salesOrderWithProducts = $integrationConnect->query("SELECT * FROM salesorder_interface " .
                "WHERE salesorder_no = '$salesOrder->salesorder_no'" .
                " AND sostatus in ('created', 'approved')");

            $flag = true;

            $string = "";
            $leadzero = "";
            $accountlenth = "";
            $productnamearray = array();
            $productaccountarray = array();
            $productlength = "";
            $leadzeroproduct = "";
            $productquantitylength = "";
            $leadzeroproductquantity = "";

            if ($salesOrderWithProducts) {
                while ($salesOrderWithProduct = $salesOrderWithProducts->fetch_object()) {
                    
                    /**
                     * for check duplicate product and write productname in set file with+
                     */
                    if (!in_array($salesOrderWithProduct->productname, $productnamearray)) {
                        $productlength = strlen($salesOrderWithProduct->productname);
                        $productquantitylength = strlen($salesOrderWithProduct->productquantity);

                        /**
                         * for product name length count if product name length less the 6 then auto add zero berfor the product name
                         */
                        if ($productlength < 6) {
                            $leadzeroproduct = leadingzero($productlength);
                        }

                        /**
                         * for product quantity length count if product quantity length less the 3 then auto add zero berfor the product quantity
                         */
                        if ($productquantitylength < 3) {
                            $leadzeroproductquantity = leadingzero(3, $productquantitylength);
                        }

                        $multiproduct[] = "189" . $leadzeroproduct . $salesOrderWithProduct->productname . $leadzeroproductquantity . $salesOrderWithProduct->productquantity;
                        $productnamearray[] = $salesOrderWithProduct->productname;
                    }

                    /**
                     * for check duplicate account name and write account name in set file
                     */
                    if (!in_array($salesOrderWithProduct->accountname, $productaccountarray)) {
                        /**
                         * count account name length if length less then 6 then leading zero in account name.
                         */
                        $accountlenth = strlen($salesOrderWithProduct->accountname);
                        if ($accountlenth < 6) {
                            $leadzero = leadingzero(6, $accountlenth);
                        }
                        /**
                         * final account name;
                         * $accountname
                         */
                        $finalformataccountname = $leadzero . $salesOrderWithProduct->accountname;
                    }

                    $finalformatproductname = implode("+", $multiproduct);
                    $currentdate = date("Ymd");
                    $originalordernomber = "7777" . $salesOrderWithProduct->salesorder_no;

                    /**
                     * for find the order no. total length if length will be greater then 6 then auto remove from the starting
                     */
                    $orderlength = strlen($originalordernomber);

                    if ($orderlength > 6) {
                        $accessorderlength = $orderlength - 6;
                        /**
                         *  auto remove order no.  
                         */
                        $ordernumber = substr($originalordernomber, $accessorderlength);
                    } else {
                        $ordernumber = $originalordernomber;
                    }
                    
                    $deliveryday = date("ymd", strtotime($salesOrderWithProduct->duedate));
                    $futuredeliverydate1 = strtotime(date("Y-m-d", strtotime($salesOrderWithProduct->duedate)) . " +1 day");
                    $futuredeliverydate = date('ymd', $futuredeliverydate1);

                    $orderrefferenceno = "000" . $salesOrderWithProduct->salesorder_no;
                    
                    $flag = $flag && true;
                }
                
                if($flag){
                    echo $string = "HEADERGIZUR           " . $currentdate . 
                        "18022800M256      RUTIN   .130KF27777100   " . 
                        "mottagning initierad                               " . 
                        "                                          001" . 
                        $finalformataccountname . "1+03751+038" . $ordernumber . 
                        "+226" . $futuredeliverydate . "+039" . 
                        $deliveryday . "+040" . $ordernumber . "+" . 
                        $finalformatproductname . "+C         RUTIN   " . 
                        ".130KF51125185   Mottagning avslutad    " . 
                        "BYTES/BLOCKS/RETRIES=1084 /5    /0";
                }
            } else {
                $flag = $flag && false;
            }
        }
    } else {
        $_messages['message'] = "No SalesOrder Found!";
    }
} else {
    $_messages['message'] = "Error executing sales order query : ($integrationConnect->errno) - $integrationConnect->error";
}
?>

<?php

/**
 * auto adding zero befor number  
 */
function leadingzero($limitnumber = 6, $number)
{
    $leadzero = "";
    $leadingzero = $limitnumber - $number;
    for ($i = 0; $i < $leadingzero; $i++) {
        $leadzero.= 0;
    }
    return $leadzero;
}

/**
 *   Create function For Insert salesorder no into the msg que table when successfully created file and que in messagemq server.
 */
function InsertRecordToMsg($db, $accountname, $Setfile, $connf)
{

    $Insertsalesordertable = "INSERT INTO `$db`.`saleorder_msg_que` 
        SET accountname= '$accountname', 
            ftpfilename= '$Setfile'  ON DUPLICATE KEY UPDATE accountname = '$accountname'";
    $exequery = @mysql_query($Insertsalesordertable, $connf);
    if ($exequery) {
        $updateinterfasesatus = "UPDATE `$db`.`salesorder_interface` 
            SET sostatus = 'Delivered' where accountname = '$accountname'";
        $exequery = mysql_query($updateinterfasesatus, $connf);
        if ($exequery) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
?>

