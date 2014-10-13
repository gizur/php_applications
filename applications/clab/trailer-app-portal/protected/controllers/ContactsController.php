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
        // getting all users data
        $users = $contacts->findAllUsers('Users');
        foreach($users['result'] as $usersData) {
            $resultUsers[$usersData['id']] = $usersData['first_name'] . ' ' . $usersData['last_name'];
        }       
       // Get all account list
        $accounts = $asset->findAllAccounts('Accounts');
        foreach($accounts['result'] as $accounsData) {
            $resultAccounts[$accounsData['id']]=$accounsData['accountname'];
        }
        $result = $contacts->findAll($module, $actionType = NULL, $filter = NULL);
        $this->render('list', array(
            'result' => $result,
            'accounts' => $accounts,
            'resultUsers' => $resultUsers,
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
    
    /*
     * Funcation Name:- actionsearchcontacts
     * Description:- with this function we are getting filtered list of contacts list by given parameter
     * Return Type: Json
     */

    function actionsearchcontacts()
    {
        $this->layout = false;
        $module = "Contacts";
        $asset = new Assets;
        $contacts = new Contacts();
        $this->LoginCheck();
        // getting all users data
        $users = $contacts->findAllUsers('Users');
        foreach($users['result'] as $usersData) {
            $resultUsers[$usersData['id']] = $usersData['first_name'] . ' ' . $usersData['last_name'];
        }       
       // Get all account list
        $accounts = $asset->findAllAccounts('Accounts');
        foreach($accounts['result'] as $accounsData) {
            $resultAccounts[$accounsData['id']]=$accounsData['accountname'];
        }
       
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $email = $_POST['email'];
        $account = $_POST['account'];
        $searchString = " contact_no like '%%'";
        if (!empty($firstname)) {
            $searchString .= " and firstname like '%$firstname%'";
        }
        if (!empty($lastname)) {
            $searchString .= " and lastname like '%$lastname%'";
        }
        if (!empty($email)) {
            $searchString .= " and email like '%$email%'";
        }
        if (!empty($account)) {
            $searchString .= " and account_id like '%$account%'";
        }
        $filter = $searchString;
        $actionType = 'search';
         $result = $contacts->findAll($module, $actionType, $filter);
        $this->render('searchcontacts', array(
            'result' => $result,
            'accounts' => $accounts,
            'resultUsers' => $resultUsers,
            'resultAccounts'=>$resultAccounts,
            'session' => Yii::app()->session)
        );
        }
        
     /*
     * Funcation Name:- actionDelete
     * Description:- with this function we are deleting contacts by id
     * Return Type: Json
     */
    public function actionDelete()
    {
        $model = new Contacts;
        $module = 'Contacts';
        $this->LoginCheck();
        $id = $_POST['id'];
        // call function deleteContacts to delete selected contacts
        $model->deleteContacts($module, $id);
    }
    
    /*
     * Funcation Name:- actionDelete
     * Description:- with this function we are deleting contacts by id
     * Return Type: Json
     */
    public function actionResetpassword()
    {
        $model = new Contacts;
        $module = 'Contacts';
        $this->LoginCheck();
        $username = $_POST['email'];
        // call function deleteContacts to delete selected contacts
        $model->resetPassword($username);
    }
    
    public function actionUP() {
$model = new Contacts;
          $id = $_POST['id'];
            $this->LoginCheck();
            // call function createAsset to create new asset
            $model->updateContacts($id, $_POST, 'up') ;
exit;

}

    
    /*
     * Funcation Name:- actionDelete
     * Description:- with this function we are deleting contacts by id
     * Return Type: Json
     */
    public function actionEdit()
    {
        $module = "Contacts";
        $asset = new Assets;
        $this->LoginCheck();
        if (!empty($_POST) && isset($_POST['submit'])) {
            $id = $_POST['id'];
            $model = new Contacts;
            $module = 'Contacts';
            $this->LoginCheck();
            unset($_POST['submit']);
            // call function createAsset to create new asset
            $model->updateContacts($id, $_POST);
        }
        $accounts = $asset->findAllAccounts('Accounts');
        $contact = new Contacts;
        $contacts = $contact->findAll('Contacts');
        $id = $_GET['id'];
        $result = $contact->findById($id); 
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

        $this->render('edit', array(
            'result' => $result['result'],
            'accounts' => $accounts,
            'contacts' => $contacts,
            'salutations' => $salutations,
            'session' => Yii::app()->session)
        );
    }
}
