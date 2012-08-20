<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
	
	function vtws_relatetroubleticketdocument($crmid,$notesid){
		global $adb;
                
                $crmid = explode("x",$crmid);
                $crmid = $crmid[1];

                $notesid = explode("x",$notesid);
                $notesid = $notesid[1];                
                
                $sql="insert into vtiger_senotesrel(crmid,notesid) values (?,?)";
		$result = $adb->pquery($sql,array($crmid, $notesid));

		if($result === false){
                    return false;
		}else{
                    return true;
                }
	}
	
	function vtws_getrelatedtroubleticketdocument($crmid){
		global $adb;
                
                $crmid = explode("x",$crmid);
                $crmid = $crmid[1];               
                
                $sql="select * from vtiger_senotesrel where crmid = ?";
		$result = $adb->pquery($sql,array($crmid));

		if($result === false){
                    return false;
		}else{
                    $count = $adb->getRowCount($result);
                    $documents = Array();
                    for($i=1;$i<=$count;$i++) {
                        $documents[] = $adb->query_result($result,$i-1,'notesid');
                    }
                    return $documents;
                }
	}
        
        function vtws_gettroubleticketdocumentfile($notesid){
                global $adb;
                
                $id = explode("x",$notesid);
                $id = $id[1];
                $query="SELECT filetype FROM vtiger_notes WHERE notesid =?";
		$res = $adb->pquery($query, array($id));
		$filetype = $adb->query_result($res, 0, "filetype");
		updateDownloadCount($id);

		$fileidQuery = 'select attachmentsid from vtiger_seattachmentsrel where crmid = ?';
		$fileres = $adb->pquery($fileidQuery,array($id));
		$fileid = $adb->query_result($fileres,0,'attachmentsid');

		$filepathQuery = 'select path,name from vtiger_attachments where attachmentsid = ?';
		$fileres = $adb->pquery($filepathQuery,array($fileid));
		$filepath = $adb->query_result($fileres,0,'path');
		$filename = $adb->query_result($fileres,0,'name');
		$filename= decode_html($filename);

		$saved_filename =  $fileid."_".$filename;
		$filenamewithpath = $filepath.$saved_filename;
		$filesize = filesize($filenamewithpath);
                
                $output[0]['fileid'] = $fileid;
                $output[0]['filename'] = $filename;
                $output[0]['filetype'] = $filetype;
                $output[0]['filesize'] = $filesize;
                $output[0]['filecontents']=base64_encode(file_get_contents($filenamewithpath));

                return $output;                
        }

        /**
        * Function to update the download count of a file
        */
        function updateDownloadCount($id){
                global $adb,$log;
                $log->debug("Entering customer portal function updateDownloadCount");
                $updateDownloadCount = "UPDATE vtiger_notes SET filedownloadcount = filedownloadcount+1 WHERE notesid = ?";
                $countres = $adb->pquery($updateDownloadCount,array($id));
                $log->debug("Entering customer portal function updateDownloadCount");
                return true;
        }        
?>