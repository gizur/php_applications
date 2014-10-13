<?php

/* Contacts class
 * 
 */

class Contacts extends CFormModel
{
    /* Funcation Name:- findAll
     * Description:- Get all contacts form Vtiger using rest api.
     * Return Type: Json
     */
    function findAll($module, $actionType = NULL, $filter = NULL)
    {
        $params = array(
            'Verb' => 'GET',
            'Model' => $module,
            'Version' => Yii::app()->params->API_VERSION,
            'Timestamp' => date("c"),
            'KeyID' => Yii::app()->params->GIZURCLOUD_API_KEY,
            'UniqueSalt' => uniqid()
        );
        // Sorg arguments
        ksort($params);
        // Generate string for sign
        $string_to_sign = "";
        foreach ($params as $k => $v)
            $string_to_sign .= "{$k}{$v}";
        // Generate signature
        $signature = base64_encode(hash_hmac('SHA256', $string_to_sign, Yii::app()->params->GIZURCLOUD_SECRET_KEY, 1));
        //login using each credentials
        // Check Filter Parameter  
        if (!empty($actionType)) {
            if ((empty($filter) || !isset($filter))) {
                $filter = 'None';
            }
            $searchString = '/' . $actionType . '/s?searchString=' . base64_encode($filter);
        }
        //foreach($this->credentials as $username => $password){            
        $rest = new RESTClient();
        $rest->format('json');
        $rest->set_header('X_USERNAME', Yii::app()->session['username']);
        $rest->set_header('X_PASSWORD', Yii::app()->session['password']);
        $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
        $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
        $rest->set_header('X_SIGNATURE', $signature);
        $rest->set_header('X_GIZURCLOUD_API_KEY', Yii::app()->params->GIZURCLOUD_API_KEY);
        $response = $rest->get(Yii::app()->params->URL . $module . $searchString);
        return $result = json_decode($response, true);
    }
    
    /* Funcation Name:- findById
     * Description:- Get contact details by id.
     * Return Type: Json
     */
    function findById($id)
    {
        $module='Contacts';
        $params = array(
            'Verb' => 'GET',
            'Model' => $module,
            'Version' => Yii::app()->params->API_VERSION,
            'Timestamp' => date("c"),
            'KeyID' => Yii::app()->params->GIZURCLOUD_API_KEY,
            'UniqueSalt' => uniqid()
        );
        // Sorg arguments
        ksort($params);
        // Generate string for sign
        $string_to_sign = "";
        foreach ($params as $k => $v)
            $string_to_sign .= "{$k}{$v}";
        // Generate signature
        $signature = base64_encode(hash_hmac('SHA256', $string_to_sign, Yii::app()->params->GIZURCLOUD_SECRET_KEY, 1));
        //login using each credentials
        $rest = new RESTClient();
        $rest->format('json');
        $rest->set_header('X_USERNAME', Yii::app()->session['username']);
        $rest->set_header('X_PASSWORD', Yii::app()->session['password']);
        $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
        $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
        $rest->set_header('X_SIGNATURE', $signature);
        $rest->set_header('X_GIZURCLOUD_API_KEY', Yii::app()->params->GIZURCLOUD_API_KEY);
        $response = $rest->get(Yii::app()->params->URL . $module . '/' . $id);
        return $result = json_decode($response, true);
    }
    
    function findAllUsers($module) {
        $params = array(
            'Verb' => 'GET',
            'Model' => $module,
            'Version' => Yii::app()->params->API_VERSION,
            'Timestamp' => date("c"),
            'KeyID' => Yii::app()->params->GIZURCLOUD_API_KEY,
            'UniqueSalt' => uniqid()
        );
        // Sorg arguments
        ksort($params);
        // Generate string for sign
        $string_to_sign = "";
        foreach ($params as $k => $v)
            $string_to_sign .= "{$k}{$v}";
        // Generate signature
        $signature = base64_encode(hash_hmac('SHA256', $string_to_sign, Yii::app()->params->GIZURCLOUD_SECRET_KEY, 1));
        
        $rest = new RESTClient();
        $rest->format('json');
        $rest->set_header('X_USERNAME', Yii::app()->session['username']);
        $rest->set_header('X_PASSWORD', Yii::app()->session['password']);
        $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
        $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
        $rest->set_header('X_SIGNATURE', $signature);
        $rest->set_header('X_GIZURCLOUD_API_KEY', Yii::app()->params->GIZURCLOUD_API_KEY);
        $response = $rest->get(Yii::app()->params->URL . $module );
        return $result = json_decode($response, true);
    }

    /*
     * Funcation Name:- createContact
     * Description:- with this function we can create new asset
     * Return Type: Json
     */

    function createContact($module, $data)
    {
        $params = array(
            'Verb' => 'POST',
            'Model' => 'Contacts',
            'Version' => Yii::app()->params->API_VERSION,
            'Timestamp' => date("c"),
            'KeyID' => Yii::app()->params->GIZURCLOUD_API_KEY,
            'UniqueSalt' => uniqid()
        );
        // Sorg arguments
        ksort($params);
        // Generate string for sign
        $string_to_sign = "";
        foreach ($params as $k => $v)
            $string_to_sign .= "{$k}{$v}";

        // Generate signature
        $signature = base64_encode(hash_hmac('SHA256', $string_to_sign, Yii::app()->params->GIZURCLOUD_SECRET_KEY, 1));
        //login using each credentials
        $rest = new RESTClient();
        $rest->format('json');
        $rest->set_header('X_USERNAME', Yii::app()->session['username']);
        $rest->set_header('X_PASSWORD', Yii::app()->session['password']);
        $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
        $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
        $rest->set_header('X_SIGNATURE', $signature);
        $rest->set_header('X_GIZURCLOUD_API_KEY', Yii::app()->params->GIZURCLOUD_API_KEY);
        $response = $rest->post(Yii::app()->params->URL . $module, $data);
        $response = json_decode($response);
        if ($response->success == true) {
            echo Yii::app()->user->setFlash('success', "Contact created successfully");
            $protocol = Yii::app()->params['protocol'];
            $servername = Yii::app()->request->getServerName();
            $returnUrl = $protocol . $servername . Yii::app()->homeUrl . "?r=contacts/list";
            Yii::app()->getController()->redirect($returnUrl);
        } else {
            echo Yii::app()->user->setFlash('error', $response->error->message);
        }        
    }
    
