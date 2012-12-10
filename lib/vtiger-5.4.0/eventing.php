<?php
error_reporting(E_ALL);
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Event.php');
if (Vtiger_Event::hasSupport()) {
    Vtiger_Event::register(
        'HelpDesk', 'vtiger.entity.aftersave', 'CustomHelpDeskHandler', 
        'modules/HelpDesk/CustomHelpDeskHandler.php'
    );
}
if(chmod('eventing.php', '400')){
    echo "<br/><br/>Permission set to 400.";
}else
    echo "<br/><br/>Error in setting permissions.";
?>
