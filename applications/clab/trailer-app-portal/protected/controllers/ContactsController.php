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
        $module = "Contacts";
        
        $contacts = new Contacts();
        $this->LoginCheck();
        $result = $contacts->findAll($module, $actionType=NULL, $filter=NULL); 
        echo "<pre>"; print_r($result); exit;  
        $this->render('list', array('session' => Yii::app()->session));
    }

    public function actionAdd()
    {
        $module = "Contacts";
        
        $asset = new Assets;
        $this->LoginCheck();
        
        $accounts = $asset->findAllAccounts('Accounts'); 
        $salutations = $asset->getPicklist($module, 'salutationtype');
        print_r($salutations);
        
        $this->render('add', array(
            'accounts' => $accounts,
            'salutations' => $salutations,
            'session' => Yii::app()->session)
        );
    }

}