    /* 
     * Funcation Name:- deleteContacts
     * Description:- with this function we delete asset by id
     * Return Type: Json
     */
     function deleteContacts($module, $id) {
        $params = array(
            'Verb' => 'DELETE',
            'Model' => $module,
            'Version' => Yii::app()->params->API_VERSION,
            'Timestamp' => date("c"),
            'KeyID' => Yii::app()->params->GIZURCLOUD_API_KEY,
            'UniqueSalt' => uniqid()
        );
      // Sorg arguments
        ksort($params);
        // Generate string for sign
        $string_to_sign = "";
        foreach ($params as $k => $v)
            $string_to_sign .= "{$k}{$v}";

        // Generate signature
        $signature = base64_encode(hash_hmac('SHA256', $string_to_sign, Yii::app()->params->GIZURCLOUD_SECRET_KEY, 1));
        //login using each credentials
        $rest = new RESTClient();
        $rest->format('json');
        $rest->set_header('X_USERNAME', Yii::app()->session['username']);
        $rest->set_header('X_PASSWORD', Yii::app()->session['password']);
        $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
        $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
        $rest->set_header('X_SIGNATURE', $signature);
        $rest->set_header('X_GIZURCLOUD_API_KEY', Yii::app()->params->GIZURCLOUD_API_KEY);
        $response = $rest->delete(Yii::app()->params->URL . $module . '/' .$id);
        $response = json_decode($response);
        if ($response->success == true) {
            echo json_encode(array('msg'=>'Contact deleted successfully'));
        } else {
            echo json_encode(array('msg'=>$response->error->message));
        }
     }
     
     function resetPassword($username) {
       $params = array(
            'Verb' => 'PUT',
            'Model' => 'Authenticate',
            'Version' => Yii::app()->params->API_VERSION,
            'Timestamp' => date("c"),
            'KeyID' => Yii::app()->params->GIZURCLOUD_API_KEY,
            'UniqueSalt' => uniqid()
        );
      // Sorg arguments
        ksort($params);
        $module = 'Authenticate';
        // Generate string for sign
        $string_to_sign = "";
        foreach ($params as $k => $v)
            $string_to_sign .= "{$k}{$v}";
        // Generate signature
        $signature = base64_encode(hash_hmac('SHA256', $string_to_sign, Yii::app()->params->GIZURCLOUD_SECRET_KEY, 1));
        //login using each credentials
        $rest = new RESTClient();
        $rest->format('json');
        $rest->set_header('X_USERNAME', $username);
        //$rest->set_header('X_PASSWORD', Yii::app()->session['password']);
        $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
        $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
        $rest->set_header('X_SIGNATURE', $signature);
        $rest->set_header('X_GIZURCLOUD_API_KEY', Yii::app()->params->GIZURCLOUD_API_KEY);
        $response = $rest->put(Yii::app()->params->URL . $module . '/reset');
        $response = json_decode($response);
        if ($response->success == true) {
            echo json_encode(array('msg'=>"Password reset successfully and new password has sent to user's email id." ));
        } else {
            echo json_encode(array('msg'=>$response->error->message));
        }  
     }
     
     /*
     * Funcation Name:- createContact
     * Description:- with this function we can create new asset
     * Return Type: Json
     */

    function updateContacts($id, $data, $type)
    {
        $module = 'Contacts';
        $params = array(
            'Verb' => 'PUT',
            'Model' => $module,
            'Version' => Yii::app()->params->API_VERSION,
            'Timestamp' => date("c"),
            'KeyID' => Yii::app()->params->GIZURCLOUD_API_KEY,
            'UniqueSalt' => uniqid()
        );
        // Sorg arguments
        ksort($params);
        // Generate string for sign
        $string_to_sign = "";
        foreach ($params as $k => $v)
            $string_to_sign .= "{$k}{$v}";

        // Generate signature
        $signature = base64_encode(hash_hmac('SHA256', $string_to_sign, Yii::app()->params->GIZURCLOUD_SECRET_KEY, 1));
        //login using each credentials
        $rest = new RESTClient();
        $rest->format('json');
        $rest->set_header('X_USERNAME', Yii::app()->session['username']);
        $rest->set_header('X_PASSWORD', Yii::app()->session['password']);
        $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
        $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
        $rest->set_header('X_SIGNATURE', $signature);
        $rest->set_header('X_GIZURCLOUD_API_KEY', Yii::app()->params->GIZURCLOUD_API_KEY);
        $response = $rest->put(Yii::app()->params->URL . $module . '/' . $id . '/update', $data);
        if($type=='up') {
  echo $response;
exit;
}  else {

        $response = json_decode($response);
        if ($response->success == true) {
            echo Yii::app()->user->setFlash('success', "Contact updated successfully");
        } else {
            echo Yii::app()->user->setFlash('error', $response->error->message);
        }
        $protocol = Yii::app()->params['protocol'];
        $servername = Yii::app()->request->getServerName();
        $returnUrl = $protocol . $servername . Yii::app()->homeUrl . "?r=contacts/edit&id=" . $id;
        Yii::app()->getController()->redirect($returnUrl);
    }


}
}
?>
