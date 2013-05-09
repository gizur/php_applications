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
require_once __DIR__ . '/../../../../../lib/aws-php-sdk/sdk.class.php';

class PhpBatchTwo
{

    private $integrationConnect;
    private $sqs;
    private $messages = array();
    private $duplicateFile = array();
    private $mosDuplicateFile = array();
    private $sThree;

    public function __construct()
    {
        openlog(
            "phpcronjob2", LOG_PID | LOG_PERROR, LOG_LOCAL0
        );

        syslog(
            LOG_INFO, "Trying to connect to integration database"
        );

        /*
         * Trying to connect to integration database
         */
        $this->integrationConnect = new mysqli(
            Config::$dbIntegration['db_server'], Config::$dbIntegration['db_username'], Config::$dbIntegration['db_password'], Config::$dbIntegration['db_name'], Config::$dbIntegration['db_port']
        );

        if ($this->integrationConnect->connect_errno)
            throw new Exception('Unable to connect with integration DB');

        syslog(
            LOG_INFO, "Connected with integration db"
        );

        syslog(
            LOG_INFO, "Trying connecting with Amazon SQS"
        );

        $this->sqs = new AmazonSQS();

        syslog(
            LOG_INFO, "Connected with Amazon SQS"
        );

        syslog(
            LOG_INFO, "Trying connecting with Amazon sThree"
        );

        $this->sThree = new AmazonS3();

        syslog(
            LOG_INFO, "Connected with Amazon sThree"
        );
    }

    protected function getSalesOrders()
    {
        syslog(LOG_INFO, "In getSalesOrders() : Preparing sales order query");

        $salesOrdersQuery = "SELECT * FROM sales_orders SO 
            WHERE SO.status IN ('Created','Approved')
            LIMIT 0, " . Config::$batchVariable;

        syslog(
            LOG_INFO, "In getSalesOrders() : Executing Query: " . $salesOrdersQuery
        );

        $salesOrders = $this->integrationConnect->query($salesOrdersQuery);

        if (!$salesOrders) {
            syslog(
                LOG_WARNING, "In getSalesOrders() : Error executing sales order query :" .
                " ({$this->integrationConnect->errno}) - " .
                "{$this->integrationConnect->error}"
            );
            throw new Exception(
            "In getSalesOrders() : Error executing sales order query : " .
            "({$this->integrationConnect->errno}) - " .
            "{$this->integrationConnect->error}"
            );
        }

        if ($salesOrders->num_rows == 0) {
            syslog(
                LOG_WARNING, "In getSalesOrders() : No Sales Order Found!"
            );
            throw new Exception("In getSalesOrders() : No Sales Order Found!");
        }

        return $salesOrders;
    }

    protected function getProductsBySalesOrderId($salesOrderId)
    {
        syslog(
            LOG_INFO, "In getProductsBySalesOrderId($salesOrderId) : Fetching products"
        );
        /*
         * Fetch current sales order products.
         */
        $salesOrderProducts = $this->integrationConnect->query(
            "SELECT *" .
            "FROM sales_order_products SO " .
            "WHERE SO.sales_order_id = '$salesOrderId'"
        );

        syslog(
            LOG_INFO, "Total number of products ($salesOrderId): " .
            $salesOrderProducts->num_rows
        );

        return $salesOrderProducts;
    }

    protected function updateIntegrationSalesOrder(
    $salesOrderID, $status = 'Delivered'
    )
    {
        syslog(
            LOG_INFO, "In updateIntegrationSalesOrder($salesOrderID, $status) : " .
            "Updating sales order ($salesOrderID) $status"
        );

        $updateSaleOrder = $this->integrationConnect->query(
            "UPDATE sales_orders SET " .
            "status = '$status' WHERE id = " .
            "'$salesOrderID' LIMIT 1"
        );

        if (!$updateSaleOrder) {
            syslog(
                LOG_WARNING, "In updateIntegrationSalesOrder($salesOrderID, $status) : " .
                "Error updating salesorder"
            );
            throw new Exception(
            "In updateIntegrationSalesOrder($salesOrderID, $status) : " .
            "Error updating salesorder"
            );
        }

        return $updateSaleOrder;
    }

