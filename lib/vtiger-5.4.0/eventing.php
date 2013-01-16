<?php
//error_reporting(E_ALL);
$Vtiger_Utils_Log = true;

include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');
$module = new Vtiger_Module();
$module->name = 'CikabTroubleTicket';
$module->save();
/* Add the module to the Menu (entry point from UI) */
$menu = Vtiger_Menu::getInstance('Tools');
$menu->addModule($module);

/** Enable and Disable available tools */
$module->enableTools(Array('Import', 'Export'));
$module->disableTools('Merge');

$module->initWebservice();

include_once('vtlib/Vtiger/Event.php');
if (Vtiger_Event::hasSupport()) {
    Vtiger_Event::register(
        'HelpDesk', 'vtiger.entity.aftersave', 'CustomHelpDeskHandler', 
        'modules/CikabTroubleTicket/CustomHelpDeskHandler.php'
    );
}
if(chmod('eventing.php', '200')){
    echo "<br/><br/>Permission set to 200.";
}else
    echo "<br/><br/>Error in setting permissions.";
?>
