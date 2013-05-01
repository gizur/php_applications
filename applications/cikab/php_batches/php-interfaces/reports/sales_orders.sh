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

/*
 * Open connection to system logger
 */
openlog(
    "phpcronjob1", LOG_PID | LOG_PERROR, LOG_LOCAL0
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

    $salesOrdersQuery =  "SELECT ENT.createdtime, SO.salesorder_no, SO.subject, " .
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
        throw new Exception("No Sales Order Found!");
        syslog(
            LOG_INFO, 
            "No Sales Order Found!"
        );
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
    $SOData = "Sales Order ID;" .
        "Sales Order No;" .
        "Subject;" .
        "SO Status;" .
        "Contact Id;" .
        "Due Date;" .
        "SO Status;" .
        "Account Name;" .
        "Account Id;" .
        "Product Id;" .
        "Product Name;" .
        "Quantity\n";

    /*
     * Generate the CSV content
     */    
    while ($salesOrder = $salesOrders->fetch_object()) {

        $SOData = $SOData . "$salesOrder->salesorderid;" .
                "$salesOrder->salesorder_no;" .
                "$salesOrder->subject;" .
                "$salesOrder->sostatus;" .
                "$salesOrder->contactid;" .
                "$salesOrder->duedate;" .
                "$salesOrder->sostatus;" .
                "$salesOrder->accountname;" .
                "$salesOrder->accountid;" .
                "$salesOrder->productid;" .
                "$salesOrder->productname;" .
                "$salesOrder->quantity\n";

    }

    /*
     * Send the Email as attachment
     */
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
                "Content-Type: text/plain; charset=ISO-8859-15; name=\"sales.txt\"\n" .
                "Content-Disposition: attachment; filename=\"sales.txt\"\n" .
                "Content-Transfer-Encoding: base64\n\n" .
                base64_encode($SOData) .
                "--NextPart"
            )
        ), 
        array(
           "Source" => "noreply@gizur.com",
           "Destinations" => array(
               "jonas.colmsjo@gizur.com"
            ),
        )
    );

    /*
     * Hooray! All done now check if the mail was sent
     */
    if ($sesResponse->isOK()) {
        echo '{"status": "Mail Sent"}';
    } else {
        echo '{"status": "Mail Not Sent"}';
    }

} catch (Exception $e) {
    /*
     * Store the message and rollbach the connections.
     */
    $messages['message'] = $e->getMessage();
    $integrationConnect->rollback();
    $vTigerConnect->rollback();
}

/*
 * Close the connections
 */
$vTigerConnect->close();
$integrationConnect->close();

/*
 * Log the message
 */
syslog(LOG_WARNING, json_encode($messages));
echo json_encode($messages);
