#!/usr/bin/php
<?php
/**
 * @category   Cronjobs
 * @package    Reports
 * @subpackage SalesORders
 * @author     Jonas Colmsjö <jonas@gizur.com>
 * @version    SVN: $Id$
 * @link       href="http://gizur.com"
 * @license    Commercial license
 * @copyright  Copyright (c) 2013, Gizur AB, 
 * <a href="http://gizur.com">Gizur AB</a>, All rights reserved.
 *
 * Purpose: Mail report with Sales Ordres
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
require_once __DIR__ . '/../config.database.php';
require_once __DIR__ . '/../../../../../api/protected/vendors/aws-php-sdk/sdk.class.php';

/*
 * Open connection to system logger
 */
openlog(
    "sales_orders_report", LOG_PID | LOG_PERROR, LOG_LOCAL0
);

/*
 * Try to connect to vTiger database as per setting 
 * defined in config files.
 */
syslog(
    LOG_INFO, "Try to connect to vTiger database"
);

$vTigerConnect = new Connect(
    $dbconfigVtiger['db_server'],
    $dbconfigVtiger['db_username'],
    $dbconfigVtiger['db_password'],
    $dbconfigVtiger['db_name']
);

syslog(
    LOG_INFO, "Connected to vTiger database"
);


/*
 * Open try to catch exceptions
 */

