<?php

/* Assets class
 * 
 */

class Assets extends CFormModel {
    
    // Custom fields details
    static $customFields = array('trailertype' => 'cf_660');
    
    /* Funcation Name:- findAll
     * Description:- Get all Assets form Vtiger using rest api.
     * Return Type: Json
     */
    
    function findAll($module, $actionType = NULL, $filter = NULL) {
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
     * Description:- Get Asset details by id.
     * Return Type: Json
     */
    
    function findById($module, $id) {
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
        
        //foreach($this->credentials as $username => $password){            
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
    
    /* Funcation Name:- findAllAccounts
     * Description:- Get all Account details from vtiger.
     * Return Type: Json
     */

    function findAllAccounts($module) {
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

        $rest = new RESTClient();
        $rest->format('json');
        $rest->set_header('X_USERNAME', Yii::app()->session['username']);
        $rest->set_header('X_PASSWORD', Yii::app()->session['password']);
        $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
        $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
        $rest->set_header('X_SIGNATURE', $signature);
        $rest->set_header('X_GIZURCLOUD_API_KEY', Yii::app()->params->GIZURCLOUD_API_KEY);
        $response = $rest->get(Yii::app()->params->URL . $module);
        return $result = json_decode($response, true);
    }

    /* Funcation Name:- findAllProducts
     * Description:- Get all Products form Vtiger using rest api.
     * Return Type: Json
     */

    function findAllProducts($module) {
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

        $rest = new RESTClient();
        $rest->format('json');
        $rest->set_header('X_USERNAME', Yii::app()->session['username']);
        $rest->set_header('X_PASSWORD', Yii::app()->session['password']);
        $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
        $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
        $rest->set_header('X_SIGNATURE', $signature);
        $rest->set_header('X_GIZURCLOUD_API_KEY', Yii::app()->params->GIZURCLOUD_API_KEY);
        $response = $rest->get(Yii::app()->params->URL . $module);
        return $result = json_decode($response, true);
    }
    
    /* Funcation Name:- getPicklist
     * Description:- Get picklist value by field
     * Return Type: Json
     */
    function getPicklist($module, $fieldname) {
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

        $rest = new RESTClient();
        $rest->format('json');
        $rest->set_header('X_USERNAME', Yii::app()->session['username']);
        $rest->set_header('X_PASSWORD', Yii::app()->session['password']);
        $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
        $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
        $rest->set_header('X_SIGNATURE', $signature);
        $rest->set_header('X_GIZURCLOUD_API_KEY', Yii::app()->params->GIZURCLOUD_API_KEY);
        $response = $rest->get(Yii::app()->params->URL . $module . '/' . $fieldname);
        return $result = json_decode($response, true);
    }

    /* 
     * Funcation Name:- createAsset
     * Description:- with this function we can create new asset
     * Return Type: Json
     */
    function createAsset($module, $data) {
        $params = array(
            'Verb' => 'POST',
            'Model' => 'Assets',
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
            echo Yii::app()->user->setFlash('success', "Asset created successfully");
        } else {
            echo Yii::app()->user->setFlash('error', $response->error->message);
        }
        $protocol = Yii::app()->params['protocol'];
        $servername = Yii::app()->request->getServerName();
        $returnUrl = $protocol . $servername . Yii::app()->homeUrl."?r=assets/list";
        Yii::app()->getController()->redirect($returnUrl);
    }
    
     /* 
     * Funcation Name:- deleteAsset
     * Description:- with this function we delete asset by id
     * Return Type: Json
     */
     function deleteAsset($module, $id) {
        $params = array(
            'Verb' => 'DELETE',
            'Model' => 'Assets',
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
            echo json_encode(array('msg'=>'Asset deleted successfully'));
        } else {
            echo json_encode(array('msg'=>$response->error->message));
        }
     }

     /* 
     * Funcation Name:- updateAsset
     * Description:- with this function we update asset by id
     * Return Type: Json
     */
function updateAsset($module, $id, $data) {
        $params = array(
            'Verb' => 'PUT',
            'Model' => 'Assets',
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
        $response = json_decode($response);
         if ($response->success == true) {
            echo Yii::app()->user->setFlash('success', "Asset updated successfully");
        } else {
            echo Yii::app()->user->setFlash('error', $response->error->message);
        }
        $protocol = Yii::app()->params['protocol'];
        $servername = Yii::app()->request->getServerName();
        $returnUrl = $protocol . $servername . Yii::app()->homeUrl."?r=assets/edit&id=".$id;
        Yii::app()->getController()->redirect($returnUrl);
     }


}

?>
