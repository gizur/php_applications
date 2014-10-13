<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

function vtws_logincustomer($username, $pwd)
{
    global $adb;

    $userId = vtws_verifyActiveCustomerPortalUser($username, $pwd);
    if ($userId != null) {
        $accountId = vtws_getCustomerPortalUserAccount($userId);
        $accessKeyAndUsername = vtws_getAccessKeyAndUsernameFromAccount($accountId);

        // Get the time_zone of the user owner
        $sql = "SELECT smownerid FROM `vtiger_crmentity` WHERE crmid=?";
        $result = $adb->pquery($sql, array($userId));
        if ($result != null && isset($result)) {
            if ($adb->num_rows($result) > 0) {
                $uId = $adb->query_result($result, 0);
                $sql = "SELECT time_zone FROM `vtiger_users` WHERE id=?";
                $result = $adb->pquery($sql, array($uId));
                if ($result != null && isset($result)) {
                    if ($adb->num_rows($result) > 0) {
                        $accessKeyAndUsername = $accessKeyAndUsername + array_intersect_key($adb->query_result_rowdata($result, 0), array_flip(array('time_zone')));
                        $accessKeyAndUsername['vtiger_user_id'] = $uId;
                    }
                }
            }
        }

        //Get Object Id for contacts
        $sql = "SELECT id FROM `vtiger_ws_entity` WHERE name=?";
        $result = $adb->pquery($sql, array('Contacts'));
        if ($result != null && isset($result)) {
            $objectTypeId = $adb->query_result($result, 0, 'id');
        }
        $accessKeyAndUsername['contactId'] = $objectTypeId . "x" . $userId;
        return $accessKeyAndUsername;
    } else {
        throw new WebServiceException(WebServiceErrorCode::$INVALIDUSERPWD, "Invalid username or password");
    }
}

function vtws_verifyActiveCustomerPortalUser($username, $password)
{
    global $adb;

   $sql = "select * from vtiger_portalinfo where user_name=? and user_password=? and isactive=1";

    $result = $adb->pquery($sql, array($username, $password));

    if ($result != null && isset($result)) {
        if ($adb->num_rows($result) > 0) {
            return $adb->query_result($result, 0);
        }
    }
    return null;
}

function vtws_getCustomerPortalUserAccount($userId)
{
    global $adb;

    $sql = "SELECT accountid FROM `vtiger_contactdetails` WHERE contactid=?";
    $result = $adb->pquery($sql, array($userId));
    if ($result != null && isset($result)) {
        if ($adb->num_rows($result) > 0) {
            return $adb->query_result($result, 0);
        }
    }
    return null;
}

function vtws_getAccessKeyAndUsernameFromAccount($accountId)
{
    global $adb;

    //Get the Object Type Id for Accounts Module
    $sql = "SELECT id FROM `vtiger_ws_entity` WHERE name=?";
    $result = $adb->pquery($sql, array('Accounts'));
    if ($result != null && isset($result)) {
        $objectTypeId = $adb->query_result($result, 0, 'id');
    }

    $sql = "SELECT smownerid FROM `vtiger_crmentity` WHERE crmid=?";
    $result = $adb->pquery($sql, array($accountId));
    if ($result != null && isset($result)) {
        if ($adb->num_rows($result) > 0) {
            $vtigerUserId = $adb->query_result($result, 0);
            $sql = "SELECT user_name, accesskey FROM `vtiger_users` WHERE id=?";
            $result = $adb->pquery($sql, array($vtigerUserId));
            if ($result != null && isset($result)) {
                if ($adb->num_rows($result) > 0) {
                    return array('accountId' => $objectTypeId . "x" . $accountId) + array_intersect_key($adb->query_result_rowdata($result, 0), array_flip(array('accesskey', 'user_name')));
                }
            }
        }
    }
    return null;
}

function vtws_changepassword($username, $oldpassword, $newpassword)
{
    global $adb;

    $sql = "update vtiger_portalinfo set user_password = ? where user_name=? and user_password=?";
    $result = $adb->pquery($sql, array($newpassword, $username, $oldpassword));

    if ($result != null && isset($result)) {
        if ($adb->getAffectedRowCount($result) > 0) {
            return array('message' => 'Password Changed');
        }
    }
    throw new WebServiceException(WebServiceErrorCode::$INVALIDUSERPWD, "Unable to change password");
}

function vtws_resetpassword($username)
{
    global $adb;
    $newpassword = substr(strrev(uniqid()), 0, 8);
    $sql = "SELECT user_password FROM `vtiger_portalinfo` WHERE user_name=?";    
    $presult = $adb->pquery($sql, array($username));
    $oldPassword = '';
    if (!empty($presult))
        $oldPassword = $adb->query_result($presult, 0, 'user_password');
    
    $sql = "update vtiger_portalinfo set user_password = ? where user_name=?";
    $result = $adb->pquery($sql, array($newpassword, $username));

    if ($result != null && isset($result)) {
        if ($adb->getAffectedRowCount($result) > 0) {
            return array(
                'message' => 'Password has been reset',
                'newpassword' => $newpassword,
                'oldpassword' => $oldPassword
            );
        }
    }
    throw new WebServiceException(WebServiceErrorCode::$INVALIDUSERPWD, "Unable to reset password");
}

?>
