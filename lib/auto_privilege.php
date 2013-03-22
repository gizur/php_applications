<?php
require_once("vtiger-5.4.0/config.php");
require_once('vtiger-5.4.0/include/logging.php');
require_once('vtiger-5.4.0/include/nusoap/nusoap.php');
require_once('vtiger-5.4.0/modules/HelpDesk/HelpDesk.php');
require_once('vtiger-5.4.0/modules/Emails/mail.php');
require_once('vtiger-5.4.0/modules/HelpDesk/language/en_us.lang.php');
require_once('vtiger-5.4.0/include/utils/CommonUtils.php');
require_once('vtiger-5.4.0/include/utils/VtlibUtils.php');
require_once 'vtiger-5.4.0/modules/Users/Users.php';
require_once('vtiger-5.4.0/include/utils/UserInfoUtil.php');

if (isset($_GET['clientid']))
if (!file_exists('user_privileges/user_privileges_' . $_GET['clientid'] . 'php')){
    RecalculateSharingRules();
    $ourFileHandle = fopen('user_privileges/user_privileges_' . $_GET['clientid'] . '.php', 'w');
    fclose($ourFileHandle);        
}
?>
