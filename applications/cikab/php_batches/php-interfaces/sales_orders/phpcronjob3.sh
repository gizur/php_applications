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
 * Load the configuration files.
 */
require_once __DIR__ . '/../ftp_connection.php';
require_once __DIR__ . '/../config.sqs.inc.php';

/*
 * Open connection to the system logger
 */
openlog(
    "phpcronjob3", LOG_PID | LOG_PERROR, LOG_LOCAL0
);
/*
 * Message array to store logs
 */
$messages = array();

/*
 * Try block to catch the exceptions
 */
try {
    /*
     * Get number of files stored in message queue.
     */
    syslog(
        LOG_INFO,
        "Geting number of files stored in message queue"
    );
    $messageCount = $sqs->get_queue_size($amazonqueueConfig['_url']);
    $noOfFiles = $messageCount;

    /*
     * If number of files are 0, throw the exception
     */
    if ($messageCount <= 0) {
        throw new Exception("messageQ is empty.");
        syslog(
            LOG_INFO,
            "messageQ is empty."
        );
    }

    /*
     * Iterate till $messageCount becomes 0.
     */
    while ($messageCount > 0) {

        syslog(
            LOG_INFO,
            "Number of messages found in messageQ : $messageCount."
        );
        /*
         * Inner try catch to catch the message specific exceptions.
         */
        try {
            /*
             * Get the single message from the messageQ.
             */
            syslog(
                LOG_INFO,
                "Get the single message from the messageQ"
            );
            $responseQ = $sqs->receive_message($amazonqueueConfig['_url']);

            /*
             * If response is 200, Throw exception.
             */
            if ($responseQ->status !== 200) {
                throw new Exception(
                    "Message not recieved from the messageQ server."
                );
                syslog(
                    LOG_INFO,
                    "Message not recieved from the messageQ server"
                );
            }

            /*
             * Get the message body.
             */
            $msgObj = $responseQ->body->ReceiveMessageResult->Message;

            /*
             * If message body is empty, raise the exception.
             */
            if (empty($msgObj)) { 
                throw new Exception(
                    "Received an empty message from messageQ."
                );
                syslog(
                    LOG_INFO,
                    "Received an empty message from messageQ."
                );
            }

            syslog(
                LOG_INFO,
                "Message Received: " . $msgObj->Body
            );
            
            /*
             * File name and content were json encoded so decode it.
             */
            $fileJson = json_decode($msgObj->Body);
            /*
             * Get the message receipt, It will be 
             * used in deleting the message
             * from messageQ.
             */
            $receiptQ = $msgObj->ReceiptHandle;

            /*
             * If file content are empty raise the exception.
             */
            if (empty($fileJson->content)) {
                throw new Exception(
                    "$fileJson->file content is empty in messageQ."
                );
                syslog(
                    LOG_WARNING,
                    "$fileJson->file content is empty in messageQ."
                );
            }
            
            $ftpPath = $dbconfigFtp['serverpath'] . $fileJson->file;
            
            /*
             * If file exists at FTP, raise the Exception.
             */
            if (file_exists($ftpPath)) {
                throw new Exception(
                    "$fileJson->file file already exists at FTP server."
                );
                syslog(
                    LOG_WARNING,
                    "$fileJson->file file already exists at FTP server."
                );
            }
            
            /*
             * Prepare file in temp dir locally.
             */
            $fp = fopen('php://temp', 'r+');
            fwrite($fp, $fileJson->content);
            rewind($fp);
            /*
             * Upload file to FTP.
             */
            $uploaded = ftp_fput(
                $ftpConnId, $ftpPath, $fp, FTP_BINARY
            );
            
            fclose($fp);
            /*
             * If file upload process fails, throw the exception.
             */
            if (!$uploaded) {
                throw new Exception(
                    "Error copying file $fileJson->file on FTP server."
                );
                syslog(
                    LOG_WARNING,
                    "Error copying file $fileJson->file on FTP server."
                );
            }
            /*
             * If file processed, delete message from messageQ.
             */
            syslog(
                LOG_INFO,
                "Deleting message from messageQ : $fileJson->file."
            );
            $sqs->delete_message(
                $amazonqueueConfig['_url'], $receiptQ
            );
            
            $messages['files'][$fileJson->file] = true;
            /*
             * Catch the exceptions
             */
        } catch (Exception $e) {
            $messages[] = $e->getMessage();
        }
        /*
         * Decrease $messageCount by 1
         */
        $messageCount--;
    }
    /*
     * Update the success message
     */
    $messages['message'] = "Total $noOfFiles queue items processed.";
} catch (Exception $e) {
    $messages['message'] = $e->getMessage();
}
/*
 * Update system logs and print log messages.
 */
syslog(LOG_WARNING, json_encode($messages));
echo json_encode($messages);