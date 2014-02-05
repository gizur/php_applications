<?php

class ContactsController extends Controller {
    /**
     * @return array action filters
     */

    /**
     * This Action are display all contacts from vtiger user account 
     */
    public function accessRules() {
        
    }

    public function LoginCheck() {
        $protocol = Yii::app()->params['protocol'];
        $servername = Yii::app()->request->getServerName();
        $user = Yii::app()->session['username'];
        if (empty($user)) {
            $returnUrl = $protocol . $servername . Yii::app()->homeUrl;
            $this->redirect($returnUrl);
        }
    }

    public function actionList() {
        $module = "Contacts";
        $asset = new Assets;
        $contacts = new Contacts();
        $this->LoginCheck();
        $users = $contacts->findAllUsers('Users');
        echo "<pre>"; print_r($users); 
        // Get all account list
        $accounts = $asset->findAllAccounts('Accounts');
        foreach($accounts['result'] as $accounsData) {
            $resultAccounts[$accounsData['id']]=$accounsData['accountname'];
        }
        $result = $contacts->findAll($module, $actionType = NULL, $filter = NULL);
        $this->render('list', array(
            'result' => $result,
            'accounts' => $accounts,
            'resultAccounts'=>$resultAccounts,
            'session' => Yii::app()->session)
        );
    }

    public function actionAdd() {
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
