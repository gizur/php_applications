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
    public $language;
    private $_identity;
    public $oldpassword;
    public $newpassword;
    public $newpassword1;

    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        return array(
            // username and password are required
            array('username, password,language', 'required'),
            array('newpassword, newpassword1', 'length', 'min' => 4, 'max' => 40),
            array('newpassword1', 'compare', 'compareAttribute' => 'newpassword'),
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
            'rememberMe' => 'Remember me next time',
        );
    }

    /**
     * Authenticates the password.
     * This is the 'authenticate' validator as declared in rules().
     */
    public function authenticate($attribute, $params)
    {


        if (!$this->hasErrors()) {
            $this->_identity = new UserIdentity($this->username, $this->password, $this->language);
            Yii::app()->session['Lang'] = $this->language;
            if (!$this->_identity->authenticate())
                echo Yii::app()->user->setFlash('error', "Incorrect username or password");
        }
    }

    /**
     * Logs in the user using the given username and password in the model.
     * @return boolean whether login is successful
     */
    public function login()
    {
        if ($this->_identity === null) {
            $this->_identity = new UserIdentity($this->username, $this->password);
            $this->_identity->authenticate();
        }
        if ($this->_identity->errorCode === UserIdentity::ERROR_NONE) {
            $duration = $this->rememberMe ? 3600 * 24 * 30 : 0; // 30 days
            Yii::app()->user->login($this->_identity, $duration);
            return true;
        }
        else
            return false;
    }

    public function resetpassword($username)
    {
        $model = 'Authenticate';
        
        $params = array(
            'Verb' => 'PUT',
            'Model' => $model,
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
        // Login using each credentials
        $response['result'] = array();
        $rest = new RESTClient();
        $rest->format('json');
        $rest->set_header('X_USERNAME', $username);
        $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
        $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
        $rest->set_header('X_SIGNATURE', $signature);
        $rest->set_header('X_GIZURCLOUD_API_KEY', Yii::app()->params->GIZURCLOUD_API_KEY);
        $response = $rest->put(Yii::app()->params->URL . $model . "/reset");
        $response = json_decode($response, true);
        // Check if response is valid
        if ($response['success'] == true) {
            echo Yii::app()->user->setFlash('success', 'Your password has been successfully reset.');
        } else {
            echo Yii::app()->user->setFlash('error', $response['error']['message']);
        }
    }

    function changepassword($oldpassword, $newpassword, $newpassword1)
    {
        if (!empty($oldpassword) &&
            !empty($newpassword) &&
            !empty($newpassword1)) {
            if ($newpassword == $newpassword1) {
                $model = 'Authenticate';
                // echo " Getting Picklist" . PHP_EOL;        
                $params = array(
                    'Verb' => 'PUT',
                    'Model' => $model,
                    'Version' => Yii::app()->params->API_VERSION,
                    'Timestamp' => date("c"),
                    'KeyID' => Yii::app()->params->GIZURCLOUD_API_KEY,
                    'UniqueSalt' => uniqid()
                );
                // Sorg arguments
                ksort($params);
                $data = array('newpassword' => $newpassword);
                // Generate string for sign
                $string_to_sign = "";
                foreach ($params as $k => $v)
                    $string_to_sign .= "{$k}{$v}";

                // Generate signature
                $signature = base64_encode(hash_hmac('SHA256', $string_to_sign, Yii::app()->params->GIZURCLOUD_SECRET_KEY, 1));
                //login using each credentials
                $response['result'] = array();
                $rest = new RESTClient();
                $rest->format('json');
                $rest->set_header('X_USERNAME', Yii::app()->session['username']);
                $rest->set_header('X_PASSWORD', $oldpassword);
                $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
                $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
                $rest->set_header('X_SIGNATURE', $signature);
                $rest->set_header('X_GIZURCLOUD_API_KEY', Yii::app()->params->GIZURCLOUD_API_KEY);
                $response = $rest->put(Yii::app()->params->URL . $model . "/changepw", $data);
                $response = json_decode($response, true);
                //check if response is valid
                if ($response['success'] == true) {
                    Yii::app()->session['password'] = $newpassword;
                    echo Yii::app()->user->setFlash('success', "Your password has been changed successfully.");
                } else {
                    echo Yii::app()->user->setFlash('error', $response['error']['message']);
                }
            } else {
                echo Yii::app()->user->setFlash('error', "Both new passwords do not match.");
            }
        } else {
            echo Yii::app()->user->setFlash('error', "All fields are mandatory.");
        }
    }

}
