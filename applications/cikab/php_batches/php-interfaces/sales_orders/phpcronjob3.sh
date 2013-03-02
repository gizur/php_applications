#!/usr/bin/php
<?php
/*
 * Load the configuration files.
 */
require_once __DIR__ . '/../ftp_connection.php';
require_once __DIR__ . '/../config.sqs.inc.php';

/*
 * Open connection to the system logger
 */
openlog("phpcronjob3", LOG_PID | LOG_PERROR, LOG_LOCAL0);
/*
 * Message array to store logs
 */
$_messages = array();

/*
 * Try block to catch the exceptions
 */
try {
    /*
     * Get number of files stored in message queue.
     */
    $messageCount = $no_of_files = $sqs->get_queue_size($amazonqueueConfig['_url']);

    /*
     * If number of files are 0, throw the exception
     */
    if ($messageCount <= 0)
        throw new Exception("messageQ is empty.");

    /*
     * Iterate till $messageCount becomes 0.
     */
    while ($messageCount > 0) {

        /*
         * Inner try catch to catch the message specific exceptions.
         */
        try {
            /*
             * Get the single message from the messageQ.
             */
            $_response = $sqs->receive_message($amazonqueueConfig['_url']);

            /*
             * If response is 200, Throw exception.
             */
            if (!$_response->status == 200)
                throw new Exception("Message not recieved from the messageQ server.");

            /*
             * Get the message body.
             */
            $msgObj = $_response->body->ReceiveMessageResult->Message;

            /*
             * If message body is empty, raise the exception.
             */
            if (empty($msgObj))
                throw new Exception("Received an empty message from messageQ.");

            /*
             * File name and content were json encoded so decode it.
             */
            $_fileJson = json_decode($msgObj->Body);
            /*
             * Get the message receipt, It will be used in deleting the message
             * from messageQ.
             */
            $_receipt = $msgObj->ReceiptHandle;

            /*
             * If file content are empty raise the exception.
             */
            if(empty($_fileJson->content))
                throw new Exception("$_fileJson->file content is empty in messageQ.");
            
            $ServerFilePath = $dbconfigFtp['serverpath'];
            $ftp_path = $ServerFilePath . $_fileJson->file;
            
            /*
             * If file exists at FTP, raise the Exception.
             */
            if (file_exists($ftp_path))
                throw new Exception("$_fileJson->file file already exists at FTP server.");
            
            /*
             * Prepare file in temp dir locally.
             */
            $fp = fopen('php://temp', 'r+');
            fwrite($fp, $_fileJson->content);
            rewind($fp);
            /*
             * Upload file to FTP.
             */
            $uploaded = ftp_fput($ftpConnId, $ftp_path, $fp, FTP_ASCII);
            fclose($fp);
            /*
             * If file upload process fails, throw the exception.
             */
            if(!$uploaded)
                throw new Exception("Error copying file $_fileJson->file on FTP server.");

            /*
             * If file processed, delete message from messageQ.
             */
            $sqs->delete_message($amazonqueueConfig['_url'], $_receipt);
            
            $_messages['files'][$_fileJson->file] = true;
            /*
             * Catch the exceptions
             */
        } catch (Exception $e) {
            $_messages[] = $e->getMessage();
        }
        /*
         * Decrease $messageCount by 1
         */
        $messageCount--;
    }
    /*
     * Update the success message
     */
    $_messages['message'] = "Total $no_of_files files copied.";
} catch (Exception $e) {
    $_messages['message'] = $e->getMessage();
}
/*
 * Update system logs and print log messages.
 */
syslog(LOG_WARNING, json_encode($_messages));
echo json_encode($_messages);
?>