#!/usr/bin/php
<?php
/*
 * Load the required files.
 */
require_once __DIR__ . '/../config.inc.php';
require_once __DIR__ . '/../config.database.php';
require_once __DIR__ . '/../config.sqs.inc.php';

/*
 * Open connection to system logger
 */
openlog("phpcronjob2", LOG_PID | LOG_PERROR, LOG_LOCAL0);

/*
 * Start try block to catch the exceptions raised.
 */
try {
    /*
     * Try to connect to integration database,
     * as per the settings.
     */
    $integrationConnect = new Connect(
            $dbconfigIntegration['db_server'],
            $dbconfigIntegration['db_username'],
            $dbconfigIntegration['db_password'],
            $dbconfigIntegration['db_name']);

    /*
     * Message array to store log
     */
    $_messages = array();
    
    /*
     * $_dublicate_file_name used to remove duplicacy in
     * file names, account wise.
     */
    $_dublicate_file_name = array();

    /*
     * Fetch all pending (created, approved) sales 
     * orders from integration database.
     */
    $salesOrders = $integrationConnect->query("SELECT salesorder_no, accountname 
           FROM salesorder_interface
           WHERE sostatus IN ('created', 'approved') 
           GROUP BY salesorder_no, accountname LIMIT 0, " . $dbconfigBatchVariable['batch_valiable']);

    /*
     * If query return false / error, raise the exception.
     */
    if (!$salesOrders)
        throw new Exception("Error executing sales order query : ($integrationConnect->errno) - $integrationConnect->error");

    /*
     * If number of sales order fetched is 0, raise the exception.
     */
    if ($salesOrders->num_rows == 0)
        throw new Exception("No SalesOrder Found!");

    /*
     * Store sales order numbers in message array.
     */
    $_messages['no_sales_order'] = $salesOrders->num_rows;
    /*
     * Iterate through sales orders.
     */
    while ($salesOrder = $salesOrders->fetch_object()) {

        /*
         * Try block to catch sales order specific exceptions.
         */
        try {
            /*
             * Set auto commit false for integration server.
             */
            $integrationConnect->autocommit(FALSE);
            
            /*
             * Store sales order number in Message array.
             */
            $_messages['sales_orders'][$salesOrder->salesorder_no] = array();
            
            /*
             * $createdDate is being used in file name
             * and the following line of 
             * code is preventing the duplicacy of file name by 
             * increasing 1 minute for every salesorder for the same client.
             * check issue:
             * https://github.com/gizur/gizurcloud/issues/225#issuecomment-14158434
             */
            if (empty($_dublicate_file_name[$salesOrder->accountname]))
                $createdDate = date("YmdHi");
            else{
                $cnt = count($_dublicate_file_name[$salesOrder->accountname]);
                $createdDate = date("YmdHi", strtotime("+$cnt minutes"));
            }

            $_dublicate_file_name[$salesOrder->accountname][] = $createdDate;

            /*
             * Generate the file name.
             */
            $fileName = "SET.GZ.FTP.IN.BST.$createdDate.$salesOrder->accountname";

            /*
             * Get all the products of current sales order
             */
            $salesOrderWithProducts = $integrationConnect->query("SELECT * FROM salesorder_interface " .
                "WHERE salesorder_no = '$salesOrder->salesorder_no'" .
                " AND sostatus in ('created', 'approved')");

            /*
             * Initialise variables used in creating SET file contents.
             */
            $leadzero = "";
            $accountlenth = "";
            $productnamearray = array();
            $multiproduct = array();
            $productaccountarray = array();
            $productlength = "";
            $leadzeroproduct = "";
            $productquantitylength = "";
            $leadzeroproductquantity = "";

            /*
             * Store number of products in sales order.
             */
            $_messages['sales_orders'][$salesOrder->salesorder_no]['no_products'] = $salesOrderWithProducts->num_rows;

            /*
             * If error executing the query, raise the exception.
             */
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

            /*
             * Free the product resultset.
             */
            $salesOrderWithProducts->close();

            /*
             * Generate the file content
             */
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

            /*
             * Get the message from the responce.
             */
            $_messageID = $_response->body->SendMessageResult->MessageId;
            /*
             * Update sales order status to Delivered.
             */
            $updateStatus = $integrationConnect->query("UPDATE salesorder_interface
                            SET sostatus = 'Delivered' where salesorder_no = '$salesOrder->salesorder_no'");

            /*
             * If unable to update status, raise the exception and
             * delete the file content from message queue.
             */
            if (!$updateStatus)
                throw new Exception("Error updating status to delivered.");
            
            /*
             * Initialise an array to store file name and content.
             */
            $_messageQ = array();

            $_messageQ['file'] = $fileName;
            $_messageQ['content'] = $_content;

            /*
             * Store file name and file content to messageQ.
             */
            $_response = $sqs->send_message($amazonqueueConfig['_url'], json_encode($_messageQ));

            /*
             * If unable to store file content at queue,
             * raise the exception
             */
            if ($_response->status !== 200)
                throw new Exception("Error in sending file to messageQ.");
            
            /*
             * If everything goes right update the sales order ststus to true 
             * in message array.
             */

            updateLogMessage(&$_messages, $salesOrder->salesorder_no, true, $fileName, "Successfully sent to messageQ.");
            
            /*
             * Commit the changes.
             */
            $integrationConnect->commit();
            
            /*
             * If any exception occurs during the process rollback the
             * connection and update message array.
             */
        } catch (Exception $e) {
            $integrationConnect->rollback();
            updateLogMessage(&$_messages, $salesOrder->salesorder_no, false, $fileName, $e->getMessage());
        }
    }

    /*
     * Release sales order resultset
     */
    $salesOrders->close();
    /*
     * Catch the exceptions
     */
} catch (Exception $e) {
    $_messages['message'] = $e->getMessage();
}

/*
 * Close the connections
 */
$integrationConnect->close();

/*
 * Update system logs and print log messages.
 */
syslog(LOG_WARNING, json_encode($_messages));
echo json_encode($_messages);
?>

<?php

/*
 * updateLogMessage fuction is used to update the message array.
 */
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