    protected function createSETFile($salesOrder, &$msg)
    {
        $cnt = 0;

        $soProducts = $this->getProductsBySalesOrderId(
            $salesOrder->id
        );
        
        $msg[$salesOrder->salesorder_no]['count']
            = $soProducts->num_rows;

        if (empty($this->duplicateFile[$salesOrder->accountname]))
            $createdDate = date("YmdHi");
        else {
            $cnt = count($this->duplicateFile[$salesOrder->accountname]);
            $createdDate = date("YmdHi", strtotime("+$cnt minutes"));
        }

        $this->duplicateFile[$salesOrder->accountname][] = $createdDate;

        /*
         * Generate the file name.
         */
        $fileName = "SET.GZ.FTP.IN.BST.$createdDate." .
            "$salesOrder->accountname";
        
        $msg[$salesOrder->salesorder_no]['set']['file'] = $fileName;
        /*
         * Initialise variables used in creating SET file contents.
         */
        $leadzero = "";
        $productnamearray = array();
        $multiproduct = array();
        $productlength = "";
        $leadzeroproduct = "";
        $productquantitylength = "";
        $leadzeroproductquantity = "";

        while ($sOWProduct = $soProducts->fetch_object()) {

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
                        6, $productlength
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
        }

        $accountlenth = strlen($salesOrder->accountname);
        if ($accountlenth < 6) {
            $leadzero = Functions::leadingzero(6, $accountlenth);
        }
        $finalformataccountname = $leadzero .
            $salesOrder->accountname;

        $salesID = preg_replace(
            '/[A-Z]/', '', $salesOrder->salesorder_no
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
        }
        else
            $ordernumber = $originalordernomber;

        if (!empty($salesOrder->duedate) && $salesOrder->duedate != '0000-00-00')
            $deliveryday = date(
                "ymd", strtotime($salesOrder->duedate)
            );
        else
            $deliveryday = date('ymd');

        $futuredeliveryDate = strtotime(
            date("Y-m-d", strtotime($deliveryday)) . "+2 day"
        );
        $futuredeliverydate = date('ymd', $futuredeliveryDate);

        $currentdate = date("YmdHi");
        $finalformatproductname = implode("+", $multiproduct);
        unset($multiproduct);
        unset($productnamearray);

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
            "BYTES/BLOCKS/RETRIES=1084 /5    /0.";

        syslog(
            LOG_INFO, "File $fileName content generated"
        );
        /*
         * Add next line character at every 80 length
         */
        syslog(
            LOG_INFO, "Adding next line char $fileName at every 80 chars"
        );
        $pieces = str_split($contentF, 80);
        $contentF = join("\n", $pieces);

        syslog(
            LOG_INFO, "File $fileName contents: " . $contentF
        );

        $messageQ = array();

        $messageQ['file'] = $fileName;
        $messageQ['content'] = $contentF;
        $messageQ['type'] = 'SET';

        return $messageQ;
    }

    protected function createMOSFile($salesOrder, &$msg)
    {
        $cnt = 0;
        
        $soProducts = $this->getProductsBySalesOrderId(
            $salesOrder->id
        );
        
        $msg[$salesOrder->salesorder_no]['count']
            = $soProducts->num_rows;
        
        if (empty($this->mosDuplicateFile[$salesOrder->accountname]))
            $createdDate = date("YmdHi");
        else {
            $cnt = count($this->mosDuplicateFile[$salesOrder->accountname]);
            $createdDate = date("YmdHi", strtotime("+$cnt minutes"));
        }

        $this->mosDuplicateFile[$salesOrder->accountname][] = $createdDate;

        /*
         * Generate the file name.
         */
        $fileName = "MOS.GZ.FTP.IN.BST.$createdDate." .
            "$salesOrder->accountname";
        $msg[$salesOrder->salesorder_no]['mos']['file'] = $fileName;

        $sequence = 1;
        $contentF = "";
        
        while ($sOWProduct = $soProducts->fetch_object()) {
            $seqZero = Functions::leadingzero(5, strlen((string)$sequence));
            
            if (!empty($salesOrder->duedate) && $salesOrder->duedate != '0000-00-00')
                $deliveryday = date(
                    "ymd", strtotime($salesOrder->duedate)
                );
            else
                $deliveryday = date('ymd');
        
            $week = date('W', strtotime($deliveryday));

            $campaignWeek = date('W', strtotime(
                date("ymd", strtotime($deliveryday)) . "+2 day"
            ));
            $weekZero = Functions::leadingzero(4, strlen((string)$week));
            $campWeekZero = Functions::leadingzero(
                4, strlen((string)$campaignWeek)
            );
            
            $dummyOne = (string)'3095';
            $vgr = (string)'000';
            $art = (string)'0000';
            $varubet = (string)'0000';
            $store = (string)$salesOrder->accountname;
            
            $quantity = (string)$sOWProduct->productquantity;
            $qtnZero = Functions::leadingzero(7, strlen((string)$quantity));
            
            $dummyTwo = (string)'1000';
            
            $reservationId = (string)'0000000';
            
            $contentF .= "{$seqZero}{$sequence}{$dummyOne}" .
                "{$vgr}{$art}{$varubet}{$store}" .
                "{$weekZero}{$week}{$qtnZero}{$quantity}" . 
                "{$dummyTwo}{$campWeekZero}{$campaignWeek}{$reservationId}\n";
            $sequence++;
        }
        
        syslog(
            LOG_INFO, "File $fileName contents: " . $contentF
        );

        $messageQ = array();

        $messageQ['file'] = $fileName;
        $messageQ['content'] = $contentF;
        $messageQ['type'] = 'MOS';

        return $messageQ;
    }

