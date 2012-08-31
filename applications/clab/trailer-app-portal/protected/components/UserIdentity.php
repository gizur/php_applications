<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
	/**
	 * Authenticates a user.
	 * The example implementation makes sure if the username and password
	 * are both 'demo'.
	 * In practical applications, this should be changed to authenticate
	 * against some persistent user identity storage (e.g. database).
	 * @return boolean whether authentication succeeds.
	 */
	  private $_id;
	  
	public function authenticate()
	{
		   $params = array(
                    'Verb'          => 'POST',
                    'Model'	        => 'Authenticate',
                    'Version'       => Yii::app()->params->API_VERSION,
                    'Timestamp'     => date("c"),
                    'KeyID'         => Yii::app()->params->GIZURCLOUD_API_KEY
        );

        // Sorg arguments
        ksort($params);
          // Generate string for sign
        $string_to_sign = "";
        foreach ($params as $k => $v)
            $string_to_sign .= "{$k}{$v}";
    
        // Generate signature
        $signature = base64_encode(hash_hmac('SHA256', 
        $string_to_sign, Yii::app()->params->GIZURCLOUD_SECRET_KEY, 1));
		    $rest = new RESTClient();
            $rest->format('json'); 
            $rest->set_header('X_USERNAME', $this->username);
            $rest->set_header('X_PASSWORD', $this->password);
            $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
            $rest->set_header('X_SIGNATURE', $signature);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', Yii::app()->params->GIZURCLOUD_API_KEY);
            $response = $rest->post(Yii::app()->params->URL .
                           "Authenticate/login", array(
                      
                   ));
		          $response=json_decode($response);
		         /*
		           * Check Response if the responce is true then set the 
		           * session other wise return error message.
		           */ 

		  if($response->success=='true'){
			Yii::app()->session['username'] = $this->username;
			Yii::app()->session['password'] = $this->password;
			Yii::app()->session['account'] = $response->contactname;
			Yii::app()->session['contactname'] = $response->accountname;
			$this->errorCode=self::ERROR_NONE;
			return true;
             } else {
				return false;
				 
			 }
		           
	}
	
	public function getId()
    {
        return $this->_id;
    }
}