try {
    /*
     * Try to fetch pending sales orders fron vTiger database 
     */

     $salesOrdersQuery =  "SELECT ENT.createdtime, SO.salesorder_no, SO.subject," .
        "SO.duedate, SO.salesorderid, ENT.modifiedtime, ".
        "SO.sostatus, ACCO.accountname, PRO.productname, IVP.quantity " .
        "FROM vtiger_salesorder SO " .
        "INNER JOIN vtiger_crmentity ENT on ENT.crmid = SO.salesorderid " .
        "INNER JOIN vtiger_account ACCO on ACCO.accountid = SO.accountid " .
        "INNER JOIN vtiger_inventoryproductrel IVP on IVP.id=SO.salesorderid " .
        "INNER JOIN vtiger_products PRO on PRO.productid=IVP.productid " .
        "WHERE SO.sostatus<>'Closed' " .
        "AND lower(SO.subject)<>'initial push' AND lower(SO.subject)<>'Intial Push' " .
        "ORDER BY ENT.createdtime, SO.salesorder_no";


    syslog(LOG_INFO, "Executing Query: " . $salesOrdersQuery);
    
    $salesOrders = $vTigerConnect->query($salesOrdersQuery);

    /*
     * Message array to store error / success messages
     * through out end.
     */
    $messages = array();

    /*
     * In case of unable to fetch sales orders
     * throw exception.
     */
    if (!$salesOrders){
        syslog(
            LOG_WARNING, 
            "Error executing sales order query : ($vTigerConnect->errno) - " .
                "$vTigerConnect->error"
        );
        throw new Exception(
            "Error executing sales order query : " . 
            "($vTigerConnect->errno) - $vTigerConnect->error"
        );
    }

    /*
     * If no pending sales orders found
     * throw exception. 
     */
    if ($salesOrders->num_rows == 0){
        syslog(
            LOG_INFO, 
            "No Sales Order Found!"
        );
        throw new Exception("No Sales Order Found!");        
    }

    /*
     * Update message array with number of sales orders.
     */
    $messages['no_sales_orders'] = $salesOrders->num_rows;

    /*
     * Iterate through sales orders
     */
    syslog(
        LOG_INFO, 
        "Iterate through sales orders"
    );

    /*
     * Header of the CSV file content
     */
    $SOData = "Created time;" .
        "Sales Order No;" .
        "Subject;" .
        "SO Status;" .
        "Account Name;" .
        "Product Name;" .
        "Quantity;" .
        "Check Sum\n";
    /*
     * Generate the CSV content
     */
   $flag=0;
    $arr = array();
    while ($salesOrder = $salesOrders->fetch_object()) {
    
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
        else {
          $ordernumber = $originalordernomber;
           }
          $dt = date('Y-m-d',strtotime($salesOrder->createdtime));
          $updatedTime = date('Y-m-d',strtotime($salesOrder->modifiedtime));
          
          $orderDate1 = strtotime(
            date("Y-m-d", strtotime($updatedTime)) . "+1 day"
        );
        $orderDate2 = date('Y-m-d', $orderDate1);
          $orderDate = substr($orderDate2,-2);
          $salesOrderId = substr($ordernumber,-2);
         /* if (!empty($salesOrder->duedate) && $salesOrder->duedate != '0000-00-00') {
            $deliveryday = date(
                    "Y-m-d", strtotime($salesOrder->duedate)
            );
            } else {
          $deliveryday = date('Y-m-d');
          } */
          $futuredeliveryDate = strtotime(
            date("Y-m-d", strtotime($orderDate2)) . "+2 day"
        );
        $futuredeliverydate = date('Y-m-d', $futuredeliveryDate);
          $deliveryDate = substr($futuredeliverydate,-2);
          $bnr = substr($salesOrder->productname,-2);  
          $chkSum = $orderDate+$salesOrderId+$deliveryDate+$salesOrderId+$bnr;
           $lastDate = strtotime(
            date("Y-m-d") . "-1 day"
        );
          $lastD = date('Y-m-d', $lastDate); 
          if($updatedTime==$lastD) {
          $arr[$ordernumber] = $chkSum;
        // $arr1[]="orderDate:$orderDate+orderNo:$salesOrderId+DeleveryDate:$deliveryDate+orderNo:$salesOrderId+bnr:$bnr";
          }  
        $SOData = $SOData . "$salesOrder->createdtime;" .
                "$salesOrder->salesorder_no;" .
                "$salesOrder->subject;" .
                "$salesOrder->sostatus;" .
                "$salesOrder->accountname;" .
                "$salesOrder->productname;" .
                "$salesOrder->quantity;" .
                "$chkSum\n";
    }
     $orderCount = array_count_values($arr);
     if(isset($orderCount)) {
    foreach($orderCount as $key=>$value) {
         if($value>1) { $flag++; }
      }
     }
      
     $filtered = array_filter($arr, function($values) use ($orderCount) { 
          return $orderCount[$values] > 1;
     });
      $keysString = implode(", ", array_keys($filtered));
    /*
     * Send the Email as attachment
     */
    syslog(
        LOG_INFO, 
        "Send the Email as attachment"
    );     
    $email = new AmazonSES();
    $sesResponse = $email->send_raw_email(
        array(
            'Data' => base64_encode(
                "Subject: Sales order Report\n".
                "MIME-Version: 1.0\n".
                "Content-type: Multipart/Mixed; boundary=\"NextPart\"\n\n".
                "--NextPart\n".
                "Content-Type: text/plain\n\n".
                "PFA\n" .
                "--NextPart\n" .
                "Content-Type: text/plain; charset=ISO-8859-15; name=\"sales_order_report_" . date('ymd') . ".txt\"\n" .
                "Content-Disposition: attachment; filename=\"sales_order_report_" . date('ymd') . ".txt\"\n" .
                "Content-Transfer-Encoding: base64\n\n" .
                base64_encode($SOData) .
                "--NextPart"
            )
        ), 
        array(
           "Source" => "noreply@gizur.com",
           "Destinations" => Config::$toEmailReports
        )
    );

    /*
     * Hooray! All done now check if the mail was sent
     */
    if ($sesResponse->isOK()) {
        $messages['status'] =  "Mail Sent";
    } else {
        $messages['status'] =  "Mail Not Sent";
    }
    
     /* Send mail alert if check sum is greater then 175 */
    if($flag>0) {
    syslog(
        LOG_INFO, 
        "Send Alert Mail"
    );
$sesResponseAlert = $email->send_email(
        "noreply@gizur.com",
        array(
           "ToAddresses" => Config::$toEmailReports
        ),
        array(
            'Subject.Data'=>"Alert! Duplicate sales orders found!",
            'Body.Text.Data'=>"Hi,". PHP_EOL .
            "Duplicate Order List..". PHP_EOL . PHP_EOL .
            $keysString .PHP_EOL .
                                PHP_EOL .
                                '--' .
                                PHP_EOL .
                                'Gizur Admin'               
        )       
    );
    if ($sesResponseAlert->isOK()) {
        $messages['statusAlert'] =  "Mail Sent";
    } else {
        $messages['statusAlert'] =  "Mail Not Sent";
    }
  }

} catch (Exception $e) {
    /*
     * Store the message and rollbach the connections.
     */
    $messages['message'] = $e->getMessage();
    $vTigerConnect->rollback();
}

/*
 * Close the connections
 */
$vTigerConnect->close();

/*
 * Log the message
 */
syslog(LOG_WARNING, json_encode($messages));
echo json_encode($messages);
