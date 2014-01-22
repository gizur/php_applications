<?php

class SiteController extends Controller
{

    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return array(
            // captcha action renders the CAPTCHA image displayed on the contact page
            'captcha' => array(
                'class' => 'CCaptchaAction',
                'backColor' => 0xFFFFFF,
            ),
            // page action renders "static" pages stored under 'protected/views/site/pages'
            // They can be accessed via: index.php?r=site/page&view=FileName
            'page' => array(
                'class' => 'CViewAction',
            ),
        );
    }

    /**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested by users.
     */
    public function actionIndex()
    {
        $protocol = Yii::app()->params['protocol'];
        $servername = Yii::app()->request->getServerName();
        $model = new LoginForm;

        // if it is ajax validation request
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'login-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        if (!empty(Yii::app()->session['username'])) {
            $this->redirect($protocol . $servername . Yii::app()->homeUrl . '?r=troubleticket/surveylist');
            exit;
        }
        // collect user input data
        if (isset($_POST['LoginForm'])) {
            $model->attributes = $_POST['LoginForm'];
            $returnUrl = Yii::app()->homeUrl;
            // validate user input and redirect to the previous page if valid
            if ($model->validate() && $model->login())
                $returnUrl = Yii::app()->homeUrl . '?r=troubleticket/surveylist';
            $this->redirect($protocol . $servername . $returnUrl);
        }
        // display the login form
        $this->render('login', array('model' => $model));
    }

    /**
     * This is the action to handle external exceptions.
     */
    public function actionError()
    {
        if ($error = Yii::app()->errorHandler->error) {
            if (Yii::app()->request->isAjaxRequest)
                echo $error['message'];
            else
                $this->render('error', $error);
        }
    }

    /**
     * Displays the contact page
     */
    public function actionContact()
    {
        $model = new ContactForm;
        if (isset($_POST['ContactForm'])) {
            $model->attributes = $_POST['ContactForm'];
            if ($model->validate()) {
                $headers = "From: {$model->email}\r\nReply-To: {$model->email}";
                mail(Yii::app()->params['adminEmail'], $model->subject, $model->body, $headers);
                Yii::app()->user->setFlash('contact', 'Thank you for contacting us. We will respond to you as soon as possible.');
                $this->refresh();
            }
        }
        $this->render('contact', array('model' => $model));
    }

    /**
     * Displays the login page
     */
    public function actionLogin()
    {
        $protocol = Yii::app()->params['protocol'];
        $servername = Yii::app()->request->getServerName();

        $model = new LoginForm;

        // if it is ajax validation request
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'login-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        // collect user input data
        if (isset($_POST['LoginForm'])) {
            $model->attributes = $_POST['LoginForm'];
            // validate user input and redirect to the previous page if valid
            if ($model->validate() && $model->login())
                $this->redirect($protocol . $servername . Yii::app()->user->returnUrl);
        }
        // display the login form
        $this->render('login', array('model' => $model));
    }

    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout()
    {

        $protocol = Yii::app()->params['protocol'];
        $servername = Yii::app()->request->getServerName();
        $model = 'Authenticate';
        //echo " Getting Picklist" . PHP_EOL;        

        $params = array(
            'Verb' => 'POST',
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
        //login using each credentials

        $rest = new RESTClient();
        $rest->format('json');
        $rest->set_header('X_USERNAME', Yii::app()->session['username']);
        $rest->set_header('X_PASSWORD', Yii::app()->session['password']);
        $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
        $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
        $rest->set_header('X_SIGNATURE', $signature);
        $rest->set_header('X_GIZURCLOUD_API_KEY', Yii::app()->params->GIZURCLOUD_API_KEY);
        $response = $rest->post(Yii::app()->params->URL . $model . "/logout");
        $response = json_decode($response);
        /*
         * Check Response if the responce is true then set the 
         * session other wise return error message.
         */
        unset(Yii::app()->session['gizur_table_id_index']);
        Yii::app()->session['username'] = "";
        Yii::app()->session['password'] = "";
        Yii::app()->session['Lang'] = "";
        Yii::app()->session->destroy();
        if ($response->success) {
            $this->redirect($protocol . $servername . Yii::app()->user->returnUrl);
        } else {
            return false;
        }
    }

    function actionresetpassword()
    {
        $model = new LoginForm;
        if (isset($_POST['submit'])) {
            $model->resetpassword($_POST['LoginForm']['username']);
        }
        $this->render('resetpassword', array('model' => $model));
    }

    function actionchangepassword()
    {
        $model = new LoginForm;
        if (isset($_POST['submit'])) {
            $model->changepassword($_POST['LoginForm']['oldpassword'], $_POST['LoginForm']['newpassword'], $_POST['LoginForm']['newpassword1']);
        }
        $this->render('changepassword', array('model' => $model));
    }

}
