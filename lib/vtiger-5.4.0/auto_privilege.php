<?php
require_once("config.php");
require_once('include/logging.php');
require_once('include/nusoap/nusoap.php');
require_once('include/utils/CommonUtils.php');
require_once('include/utils/VtlibUtils.php');
require_once('modules/Users/Users.php');
require_once('include/utils/UserInfoUtil.php');

if (isset($_GET['clientid']))
if (!file_exists('user_privileges/user_privileges_' . $_GET['clientid'] . '.php')){
    RecalculateSharingRules();
    $ourFileHandle = fopen('user_privileges/user_privileges_' . $_GET['clientid'] . '.php', 'w');
    fclose($ourFileHandle);        
}
?>
