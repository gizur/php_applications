<?php
/* Assets class
 * 
 */
class Assets extends CFormModel { 
    
    
    
     /* Funcation Name:- findAll
      * Description:- Get all Assets form Vtiger using rest api.
      * Return Type: Json
      */
    function findAll($module, $assetNo=NULL, $assetName=NULL) {
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
        $FilterParameter = array();
        if (!empty($assetNo)) {
            $FilterParameter[] = $assetNo;
        }
        if (!empty($assetName)) {
            $FilterParameter[] = $assetName;
        }
                
        $extraparameter = implode('/', $FilterParameter);
        if (!empty($extraparameter)) {
            $extraparameter = "/" . $extraparameter;
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
        $response = $rest->get(Yii::app()->params->URL . $module  . $extraparameter);
        return $result = json_decode($response, true);
    }

    
}

?>
