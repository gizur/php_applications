#!/usr/bin/php
<?php
/**
 * 
 * created date : 02/06/2012
 * created by : anil singh
 * @author anil singh <anil-singh@essindia.co.in>
 * flow : Connect to your FTP Server 
 * 		  
 * modify date : 02/06/2012
 */
/**
 * Call FTP Connection
 */
require_once __DIR__ . '/../ftp_connection.php';

/**
 * include SQS instance file 
 */
require_once __DIR__ . '/../config.sqs.inc.php';

/**
 * for use databse connection
 */
require_once __DIR__ . '/../config.database.php';
/**
 * set autocommit off
 */
/**
 * ready state of syslog
 */
openlog("phpcronjob3", LOG_PID | LOG_PERROR, LOG_LOCAL0);

$LocalFilePath = __DIR__ . "/" . $dbconfig_ftplocalpath['localpath'];
$ServerFilePath = $dbconfig_ftpserverpath['serverpath'];
$GetAllQues = "SELECT accountname FROM `" . $dbconfig_integration['db_name'] . "`.`saleorder_msg_que` 
    WHERE status=0 group by accountname limit 0," . $dbconfig_batchvaliable['batch_valiable'] . "";
$executequery = @mysql_query($GetAllQues, $obj1->link);
if (!$executequery) {
    $OKAll = false;
    $syslogmessage = "Some problem in Query1, the error is : " . mysql_error();
    syslog(LOG_WARNING, "" . $syslogmessage . "");
    mysql_close($obj1->link);
    exit;
} else {
    /**
     * Count the record if no record found then it will be go else conditions
     */
    /**
     * Define array for syslog message
     */
    $syslogmessage = array();

    $numrows = @mysql_num_rows($executequery);
    $syslogmessage[] = "Total no of accounts found : $numrows \n";
    /**
     * Check the count record
     */
    if ($numrows > 0) {
        
        $OKAll = true;
        
        while ($GETRows = mysql_fetch_array($executequery)) {

            $account_flag = true;
            $syslogmessage[] = "  Account : " . $GETRows['accountname'] . " \n";
            $GetAllQuesacno = "SELECT * FROM `" . $dbconfig_integration['db_name'] . "`.`saleorder_msg_que` 
                WHERE accountname='" . $GETRows['accountname'] . "' AND status=0";
            $executequery2 = @mysql_query($GetAllQuesacno, $obj1->link);

            if (!$executequery2) {
                $account_flag = $account_flag && false;
                $syslogmessage[] = "Some problem in Query2, the error is : " . mysql_error();
                syslog(LOG_WARNING, "" . $syslogmessage . "");
            } else {
                /**
                 * Count the record if no record found then it will be go else conditions
                 */
                $numrows2 = @mysql_num_rows($executequery2);
                $syslogmessage[] = "      Total files for account " . $GETRows['accountname'] . ": $numrows2 \n";
                /**
                 * Check the count record
                 */
                if ($numrows2 > 0) {
                    while ($GETRowsacno = mysql_fetch_array($executequery2)) {

                        @mysql_query("set autocommit = 0", $obj1->link);

                        /**
                         * set satrt trasaction on
                         */
                        @mysql_query("start transaction", $obj1->link);

                        /**
                         * Call Local file path and File Name When send on FTP
                         */
                        $local_file = $LocalFilePath . $GETRowsacno['ftpfilename'];
                        /**
                         * Check file on local server if not found then manage syslog
                         */
                        if (!file_exists($local_file)) {
                            $syslogmessage[] = $local_file . " doesnot exist on local server.!!";
                        } else {
                            //IF FILE DOES EXIST, FTP IT.

                            /**
                             * Call server file path and File Name When uploaded on FTP
                             */
                            $ftp_path = $ServerFilePath . $GETRowsacno['ftpfilename'];

                            /**
                             * Check file on local server if found then manage syslog
                             */
                            if (file_exists($ftp_path)) {
                                $syslogmessage[] = $ftp_path . " exist on FTP server.!";
                            } else {
                                //IF File does not exist on ftp, process it.
                                $updatesaleorde = "UPDATE `" . $dbconfig_integration['db_name'] . "`.`saleorder_msg_que` 
                                SET status = 1 WHERE id=" . $GETRowsacno['id'];
                                $updatetable = @mysql_query($updatesaleorde, $obj1->link);

                                if ($updatetable) {
                                    /**
                                     * Push the above files on FTP Server by put command. if 
                                     * the above condition will be true then file upload on 
                                     * ftp server other wise manage 
                                     * Syslog
                                     */
                                    $upload = ftp_put($conn_id, $ftp_path, $local_file, FTP_ASCII);
                                    /**
                                     * if the files not push on FTP the getting a Error message.
                                     */
                                    if (!$upload) {
                                        $syslogmessage[] = "Some permission issue in files OR Directories. "
                                            . "File does not upload on ftp server!!";

                                        //ROLL BACK THE ABOVE QUERY.
                                        mysql_query("rollback", $obj1->link);
                                        $account_flag = $account_flag && false;
                                    } else {
                                        $account_flag = $account_flag && true;
                                        mysql_query("commit", $obj1->link);
                                    }
                                } else {
                                    $syslogmessage[] = "Some problem in query updation and error is : " . mysql_error();
                                }
                            }
                        }
                    }
                    /*if ($account_flag) {
                        $rmqmessagerecid = $GETRows['accountname'];
                        $_response = $sqs->receive_message($amazonqueue_config['_url']);
                        if ($_response->status == 200) {
                            $msgObj = $_response->body->ReceiveMessageResult->Message;
                            if (!empty($msgObj)) {
                                $syslogmessage[] = " [x] Received " . $msgObj->Body . "\n";
                                $sqs->delete_message($amazonqueue_config['_url'], $msgObj->ReceiptHandle);
                            } else {
                                $syslogmessage[] = $rmqmessagerecid . "Message Not Recieved from the MessageQ Server.";
                            }
                        } else {
                            $syslogmessage[] = $rmqmessagerecid . "Message Not Recieved from the MessageQ Server.";
                        }
                    }*/
                }
            }
        }
    }
}

$access = date("y/m/d h:i:s");

/** write error message into the syslog		
    */
$findproblemsalesordermsg = @implode(" \n ", $syslogmessage);
echo $message = "sorry ! -" . $findproblemsalesordermsg . ". at " . $access . "  ";
syslog(LOG_WARNING, "" . $message . "");
?>
