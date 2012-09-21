<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class LoginForm extends CFormModel
{
	public $username;
	public $password;
	public $rememberMe;
    public $langauge;
	private $_identity;

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
		return array(
			// username and password are required
			array('username, password,langauge', 'required'),
			// rememberMe needs to be a boolean
			array('rememberMe', 'boolean'),
			// password needs to be authenticated
			array('password', 'authenticate'),
		);
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
			'rememberMe'=>'Remember me next time',
		);
	}

	/**
	 * Authenticates the password.
	 * This is the 'authenticate' validator as declared in rules().
	 */
	public function authenticate($attribute,$params)
	{
		
		
		if(!$this->hasErrors())
		{
			$this->_identity=new UserIdentity($this->username,$this->password,$this->langauge);
			Yii::app()->session['Lang']=$this->langauge;
			if(!$this->_identity->authenticate())
				echo Yii::app()->user->setFlash('error', "Incorrect username or password");
		}
	}

	/**
	 * Logs in the user using the given username and password in the model.
	 * @return boolean whether login is successful
	 */
	public function login()
	{
		if($this->_identity===null)
		{
			$this->_identity=new UserIdentity($this->username,$this->password);
			$this->_identity->authenticate();
		}
		if($this->_identity->errorCode===UserIdentity::ERROR_NONE)
		{
			$duration=$this->rememberMe ? 3600*24*30 : 0; // 30 days
			Yii::app()->user->login($this->_identity,$duration);
			return true;
		}
		else
			return false;
	}
	
	public function resetpassword()
	{
		 $model = 'Authenticate';
        //echo " Getting Picklist" . PHP_EOL;        

        $params = array(
                    'Verb'          => 'PUT',
                    'Model'	        => $model,
                    'Version'       => Yii::app()->params->API_VERSION,
                    'Timestamp'     => date("c"),
                    'KeyID'         => Yii::app()->params->GIZURCLOUD_API_KEY,
                    'UniqueSalt'    => uniqid()
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
        //login using each credentials
           $response['result']=array();           
            $rest = new RESTClient();
            $rest->format('json'); 
            $rest->set_header('X_USERNAME', Yii::app()->session['username']);
            $rest->set_header('X_PASSWORD', Yii::app()->session['password']);
            $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
            $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
            $rest->set_header('X_SIGNATURE', $signature);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', Yii::app()->params->GIZURCLOUD_API_KEY);
            $response = $rest->get(Yii::app()->params->URL.$model."/reset");
            $response = json_decode($response,true);
            //check if response is valid
            
            //unset($rest);
        //} 
		
	}
	
	
	
}
