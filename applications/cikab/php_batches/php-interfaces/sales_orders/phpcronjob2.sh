#!/usr/bin/php
<?php
include '../config.inc.php';
include '../config.database.php';
include '../config.sqs.inc.php';

openlog("phpcronjob2", LOG_PID | LOG_PERROR, LOG_LOCAL0);

try {
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

    if (!$salesOrders)
        throw new Exception("Error executing sales order query : ($integrationConnect->errno) - $integrationConnect->error");

    if ($salesOrders->num_rows == 0)
        throw new Exception("No SalesOrder Found!");

    $_messages['no_sales_order'] = $salesOrders->num_rows;
    // Cycle through results
    while ($salesOrder = $salesOrders->fetch_object()) {

        try {

            $integrationConnect->autocommit(FALSE);
            $flag = true;
            $_messages['sales_orders'][$salesOrder->salesorder_no] = array();

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
            $multiproduct = array();
            $productaccountarray = array();
            $productlength = "";
            $leadzeroproduct = "";
            $productquantitylength = "";
            $leadzeroproductquantity = "";

            $_messages['sales_orders'][$salesOrder->salesorder_no]['no_products'] = $salesOrderWithProducts->num_rows;

            if (!$salesOrderWithProducts)
                throw new Exception("Problem in fetching products.");

            while ($salesOrderWithProduct = $salesOrderWithProducts->fetch_object()) {

                /**
                 * for check duplicate product and write productname in set file with+
                 */
                if (!in_array($salesOrderWithProduct->productname, $productnamearray)) {
                    $productlength = strlen($salesOrderWithProduct->productname);
                    $productquantitylength = strlen($salesOrderWithProduct->productquantity);

                    if ($productlength < 6) {
                        $leadzeroproduct = leadingzero($productlength);
                    }

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

                    $accountlenth = strlen($salesOrderWithProduct->accountname);
                    if ($accountlenth < 6) {
                        $leadzero = leadingzero(6, $accountlenth);
                    }
                    $finalformataccountname = $leadzero . $salesOrderWithProduct->accountname;
                }

                $finalformatproductname = implode("+", $multiproduct);
                $currentdate = date("Ymd");
                $originalordernomber = "7777" . $salesOrderWithProduct->salesorder_no;

                /**
                 * for find the order no. total length if length 
                 * will be greater then 6 then auto remove from the starting
                 */
                $orderlength = strlen($originalordernomber);

                if ($orderlength > 6) {
                    $accessorderlength = $orderlength - 6;

                    $ordernumber = substr($originalordernomber, $accessorderlength);
                } else
                    $ordernumber = $originalordernomber;

                $deliveryday = date("ymd", strtotime($salesOrderWithProduct->duedate));
                $futuredeliverydate1 = strtotime(date("Y-m-d", strtotime($salesOrderWithProduct->duedate)) . " +1 day");
                $futuredeliverydate = date('ymd', $futuredeliverydate1);

                unset($multiproduct);
                unset($productnamearray);
            }

            $salesOrderWithProducts->close();

            if (!$flag)
                throw new Exception("Problem generating file content.");

            $_content = "HEADERGIZUR           " . $currentdate .
                "18022800M256      RUTIN   .130KF27777100   " .
                "mottagning initierad                               " .
                "                                          001" .
                $finalformataccountname . "1+03751+038" . $ordernumber .
                "+226" . $futuredeliverydate . "+039" .
                $deliveryday . "+040" . $ordernumber . "+" .
                $finalformatproductname . "+C         RUTIN   " .
                ".130KF51125185   Mottagning avslutad    " .
                "BYTES/BLOCKS/RETRIES=1084 /5    /0";

            $_messageQ = array();

            // Send File name & content to SQS 
            $_messageQ['file'] = $fileName;
            $_messageQ['content'] = $_content;

            $_response = $sqs->send_message($amazonqueue_config['_url'], json_encode($_messageQ));

            if ($_response->status !== 200)
                throw new Exception("Error in sending file to messageQ.");

            $updateStatus = $integrationConnect->query("UPDATE salesorder_interface
                            SET sostatus = 'Delivered' where salesorder_no = '$salesOrder->salesorder_no'");

            if (!$updateStatus)
                throw new Exception("Error updating status to delivered.");

            updateMessage(&$_messages, $salesOrder->salesorder_no, true, $fileName, "Successfully sent to messageQ.");
            $integrationConnect->commit();
        } catch (Exception $e) {
            $integrationConnect->rollback();
            updateLogMessage(&$_messages, $salesOrder->salesorder_no, false, $fileName, $e->getMessage());
        }
    }

    $salesOrders->close();
} catch (Exception $e) {
    $_messages['message'] = $e->getMessage();
}

$integrationConnect->close();

syslog(LOG_WARNING, json_encode($_messages));
echo json_encode($_messages);
?>

<?php

function updateLogMessage($m, $so, $status, $filename, $msg)
{
    $m['sales_orders'][$so]['status'] = $status;
    $m['sales_orders'][$so]['file'] = $filename;
    $m['sales_orders'][$so]['message'] = $msg;
}

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
?>