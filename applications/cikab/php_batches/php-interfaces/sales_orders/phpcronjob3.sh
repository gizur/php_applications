#!/usr/bin/php
<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
include __DIR__ . '/../ftp_connection.php';
include __DIR__ . '/../config.sqs.inc.php';

openlog("phpcronjob3", LOG_PID | LOG_PERROR, LOG_LOCAL0);

//$dbconfig_batchvaliable['batch_valiable'];
$_messages = array();

try {
    $messageCount = $sqs->get_queue_size($amazonqueue_config['_url']);

    if ($messageCount <= 0)
        throw new Exception("messageQ is empty.");

    while ($messageCount > 0) {

        try {
            $_response = $sqs->receive_message($amazonqueue_config['_url']);

            if (!$_response->status == 200)
                throw new Exception("Message not recieved from the messageQ server.");

            $msgObj = $_response->body->ReceiveMessageResult->Message;

            if (empty($msgObj))
                throw new Exception("Received an empty message from messageQ.");

            $_fileJson = $msgObj->Body;
            $_receipt = $msgObj->ReceiptHandle;

            if(empty($_fileJson->content))
                throw new Exception("$_fileJson->file content is empty in messageQ.");
            
            $ServerFilePath = $dbconfig_ftpserverpath['serverpath'];
            $ftp_path = $ServerFilePath . $_fileJson->file;
            
            if (file_exists($ftp_path))
                throw new Exception("$_fileJson->file file already exists at FTP server.");
            
            $fp = fopen('php://temp', 'r+');
            fwrite($fp, $_fileJson->content);
            rewind($fp);
            $uploaded = ftp_fput($ftp_conn, $ftp_path, $fp, FTP_ASCII);
            
            if(!$uploaded)
                throw new Exception("Error copying file $_fileJson->file on FTP server.");

            $sqs->delete_message($amazonqueue_config['_url'], $_receipt);
            
            $_messages['files'][$_fileJson->file] = true;
        } catch (Exception $e) {
            $_messages['message'] = $e->getMessage();
        }
    }
    $_messages['message'] = "Total $messageCount files copied.";
} catch (Exception $e) {
    $_messages['message'] = $e->getMessage();
}

syslog(LOG_WARNING, json_encode($_messages));
echo json_encode($_messages);
?>