<?php

class ContactsController extends Controller
{
    /**
     * @return array action filters
     */

    /**
     * This Action are display all contacts from vtiger user account 
     */
    public function accessRules()
    {
        return array(
            array('allow', // allow all users to perform 'index' and 'view' actions
                'actions' => array('index', 'contactlist'),
                'users' => array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('create', 'update'),
                'users' => array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array('admin', 'delete'),
                'users' => array('admin'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    public function LoginCheck()
    {
        $protocol = Yii::app()->params['protocol'];
        $servername = Yii::app()->request->getServerName();
        $user = Yii::app()->session['username'];
        if (empty($user)) {
            $returnUrl = $protocol . $servername . Yii::app()->homeUrl;
            $this->redirect($returnUrl);
        }
    }
    
    public function actionList()
    {
        $this->render('list', array('session' => Yii::app()->session));
    }

    public function actionAdd()
    {
        $module = "Contacts";
        
        $asset = new Assets;
        $this->LoginCheck();
        
        $accounts = $asset->findAllAccounts('Accounts'); 
        $salutations = $asset->getPicklist($module, 'salutationtype');
        
        $this->render('add', array(
            'accounts' => $accounts,
            'salutations' => $salutations,
            'session' => Yii::app()->session)
        );
    }

}