    protected function storeFileInSThree(
    $bucket, $fileFolder, $fileName, $contentF
    )
    {
        /*
         * Store file in S3 Bucket
         */
        syslog(
            LOG_INFO, "Store file in S3 Bucket"
        );

        $responseSThree = $this->sThree->create_object(
            $bucket, $fileFolder . $fileName, array(
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
            "Unable to save file $fileName in S3 bucket " .
            "($bucket)"
            );
            syslog(
                LOG_WARNING, "Unable to save file $fileName in S3 bucket " .
                "($bucket)"
            );
        }
        return $responseSThree;
    }

    protected function storeFileInMessageQ($qUrl, $messageQ)
    {
        /*
         * Store file name and file content to messageQ.
         */
        syslog(
            LOG_INFO, "Store file name and file content to messageQ."
        );

        $responseQ = $this->sqs->send_message(
            $qUrl, json_encode($messageQ)
        );

        /*
         * If unable to store file content at queue,
         * raise the exception
         */
        if ($responseQ->status !== 200) {            
            syslog(
                LOG_WARNING, "Error in sending file to messageQ."
            );
            throw new Exception("Error in sending file to messageQ.");
        }

        return $responseQ;
    }

    public function init()
    {
        try {
            $salesOrders = $this->getSalesOrders();
            $numberSalesOrders = $salesOrders->num_rows;

            /*
             * Update message array with number of sales orders.
             */
            $this->messages['count'] = $numberSalesOrders;
            $msg = &$this->messages['salesorders'];
            
            while ($salesOrder = $salesOrders->fetch_object()) {
                try {
                    /*
                     * Disable auto commit.
                     */
                    syslog(
                        LOG_INFO, "Disabling auto commit"
                    );
                    $this->integrationConnect->autocommit(FALSE);

                    $fileName = "";
                    
                    if ($salesOrder->set == 'No' && $salesOrder->mos == 'No') {
                        syslog(
                            LOG_WARNING, 
                            "SET and MOS both are set NO for sales order " .
                            "$salesOrder->salesorder_no " .
                            "($salesOrder->accountname)."
                        );
                        throw new Exception(
                            "SET and MOS both are set NO for sales order " .
                            "$salesOrder->salesorder_no " .
                            "($salesOrder->accountname)."
                        );
                    }
                    
                    if ($salesOrder->set == 'Yes') {
                        
                        $msg[$salesOrder->salesorder_no]['set']['status'] = false;
                        
                        $setFile = $this->createSETFile($salesOrder, $msg);

                        $this->storeFileInSThree(
                            Config::$amazonSThree['bucket'], 
                            Config::$amazonSThree['setFolder'], 
                            $setFile['file'], 
                            $setFile['content']
                        );

                        $this->storeFileInMessageQ(
                            Config::$amazonQ['_url'], json_encode($setFile)
                        );
                        
                        $msg[$salesOrder->salesorder_no]['set']['status'] = true;
                        $fileName = $setFile['file'];
                    } 
                    
                    if($salesOrder->mos == 'Yes') {
                        $msg[$salesOrder->salesorder_no]['mos']['status'] = false;
                        
                        $mosFile = $this->createMOSFile($salesOrder, $msg);

                        $this->storeFileInSThree(
                            Config::$amazonSThree['bucket'], 
                            Config::$amazonSThree['setFolder'], 
                            $mosFile['file'], 
                            $mosFile['content']
                        );

                        $this->storeFileInMessageQ(
                            Config::$amazonQ['_url'], json_encode($mosFile)
                        );
                        
                        $msg[$salesOrder->salesorder_no]['mos']['status'] = true;
                        $fileName = $mosFile['file'];
                    }
                    
                    $this->updateIntegrationSalesOrder(
                        $salesOrder->id, 'Delivered'
                    );
                    /*
                     * Commit the databases.
                     */
                    $this->integrationConnect->commit();
                } catch (Exception $e) {
                    $numberSalesOrders--;
                    /*
                     * Rollback the connections
                     */
                    $this->integrationConnect->rollback();
                }
            }

            $this->messages['message'] = "$numberSalesOrders number " .
                "of sales orders processed.";
        } catch (Exception $e) {
            $this->messages['message'] = $e->getMessage();
            /*
             * Rollback the connections
             */
            $this->integrationConnect->rollback();
        }
        
        syslog(
            LOG_INFO, json_encode($this->messages)
        );
        echo json_encode($this->messages);
    }

}

class Functions
{
    /**
     * auto adding zero befor number  
     */
    static function leadingzero($limitnumber = 6, $number = 0)
    {
        $leadzero = "";
        $leadingzero = $limitnumber - $number;
        for ($i = 0; $i < $leadingzero; $i++) {
            $leadzero .= '0';
        }
        return $leadzero;
    }

    /*
     * Get 4 last digit from microtime
     */

    static function getMilliSecond()
    {
        $seconds = round(microtime(true) * 1000);
        $remainder = substr("$seconds", -4);

        return $remainder;
    }

}

$phpBatchTwo = new PhpBatchTwo();
$phpBatchTwo->init();