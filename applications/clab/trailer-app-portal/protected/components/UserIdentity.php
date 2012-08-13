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
		$rest = new RESTClient();
                   $rest->format('json');
                   $rest->set_header('X_USERNAME',$this->username);
		           $rest->set_header('X_PASSWORD',$this->password); 
                   $response = $rest->post(VT_REST_URL.
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
