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

require_once __DIR__ . '/../config.inc.php';
require_once __DIR__ . '/../../../../../lib/aws-php-sdk/sdk.class.php';

class PhpBatchThree
{

    private $messages = array();
    private $messageCount = 0;
    private $noOfFiles = 0;
    private $setFtpConn;
    private $mosFtpConn;
    private $sqs;

    function __construct()
    {
        /*
         * Open connection to the system logger
         */
        openlog(
            "phpcronjob3", LOG_PID | LOG_PERROR, LOG_LOCAL0
        );

        syslog(
            LOG_INFO, "Trying connecting with Amazon SQS"
        );

        $this->sqs = new AmazonSQS();

        syslog(
            LOG_INFO, "Connected with Amazon SQS"
        );

        $this->setFtpConn = $this->getftpConnection(
            Config::$setFtp['host'], Config::$setFtp['port'], Config::$setFtp['username'], Config::$setFtp['password']
        );

        $this->mosFtpConn = $this->getftpConnection(
            Config::$mosFtp['host'], Config::$mosFtp['port'], Config::$mosFtp['username'], Config::$mosFtp['password']
        );
    }

    protected function getftpConnection($host, $port, $username, $password, $timeout = 10)
    {
        /**
         * Check FTP Connection
         */
        $ftpConn = ftp_connect(
            $host, $port, $timeout
        );

        /**
         * If connection fails update syslog with 
         * the error message and display
         * an error message.
         */
        if (!$ftpConn) {
            $syslogmessage = "Some problem in FTP Connection ($host:$port). " .
                "Please check Host Name!";
            syslog(
                LOG_WARNING, $syslogmessage
            );
            throw new Exception($syslogmessage);
        }

        /**
         *  Check Authentication after connection
         */
        $ftpLoginResult = ftp_login(
            $ftpConn, $username, $password
        );
        /**
         * If authentication fails update the syslog.
         */
        if (!$ftpLoginResult) {
            $syslogmessage = "Some problem in FTP Connection ($host:$port)" .
                ". Please check username and password!";
            syslog(
                LOG_WARNING, $syslogmessage
            );
            throw new Exception($syslogmessage);
        }

        /*
         * Enable passive mode
         */
        ftp_pasv($ftpConn, true);

        /**
         * Check FTP Connection and Authenticate the connection again
         */
        if (!$ftpConn || !$ftpLoginResult) {
            $syslogmessage = "Some problem in FTP Connection ($host:$port)!";
            syslog(
                LOG_WARNING, $syslogmessage
            );
            throw new Exception($syslogmessage);
        }

        return $ftpConn;
    }

    protected function saveToFtp($ftpConnId, $serverpath, $fileJson)
    {
        $ftpPath = $serverpath . $fileJson->file;

        /*
         * If file exists at FTP, raise the Exception.
         */
        if (ftp_size($ftpConnId, $ftpPath) != -1) {
            syslog(
                LOG_WARNING, "$fileJson->file file already exists at FTP server."
            );
            throw new Exception(
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
            syslog(
                LOG_WARNING, "Error copying file $fileJson->file on FTP server."
            );
            throw new Exception(
            "Error copying file $fileJson->file on FTP server."
            );
        }

        return $uploaded;
    }

    public function init()
    {
        $this->messageCount = $this->sqs->get_queue_size(Config::$amazonQ['url']);
        $this->noOfFiles = $this->messageCount;


        /*
         * If number of files are 0, throw the exception
         */
        if ($this->messageCount <= 0) {
            syslog(
                LOG_INFO, "messageQ is empty."
            );
            throw new Exception("messageQ is empty.");
        }
        /*
         * Iterate till $messageCount becomes 0.
         */
        syslog(
            LOG_INFO, 
            "Number of messages found in messageQ : $this->messageCount."
        );
            
        while ($this->messageCount > 0) {
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
                $responseQ = $this->sqs->receive_message(
                    Config::$amazonQ['url']
                );

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
                        LOG_INFO, "Received an empty message from messageQ."
                    );
                }

                syslog(
                    LOG_INFO, "Message Received: " . $msgObj->Body
                );

                /*
                 * File name and content were json encoded so decode it.
                 */
                $fileJson = json_decode($msgObj->Body);
                /*
                 * Get the message receipt
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
                        LOG_WARNING, "$fileJson->file content is empty in messageQ."
                    );
                }

                if ($fileJson->type == 'set' || !isset($fileJson->type)) {
                    $this->saveToFtp(
                        $this->setFtpConn, Config::$setFtp['serverpath'], $fileJson
                    );
                } else if ($fileJson->type == 'mos') {
                    $this->saveToFtp(
                        $this->mosFtpConn, Config::$mosFtp['serverpath'], $fileJson
                    );
                }
                /*
                 * Delete message from messageQ.
                 */
                syslog(
                    LOG_INFO, "Deleting message from messageQ : $fileJson->file."
                );
                $this->sqs->delete_message(
                    Config::$amazonQ['url'], $receiptQ
                );

                $this->messages['files'][$fileJson->file] = true;
            } catch (Exception $e) {
                $this->messages['files'][$fileJson->file] = false;
                $this->messages['message'] = $e->getMessage();
            }
            /*
             * Decrease $messageCount by 1
             */
            $this->messageCount--;
        }
    }
}


try{
    $phpBatchThree = new phpBatchThree();
    $phpBatchThree->init();
}catch(Exception $e){
    syslog(LOG_WARNING, $e->getMessage());
    echo $e->getMessage();
}