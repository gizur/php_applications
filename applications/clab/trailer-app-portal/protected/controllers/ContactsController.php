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

    public function actionList()
    {
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

        if (!empty($_POST) && isset($_POST['submit'])) {
            $model = new Contacts;
            $module = 'Contacts';
            $this->LoginCheck();
            unset($_POST['submit']);
            // call function createAsset to create new asset
            $model->createContact($module, $_POST);
        }
        $accounts = $asset->findAllAccounts('Accounts');

        $contact = new Contacts;
        $contacts = $contact->findAll('Contacts');

        /*
         * Salutation (Not Working)
         */
        //$salutations = $asset->getPicklist($module, 'salutationtype');
        
        $salutations = array('' => '--None--',
            'Mr.' => 'Mr.',
            'Ms.' => 'Ms.',
            'Mrs.' => 'Mrs.',
            'Dr.' => 'Dr.',
            'Prof.' => 'Prof.');

        $this->render('add', array(
            'accounts' => $accounts,
            'contacts' => $contacts,
            'salutations' => $salutations,
            'session' => Yii::app()->session)
        );
    }

}
