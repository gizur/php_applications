#!/usr/bin/php
<?php
/**
 * @category   Cronjobs
 * @package    Integration
 * @subpackage CronJob
 * @author     Prabhat Khera <gizur-ess-prabhat@gizur.com>
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

    private $_integrationConnect;
    private $_sqs;
    private $_messages = array();
    private $_duplicateFile = array();
    private $_sThree;

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
        $this->_integrationConnect = new mysqli(
            Config::$dbIntegration['db_server'], 
            Config::$dbIntegration['db_username'], 
            Config::$dbIntegration['db_password'], 
            Config::$dbIntegration['db_name'], 
            Config::$dbIntegration['db_port']
        );

        if ($this->_integrationConnect->connect_errno)
            throw new Exception('Unable to connect with integration DB');

        syslog(
            LOG_INFO, "Connected with integration db"
        );

        syslog(
            LOG_INFO, "Trying connecting with Amazon SQS"
        );

        $this->_sqs = new AmazonSQS();

        syslog(
            LOG_INFO, "Connected with Amazon SQS"
        );

        syslog(
            LOG_INFO, "Trying connecting with Amazon _sThree"
        );

        $this->_sThree = new AmazonS3();

        syslog(
            LOG_INFO, "Connected with Amazon _sThree"
        );
    }

    protected function getSalesOrdersForSet()
    {
        syslog(
            LOG_INFO, 
            "In getSalesOrdersForSet() : Preparing sales order query"
        );

        $salesOrdersQuery = "SELECT * FROM sales_orders SO 
            WHERE SO.set_status IN ('Created','Approved') AND SO.set = 'Yes'
            LIMIT 0, " . Config::$batchVariable;

        syslog(
            LOG_INFO, 
            "In getSalesOrdersForSet() : Executing Query: " . $salesOrdersQuery
        );

        $salesOrders = $this->_integrationConnect->query($salesOrdersQuery);

        if (!$salesOrders) {
            syslog(
                LOG_WARNING,
                "In getSalesOrdersForSet() : " .
                "Error executing sales order query :" .
                " ({$this->_integrationConnect->errno}) - " .
                "{$this->_integrationConnect->error}"
            );
            throw new Exception(
                "In getSalesOrdersForSet() : Error " .
                "executing sales order query : " .
                "({$this->_integrationConnect->errno}) - " .
                "{$this->_integrationConnect->error}"
            );
        }

        if ($salesOrders->num_rows == 0) {
            syslog(
                LOG_WARNING, "In getSalesOrdersForSet() : No Sales Order Found!"
            );
            throw new Exception(
                "In getSalesOrdersForSet() : No Sales Order Found!"
            );
        }

        return $salesOrders;
    }

    protected function getAccountsForMos()
    {
        syslog(
            LOG_INFO,
            "In getAccountsForMos() : Preparing sales order query"
        );

        $salesOrdersQuery = "SELECT DISTINCT SO.accountname FROM sales_orders SO
            WHERE SO.mos_status IN ('Created','Approved') AND SO.mos = 'Yes'
            LIMIT 0, " . Config::$batchVariable;

        syslog(
            LOG_INFO,
            "In getAccountsForMos() : Executing Query: " . $salesOrdersQuery
        );

        $salesOrders = $this->_integrationConnect->query($salesOrdersQuery);

        if (!$salesOrders) {
            syslog(
                LOG_WARNING, 
                "In getAccountsForMos() : Error executing sales order query :" .
                " ({$this->_integrationConnect->errno}) - " .
                "{$this->_integrationConnect->error}"
            );
            throw new Exception(
                "In getAccountsForMos() : " .
                "Error executing sales order query : " .
                "({$this->_integrationConnect->errno}) - " .
                "{$this->_integrationConnect->error}"
            );
        }

        if ($salesOrders->num_rows == 0) {
            syslog(
                LOG_WARNING, "In getAccountsForMos() : No Sales Order Found!"
            );
            throw new Exception(
                "In getAccountsForMos() : No Sales Order Found!"
            );
        }

        return $salesOrders;
    }

    protected function getProductsBySalesOrderId($salesOrderId)
    {
        syslog(
            LOG_INFO, 
            "In getProductsBySalesOrderId($salesOrderId) : Fetching products"
        );
        /*
         * Fetch current sales order products.
         */
        $salesOrderProducts = $this->_integrationConnect->query(
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

    protected function getProductsByAccountName($accountname)
    {
        syslog(
            LOG_INFO,
            "In getProductsByAccountName($accountname) : Fetching products"
        );
        /*
         * Fetch current sales order products.
         */
        $query = "SELECT * " .
            "FROM sales_order_products SOP LEFT JOIN sales_orders SO ON " .
            "SO.id = SOP.sales_order_id " .
            "WHERE SO.accountname = '$accountname'";
        
        syslog(
            LOG_INFO, "Fetching products ($accountname): $query"
        );
        
        $products = $this->_integrationConnect->query($query);

        syslog(
            LOG_INFO, "Total number of products ($accountname): " .
            $products->num_rows
        );

        return $products;
    }

    protected function updateIntegrationSalesOrder(
    $salesOrderID, $column, $status = 'Delivered'
    )
    {
        syslog(
            LOG_INFO, 
            "In updateIntegrationSalesOrder($salesOrderID, $status) : " .
            "Updating sales order ($salesOrderID) column $column to $status"
        );

        $updateSaleOrder = $this->_integrationConnect->query(
            "UPDATE sales_orders SET " .
            "$column = '$status' WHERE id = " .
            "'$salesOrderID' LIMIT 1"
        );

        if (!$updateSaleOrder) {
            syslog(
                LOG_WARNING,
                "In updateIntegrationSalesOrder($salesOrderID, $status) : " .
                "Error updating sales order"
            );
            throw new Exception(
                "In updateIntegrationSalesOrder($salesOrderID, $status) : " .
                "Error updating sales order"
            );
        }

        return $updateSaleOrder;
    }
    
    protected function updateIntegrationSalesOrderByAccountName(
    $accountname, $column, $status = 'Delivered'    
    )
    {
        syslog(
            LOG_INFO,
            "In updateIntegrationSalesOrderByAccountName(" .
            "$accountname, $column, $status) : " .
            "Updating store ($accountname) column $column to $status"
        );

        $updateSaleOrder = $this->_integrationConnect->query(
            "UPDATE sales_orders SO SET " .
            "SO.$column = '$status' WHERE SO.accountname = " .
            "'$accountname'"
        );

        if (!$updateSaleOrder) {
            syslog(
                LOG_WARNING,
                "In updateIntegrationSalesOrderByAccountName(" .
                " $accountname, $column, $status) : " .
                "Error updating sales orders"
            );
            throw new Exception(
                "In updateIntegrationSalesOrderByAccountName(" .
                " $accountname, $column, $status) : " .
                "Error updating sales orders"
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

        if (empty($this->_duplicateFile[$salesOrder->accountname]))
            $createdDate = date("YmdHi");
        else {
            $cnt = count($this->_duplicateFile[$salesOrder->accountname]);
            $createdDate = date("YmdHi", strtotime("+$cnt minutes"));
        }

        $this->_duplicateFile[$salesOrder->accountname][] = $createdDate;

        /*
         * Generate the file name.
         */
        $fileName = "SET.GZ.FTP.IN.BST.$createdDate." .
            "$salesOrder->accountname";

        $msg[$salesOrder->salesorder_no]['file'] = $fileName;
        /*
         * Initialize variables used in creating SET file contents.
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
             * Check duplicate products and 
             * write product name in the set file separating with +
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
         * If length of order number is 
         * greater then 6 then auto remove 
	 * extra digits from the starting
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

        if (!empty($salesOrder->duedate) 
            && $salesOrder->duedate != '0000-00-00')
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
        $contentF = join(Config::$lineBreak, $pieces);

        syslog(
            LOG_INFO, "File $fileName contents: " . $contentF
        );

        $messageQ = array();

        $messageQ['file'] = $fileName;
        $messageQ['content'] = $contentF;
        $messageQ['type'] = 'SET';

        return $messageQ;
    }

    protected function createMOSFile($account, &$msg)
    {
        $soProducts = $this->getProductsByAccountName(
            $account->accountname
        );

        $msg[$account->accountname]['count'] = $soProducts->num_rows;

        $createdDate = date("YmdHi");
        $dt = date("Ymd");
        /*
         * Generate the file name.
         */
        $fileName = "MOS.GZ.FTP.IN.BST.$createdDate." .
            "$account->accountname";
        $msg[$account->accountname]['file'] = $fileName;

        $sequence = 1;
        
        $seqZero = Functions::leadingzero(5, strlen((string) $sequence));
        $cntZero = Functions::leadingzero(
            5, strlen((string) $soProducts->num_rows)
        );
        
        $header = "{$seqZero}{$sequence}0000" .
            "{$cntZero}{$soProducts->num_rows}{$dt}1727130700518" .
            "000000000000000000000000000000000000000000000\n";
        $contentF = $header;
        
        $sequence++;
        
        while ($sOWProduct = $soProducts->fetch_object()) {
            $seqZero = Functions::leadingzero(5, strlen((string) $sequence));

            if (!empty($sOWProduct->duedate) && 
                $sOWProduct->duedate != '0000-00-00')
                $deliveryday = date(
                    "ymd", strtotime($sOWProduct->duedate)
                );
            else
                $deliveryday = date('ymd');

            $week = date('yW', strtotime($deliveryday));

            $campaignWeek = date(
                'yW', 
                strtotime($deliveryday . "+1 week")
            );

            $basProId = explode('-', $sOWProduct->bas_product_id);
            
            $dummyOne = (string) '3095';
            $vgr = (string) $basProId[0];
            $art = (string) $basProId[1];
            $varubet = (string) '0000';
            $store = (string) $sOWProduct->accountname;

            $quantity = (string) $sOWProduct->productquantity;
            $qtnZero = Functions::leadingzero(7, strlen((string) $quantity));

            $dummyTwo = (string) '1000';

            $reservationId = (string) '0000000';

            $contentF .= "{$seqZero}{$sequence}{$dummyOne}" .
                "{$vgr}{$art}{$varubet}{$store}" .
                "{$week}{$qtnZero}{$quantity}" .
                "{$dummyTwo}{$campaignWeek}{$reservationId}\n";
            $sequence++;
        }

        $seqZero = Functions::leadingzero(5, strlen((string) $sequence));
        $cntZero = Functions::leadingzero(
            5, strlen((string) $soProducts->num_rows)
        );
        
        $header = "{$seqZero}{$sequence}9999" .
            "{$cntZero}{$soProducts->num_rows}{$dt}1727130700518" .
            "000000000000000000000000000000000000000000000";
        $contentF .= $header;
        
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

        $responseSThree = $this->_sThree->create_object(
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
         * Store file name and file content to message queue.
         */
        syslog(
            LOG_INFO, "Store file name and file content to message queue."
        );

        $responseQ = $this->_sqs->send_message(
            $qUrl, $messageQ
        );

        /*
         * If unable to store file content at queue,
         * raise the exception
         */
        if ($responseQ->status !== 200) {
            syslog(
                LOG_WARNING, "Error in sending file to message queue."
            );
            throw new Exception("Error in sending file to message queue.");
        }

        return $responseQ;
    }

    public function init()
    {
        /*
         * Process SET Files
         */            
        try {
            $salesOrders = $this->getSalesOrdersForSet();
            $numberSalesOrders = $salesOrders->num_rows;

            /*
             * Update message array with number of sales orders.
             */
            $this->_messages['set']['count'] = $numberSalesOrders;
            $msg = &$this->_messages['set']['salesorders'];

            while ($salesOrder = $salesOrders->fetch_object()) {
                try {
                    /*
                     * Disable auto commit.
                     */
                    syslog(
                        LOG_INFO, "Disabling auto commit"
                    );
                    $this->_integrationConnect->autocommit(FALSE);

                    $msg[$salesOrder->salesorder_no]['status'] = false;

                    $setFile = $this->createSETFile($salesOrder, $msg);

                    $this->storeFileInSThree(
                        Config::$amazonSThree['setBucket'], 
                        Config::$amazonSThree['setFolder'], 
                        $setFile['file'], 
                        $setFile['content']
                    );

                    $this->storeFileInMessageQ(
                        Config::$amazonQ['url'], json_encode($setFile)
                    );

                    $msg[$salesOrder->salesorder_no]['status'] = true;
                    $fileName = $setFile['file'];

                    $this->updateIntegrationSalesOrder(
                        $salesOrder->id, 'set_status', 'Delivered'
                    );
                    /*
                     * Commit the databases.
                     */
                    $this->_integrationConnect->commit();
                } catch (Exception $e) {
                    $numberSalesOrders--;
                    /*
                     * Rollback the connections
                     */
                    $this->_integrationConnect->rollback();
                }
            }

            $this->_messages['set']['message'] = "$numberSalesOrders number " .
                "of sales orders processed for SET files.";
            
        } catch (Exception $e) {
            $this->_messages['message'] = $e->getMessage();
            /*
             * Rollback the connections
             */
            $this->_integrationConnect->rollback();
        }
        
        /*
         * Process MOS files
         */
        try {
            $accounts = $this->getAccountsForMos();
            $numberAccounts = $accounts->num_rows;

            /*
             * Update message array with number of sales orders.
             */
            $this->_messages['mos']['count'] = $numberAccounts;
            $msg = &$this->_messages['mos']['accounts'];

            while ($account = $accounts->fetch_object()) {
                try {
                    /*
                     * Disable auto commit.
                     */
                    syslog(
                        LOG_INFO, "Disabling auto commit"
                    );
                    $this->_integrationConnect->autocommit(FALSE);

                    $msg[$account->accountname]['status'] = false;

                    $mosFile = $this->createMOSFile($account, $msg);

                    $this->storeFileInSThree(
                        Config::$amazonSThree['mosBucket'], 
                        Config::$amazonSThree['mosFolder'], 
                        $mosFile['file'], 
                        $mosFile['content']
                    );

                    $this->storeFileInMessageQ(
                        Config::$amazonQ['url'], json_encode($mosFile)
                    );

                    $msg[$account->accountname]['status'] = true;
                    $fileName = $mosFile['file'];
                    
                    $this->updateIntegrationSalesOrderByAccountName(
                        $account->accountname, 'mos_status', 'Delivered'
                    );
                    /*
                     * Commit the databases.
                     */
                    $this->_integrationConnect->commit();
                } catch (Exception $e) {
                    $numberAccounts--;
                    /*
                     * Rollback the connections
                     */
                    $this->_integrationConnect->rollback();
                }
            }

            $this->_messages['mos']['message'] = "$numberAccounts number " .
                "of accounts processed for MOS files.";
        } catch (Exception $e) {
            $this->_messages['message'] = $e->getMessage();
            /*
             * Rollback the connections
             */
            $this->_integrationConnect->rollback();
        }

        syslog(
            LOG_INFO, json_encode($this->_messages)
        );
        echo json_encode($this->_messages);
    }

}

class Functions
{

    /**
     * auto adding zero before number  
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
     * Get 4 last digit from micro-time
     */

    static function getMilliSecond()
    {
        $seconds = round(microtime(true) * 1000);
        $remainder = substr("$seconds", -4);

        return $remainder;
    }

}

try {
    $phpBatchTwo = new PhpBatchTwo();
    $phpBatchTwo->init();
} catch (Exception $e) {
    syslog(LOG_WARNING, $e->getMessage());
    echo $e->getMessage();
}
