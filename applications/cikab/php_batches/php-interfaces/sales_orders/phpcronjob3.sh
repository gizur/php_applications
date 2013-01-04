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
//require_once __DIR__ . '/../ftp_connection.php';

/**
 * include SQS instance file 
 */
require_once __DIR__ . '/../config.sqs.inc.php';

/**
 * for use databse connection
 */
require_once __DIR__ . '/../config.database.php';
echo "One<br/>";
/**
 * set autocommit off
 */
@mysql_query("set autocommit = 0", $obj1->link);

/**
 * set satrt trasaction on
 */
@mysql_query("start transaction", $obj1->link);
echo "Two<br/>";
/**
 * ready state of syslog
 */
openlog("phpcronjob3", LOG_PID | LOG_PERROR, LOG_LOCAL0);

$LocalFilePath = __DIR__ . "/" . $dbconfig_ftplocalpath['localpath'];
$ServerFilePath = $dbconfig_ftpserverpath['serverpath'];
$GetAllQues = "SELECT accountname FROM `" . $dbconfig_integration['db_name'] . "`.`saleorder_msg_que` 
    WHERE status=0 group by accountname limit 0," . $dbconfig_batchvaliable['batch_valiable'] . "";
$executequery = @mysql_query($GetAllQues, $obj1->link);
echo "three<br/>";
if (!$executequery) {
    $OKAll = false;
    $syslogmessage = "Some problem in Query1, the error is : " . mysql_error();
    syslog(LOG_WARNING, "" . $syslogmessage . "");
    exit;
} else {
    /**
     * Count the record if no record found then it will be go else conditions
     */
    $numrows = @mysql_num_rows($executequery);

    /**
     * Check the count record
     */
    echo "four<br/>";
    if ($numrows > 0) {
        while ($GETRows = mysql_fetch_array($executequery)) {
            $GetAllQuesacno = "SELECT * FROM `" . $dbconfig_integration['db_name'] . "`.`saleorder_msg_que` 
                WHERE accountname='" . $GETRows['accountname'] . "'";
            $executequery2 = @mysql_query($GetAllQuesacno, $obj1->link);

            if (!$executequery2) {
                $OKAll = false;
                $syslogmessage = "Some problem in Query2, the error is : " . mysql_error();
                syslog(LOG_WARNING, "" . $syslogmessage . "");
                exit;
            }
            echo "five<br/>";
            /**
             * Count the record if no record found then it will be go else conditions
             */
            $numrows2 = @mysql_num_rows($executequery2);

            /**
             * Check the count record
             */
            /**
             * Define array for syslog message
             */
            $syslogmessage = array();
            $OKAll = true;
            if ($numrows2 > 0) {
                echo "six<br/>";
                while ($GETRowsacno = mysql_fetch_array($executequery2)) {
                    echo "seven<br/>";
                    /**
                     * Call Local file path and File Name When send on FTP
                     */
                    $local_file = $LocalFilePath . $GETRowsacno['ftpfilename'];
                    /**
                     * Check file on local server if not found then manage syslog
                     */
                    if (!file_exists($local_file)) {
                        $OKAll = false;
                        $syslogmessage[] = $local_file . " doesnot exist on local server.!!";
                    }

                    /**
                     * Call server file path and File Name When uploaded on FTP
                     */
                    $ftp_path = $ServerFilePath . $GETRowsacno['ftpfilename'];

                    /**
                     * Check file on local server if found then manage syslog
                     */
                    /*if (file_exists($ftp_path)) {
                        $OKAll = false;
                        $syslogmessage[] = $ftp_path . " exist on FTP server.!";
                    }*/


                    /**
                     * Push the above files on FTP Server by put command. if 
                     * the above condition will be true then file upload on 
                     * ftp server other wise manage 
                     * Syslog
                     */
                    $upload = "";
                    if ($OKAll) {
                        $upload = true;
                        //$upload = ftp_put($conn_id, $ftp_path, $local_file, FTP_ASCII);
                    }
                    /**
                     * if the files not push on FTP the getting a Error message.
                     */
                    if (!$upload) {
                        $OKAll = false;
                        $syslogmessage[] = "Some permission issue in files OR Directories. "
                            . "File does not upload on ftp server!!";
                    }
                    /**
                     * if the above condition will be true then file recieved files into the message que server other wise manage 
                     * Syslog
                     */
                    if ($OKAll) {
                        echo "eight<br/>";
                        $rmqmessagerecid = $GETRowsacno['accountname'];
                        $_response = $sqs->receive_message($amazonqueue_config['_url']);
                        if ($_response->status == 200) {
                            $msgObj = $_response->body->ReceiveMessageResult->Message;
                            echo " [x] Received ", $msgObj->Body, "\n";
                        } else {
                            $OKAll = false;
                            $syslogmessage[] = $rmqmessagerecid . "Message Not Recieved from the MessageQ Server.";
                        }
                        if ($OKAll) {
                            $updatesaleorde = "UPDATE `" . $dbconfig_integration['db_name'] . "`.`saleorder_msg_que` 
                                SET status = 1 WHERE id=" . $GETRowsacno['id'];
                            $updatetable = @mysql_query($updatesaleorde, $obj1->link);
                            if (!$updatetable) {
                                $OKAll = false;
                                $syslogmessage[] = "Some problem in query updation and error is : " . mysql_error();
                            }
                        }
                    }
                }
                if ($OKAll) {
                    $sqs->delete_message($amazonqueue_config['_url'], $msgObj->ReceiptHandle);
                    mysql_query("commit");
                } else {
                    mysql_query("rollback");
                    $access = date("y/m/d h:i:s");

                    /** write error message into the syslog		
                     */
                    $findproblemsalesordermsg = @implode(" \n ", $syslogmessage);
                    $message = "sorry ! -" . $findproblemsalesordermsg . ". at " . $access . "  ";
                    syslog(LOG_WARNING, "" . $message . "");
                }
            }
        }
    }

    $conn->close();
}
?>
