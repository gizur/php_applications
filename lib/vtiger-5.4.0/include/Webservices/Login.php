<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
	
	function vtws_login($username,$pwd){
		
		$user = new Users();
		$userId = $user->retrieve_user_id($username);
		
		$tokens = vtws_getActiveToken($userId);
		if($tokens == null){
			throw new WebServiceException(WebServiceErrorCode::$INVALIDTOKEN,"Specified token is invalid or expired");
		}
		
		$accessKey = vtws_getUserAccessKey($userId);
		if($accessKey == null){
			throw new WebServiceException(WebServiceErrorCode::$ACCESSKEYUNDEFINED,"Access key for the user is undefined");
		}
		
        $validTokenFound = false;
        foreach($tokens as $token) {
            $accessCrypt = md5($token.$accessKey);
            $validTokenFound = (strcmp($accessCrypt,$pwd)===0);
            if ($validTokenFound) {
                vtws_removeToken($userId, $token);
                break;
            }
        }

        if (!$validTokenFound) {
                throw new WebServiceException(WebServiceErrorCode::$INVALIDUSERPWD,"Invalid username or password");
        }

		$user = $user->retrieveCurrentUserInfoFromFile($userId);
		if($user->status != 'Inactive'){
			return $user;
		}
		throw new WebServiceException(WebServiceErrorCode::$AUTHREQUIRED,'Given user is inactive');
	}
	
	function vtws_removeToken($userId, $token){
		global $adb;
		
		$sql = "delete from vtiger_ws_userauthtoken where userid=? and  token=?";
		$adb->pquery($sql,array($userId,$token));
		return true;
	}

	function vtws_getActiveToken($userId){
		global $adb;
		
		$sql = "select * from vtiger_ws_userauthtoken where userid=? and expiretime >= ?";
		$result = $adb->pquery($sql,array($userId,time()));
		if($result != null && isset($result)){
            $tokens = array();
            $rowcount = $adb->num_rows($result);
			if($rowcount>0){
                for ($i=0;$i<$rowcount;$i++)
				    $tokens[] = $adb->query_result($result,$i,"token");
                return $tokens;
			}
		}
		return null;
	}
	
	function vtws_getUserAccessKey($userId){
		global $adb;
		
		$sql = "select * from vtiger_users where id=?";
		$result = $adb->pquery($sql,array($userId));
		if($result != null && isset($result)){
			if($adb->num_rows($result)>0){
				return $adb->query_result($result,0,"accesskey");
			}
		}
		return null;
	}
	
?>
