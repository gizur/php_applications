<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
global $currentModule;
global $adb;

if($_REQUEST['createportalmodule']=='CRT')
{
    /////////////////  For get Tabid into the vtiger tab table
       $select_query="select tabid from vtiger_tab where name=?";
       $select_params = array('CikabTroubleTicket');
       $tabid_res=$adb->pquery($select_query,$select_params);
       $tab_id = $adb->query_result($tabid_res,'tabid');
       
       /////////  Find the max sequence into the vtiger customertabs table
       
       $selectmax_query="select max(sequence) sequence from vtiger_customerportal_tabs";
       $sequence_res=$adb->pquery($selectmax_query,array());
       $maxsequence_id = $adb->query_result($sequence_res,'sequence');
       $maxsequence_id=$maxsequence_id+1;
       
       ////////////////////// insert tiger customer tabs table
       
       $update_query = "insert into vtiger_customerportal_tabs set tabid=?, visible=?,sequence=?";
	   $update_params = array($tab_id, '1', $maxsequence_id); 
	   $adb->pquery($update_query,$update_params);
	   
	   /////////////////////// insert module id into the vtiger_customerportal_tabs table to show all
	   
	   $updatepref_query = "insert into vtiger_customerportal_prefs set tabid=?, prefkey=?,prefvalue=?";
	   $updatepref_params = array($tab_id, 'showrelatedinfo', '1'); 
	   $adb->pquery($updatepref_query,$updatepref_params);
	
      $sourse="modules/CikabTroubleTicket/CikabTroubleTicket";
      $desti="portal/CikabTroubleTicket";
//rcopy($sourse,$desti);
echo "<B>You have successfully installed extension module!<B>";
	}   
 // removes files and non-empty directories
function rrmdir($dir) {
	if (is_dir($dir)) {
    $files = scandir($dir);
    foreach ($files as $file)
    if ($file != "." && $file != "..") rrmdir("$dir/$file");
    rmdir($dir);
  }
  else if (file_exists($dir)) unlink($dir);
}
// copies files and non-empty directories
function rcopy($src, $dst) {
  if (file_exists($dst))  
  // rrmdir($dst);
  if (is_dir($src)) {
	mkdir($dst);
    $files = scandir($src);
    foreach ($files as $file)
    if ($file != "." && $file != "..") rcopy("$src/$file", "$dst/$file");
  }
  else if (file_exists($src)) copy($src, $dst);
}
?>
<?php if($_REQUEST['createportalmodule']!='CRT'){ ?>
<div style="margin-top:100px; margin-left:150px">
<a href="index.php?<?php echo $_SERVER['QUERY_STRING']; ?>&createportalmodule=CRT">Create Customer Portal Module</a>	
</div>
<?php } ?>
