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
 * Load the required files.
 */
require_once __DIR__ . '/../config.inc.php';
require_once __DIR__ . '/../config.database.php';
require_once __DIR__ . '/../config.sqs.inc.php';

/*
 * Open connection to system logger
 */
openlog(
    "phpcronjob2", LOG_PID | LOG_PERROR, LOG_LOCAL0
);

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
            $dbconfigIntegration['db_name']
    );

    /*
     * Message array to store log
     */
    $messages = array();

    /*
     * $duplicateFile used to remove duplicacy in
     * file names, account wise.
     */
    $duplicateFile = array();

    /*
     * Fetch all pending (created, approved) sales 
     * orders from integration database.
     */
    $salesOrders = $integrationConnect->query(
        "SELECT salesorder_no,
         accountname 
         FROM salesorder_interface
         WHERE sostatus IN ('created', 'approved') 
         GROUP BY salesorder_no, accountname 
         LIMIT 0, " . $dbconfigBatchVariable['batch_valiable']
    );

    /*
     * If query return false / error, raise the exception.
     */
    if (!$salesOrders)
        throw new Exception(
            "Error executing sales order query : " .
            "($integrationConnect->errno) - $integrationConnect->error"
        );

    /*
     * If number of sales order fetched is 0, raise the exception.
     */
    if ($salesOrders->num_rows == 0)
        throw new Exception("No SalesOrder Found!");

    /*
     * Store sales order numbers in message array.
     */
    $messages['no_sales_order'] = $salesOrders->num_rows;
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
            $mess = array();

            /*
             * $createdDate is being used in file name
             * and the following line of 
             * code is preventing the duplicacy of file name by 
             * increasing 1 minute for every salesorder for the same client.
             * check issue:
             * https://github.com/gizur/gizurcloud/
             * issues/225#issuecomment-14158434
             */
            if (empty($duplicateFile[$salesOrder->accountname]))
                $createdDate = date("YmdHi");
            else {
                $cnt = count($duplicateFile[$salesOrder->accountname]);
                $createdDate = date("YmdHi", strtotime("+$cnt minutes"));
            }

            $duplicateFile[$salesOrder->accountname][] = $createdDate;

            /*
             * Generate the file name.
             */
            $fileName = "SET.GZ.FTP.IN.BST.$createdDate." .
                "$salesOrder->accountname";

            /*
             * Get all the products of current sales order
             */
            $salesOrderWithProducts = $integrationConnect->query(
                "SELECT * FROM salesorder_interface " .
                "WHERE salesorder_no = '$salesOrder->salesorder_no'" .
                " AND sostatus in ('created', 'approved')"
            );

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
            $mess['no_products'] = $salesOrderWithProducts->num_rows;
            $messages['sales_orders'][$salesOrder->salesorder_no] = $mess;

            /*
             * If error executing the query, raise the exception.
             */
            if (!$salesOrderWithProducts)
                throw new Exception("Problem in fetching products.");

            while ($sOWProduct = $salesOrderWithProducts->fetch_object()) {

                /**
                 * for check duplicate product and 
                 * write productname in set file with+
                 */
                if (!in_array($sOWProduct->productname, $productnamearray)) {
                    $productlength = strlen($sOWProduct->productname);
                    $productquantitylength = strlen(
                        $sOWProduct->productquantity
                    );

                    if ($productlength < 6) {
                        $leadzeroproduct = Functions::leadingzero(
                                $productlength
                        );
                    }

                    if ($productquantitylength < 3) {
                        $leadzeroproductquantity = Functions::leadingzero(
                                3, $productquantitylength
                        );
                    }

                    $multiproduct[] = "189" . $leadzeroproduct .
                        $sOWProduct->productname .
                        $leadzeroproductquantity .
                        $sOWProduct->productquantity;
                    $productnamearray[] = $sOWProduct->productname;
                }

                /**
                 * for check duplicate account name 
                 * and write account name in set file
                 */
                $inArr = in_array(
                    $sOWProduct->accountname, $productaccountarray
                );
                if (!$inArr) {

                    $accountlenth = strlen($sOWProduct->accountname);
                    if ($accountlenth < 6) {
                        $leadzero = Functions::leadingzero(6, $accountlenth);
                    }
                    $finalformataccountname = $leadzero .
                        $sOWProduct->accountname;
                }

                $salesID = preg_replace(
                    '/[A-Z]/', '', $sOWProduct->salesorder_no
                );
                $originalordernomber = "7777" . $salesID;

                /**
                 * for find the order no. total length if length 
                 * will be greater then 6 then auto remove from the starting
                 */
                $orderlength = strlen($originalordernomber);

                if ($orderlength > 6) {
                    $accessorderlength = $orderlength - 6;

                    $ordernumber = substr(
                        $originalordernomber, $accessorderlength
                    );
                } else
                    $ordernumber = $originalordernomber;

                if(!empty($sOWProduct->duedate) 
                    && $sOWProduct->duedate != '0000-00-00')
                $deliveryday = date(
                    "ymd", strtotime($sOWProduct->duedate)
                );
                else
                    $deliveryday = date('ymd');
                
                $futuredeliveryDate = strtotime(
                    date("Y-m-d", strtotime($deliveryday)) . "+2 day"
                );
                $futuredeliverydate = date('ymd', $futuredeliveryDate);

                
            }
            $currentdate = date("YmdHi");                
            $finalformatproductname = implode("+", $multiproduct);
            unset($multiproduct);
            unset($productnamearray);
            /*
             * Free the product resultset.
             */
            $salesOrderWithProducts->close();

            $milliSec = Functions::getMilliSecond();
            /*
             * Generate the file content
             */
            $contentF = "HEADERGIZUR           " . $currentdate .
                "{$milliSec}M256      RUTIN   .130KF27777100   " .
                "mottagning initierad                               " .
                "                                          001" .
                $finalformataccountname . "1+03751+038" . $ordernumber .
                "+226" . $futuredeliverydate . "+039" .
                $deliveryday . "+040" . $ordernumber . "+" .
                $finalformatproductname . "+C         RUTIN   " .
                ".130KF27777100   Mottagning avslutad    " .
                "BYTES/BLOCKS/RETRIES=1084 /5    /0";

            /*
             * Get the message from the responce.
             */
            $messageID = $responseQ->body->SendMessageResult->MessageId;
            /*
             * Update sales order status to Delivered.
             */
            $updateStatus = $integrationConnect->query(
                "UPDATE salesorder_interface
                SET sostatus = 'Delivered' 
                where salesorder_no = '$salesOrder->salesorder_no'"
            );

            /*
             * If unable to update status, raise the exception and
             * delete the file content from message queue.
             */
            if (!$updateStatus)
                throw new Exception("Error updating status to delivered.");

            /*
             * Initialise an array to store file name and content.
             */
            $messageQ = array();

            $messageQ['file'] = $fileName;
            $messageQ['content'] = $contentF;

            /*
             * Store file name and file content to messageQ.
             */
            $responseQ = $sqs->send_message(
                $amazonqueueConfig['_url'], json_encode($messageQ)
            );

            /*
             * If unable to store file content at queue,
             * raise the exception
             */
            if ($responseQ->status !== 200)
                throw new Exception("Error in sending file to messageQ.");

            /*
             * Store file in S3 Bucket
             */
            $sThree = new AmazonS3();
            $responseSThree = $sThree->create_object(
                $amazonSThree['bucket'], 
                $fileName, 
                array(
                    'body' => $contentF,
                    'contentType' => 'plain/text',
                    'headers' => array(
                        'Cache-Control' => 'max-age',
                        'Content-Language' => 'en-US',
                        'Expires' =>
                        'Thu, 01 Dec 1994 16:00:00 GMT',
                    ))
            );
            
            if (!$responseSThree->isOK()) {
                throw new Exception(
                    "Unable to save file in S3 bucket " . 
                    "(" . $amazonSThree['bucket'] . ")"
                );
            }
            /*
             * If everything goes right update the sales order ststus to true 
             * in message array.
             */

            Functions::updateLogMessage(
                &$messages, $salesOrder->salesorder_no, 
                true, 
                $fileName, 
                "Successfully sent to messageQ."
            );

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
            Functions::updateLogMessage(
                &$messages, 
                $salesOrder->salesorder_no, 
                false, 
                $fileName, 
                $e->getMessage()
            );
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
    $messages['message'] = $e->getMessage();
}

/*
 * Close the connections
 */
$integrationConnect->close();

/*
 * Update system logs and print log messages.
 */
syslog(LOG_WARNING, json_encode($messages));
echo json_encode($messages);

class Functions
{
    /*
     * updateLogMessage fuction is used to update the message array.
     */

    static function updateLogMessage($m, $so, $status, $filename, $msg)
    {
        $m['sales_orders'][$so]['status'] = $status;
        $m['sales_orders'][$so]['file'] = $filename;
        $m['sales_orders'][$so]['message'] = $msg;
    }

    /**
     * auto adding zero befor number  
     */
    static function leadingzero($limitnumber = 6, $number = 0)
    {
        $leadzero = "";
        $leadingzero = $limitnumber - $number;
        for ($i = 0; $i < $leadingzero; $i++) {
            $leadzero.= 0;
        }
        return $leadzero;
    }

    /*
     * Get 4 first digit from millisecond
     */

    static function getMilliSecond()
    {
        $seconds = round(microtime(true) * 1000);
        $remainder = substr("$seconds", -4);

        return $remainder;
    }

}