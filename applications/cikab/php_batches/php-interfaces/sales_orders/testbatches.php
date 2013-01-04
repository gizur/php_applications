<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);
$output = null;

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'setup-tables':
            shell_exec('sudo chmod +x setup-tables.sh');
            $output = shell_exec('./setup-tables.sh');
            shell_exec('sudo chmod -x setup-tables.sh');
            break;
        case 'phpcronjob1':
            shell_exec('sudo chmod +x phpcronjob1.sh');
            $output = shell_exec('./phpcronjob1.sh');
            shell_exec('sudo chmod -x phpcronjob1.sh');
            break;
        case 'phpcronjob2':
            shell_exec('sudo chmod +x phpcronjob2.sh');
            $output = shell_exec('./phpcronjob2.sh');
            shell_exec('sudo chmod -x phpcronjob2.sh');
            break;
        case 'phpcronjob3':
            shell_exec('sudo chmod +x phpcronjob3.sh');
            $output = shell_exec('./phpcronjob3.sh');
            shell_exec('sudo chmod -x phpcronjob3.sh');
            break;
        case 'phpinfo':
            phpinfo();
            break;
    }
    echo "<pre>$output</pre>";
}
include '../config.inc.php';
include '../../../../../lib/aws-php-sdk/sdk.class.php';

echo "<pre>";
$sqs = new AmazonSQS();
/*$_message = urlencode("I am here");
$_response = $sqs->send_message($amazonqueue_config['_url'], $_message);
if($_response->status == 200){
    echo "Successfull";
}*/
$_response1 = $sqs->receive_message($amazonqueue_config['_url']);
if($_response1->status == 200){
    print_r($_response1->body->ReceiveMessageResult->Message);
    echo $msgObj = $_response1->body->ReceiveMessageResult->Message->MessageId;
    $sqs->delete_message($amazonqueue_config['_url'], $_response1->body->ReceiveMessageResult->Message->ReceiptHandle);
}
?>
