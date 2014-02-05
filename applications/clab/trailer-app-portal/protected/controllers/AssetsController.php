<?php

/**
 * Controller
 */
class AssetsController extends Controller
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
                'actions' => array('add', 'list'),
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

    /*
     * Funcation Name:- actionList
     * Description:- with this we are getting all asset list from vtiger
     * Return Type: Json
     */

    public function actionList()
    {
        $module = "Assets";
        $model = new Assets;
        $this->LoginCheck();
        // Get all accounts list
        $accounts = $model->findAllAccounts('Accounts');
        foreach ($accounts['result'] as $accounsData) {
            $resultAccounts[$accounsData['id']] = $accounsData['accountname'];
        }
        // Get products list
        $products = $model->findAllProducts('Products');
        foreach ($products['result'] as $productsData) {
            $resultProducts[$productsData['id']] = $productsData['productname'];
        }
        // Get all assets list
        $records = $model->findAll($module, $actionType = NULL, $filter = NULL);
        $this->render('list', array('model' => $model,
            'result' => $records,
            'resultAccounts' => $resultAccounts,
            'resultProducts' => $resultProducts,
            'session' => Yii::app()->session)
        );
    }

    /*
     * Funcation Name:- actionsearchasset
     * Description:- with this function we are getting filtered list of asset list by given parameter
     * Return Type: Json
     */

    function actionsearchasset()
    {
        $this->layout = false;
        $model = new Assets;
        $module = 'Assets';
        $this->LoginCheck();
        //$assetNo = addslashes($_POST['assetNo']);
        //$assetName = addslashes($_POST['assetName']);
        $assetNo = strtoupper($_POST['assetNo']);
        $assetName = strtoupper($_POST['assetName']);
        $searchString = " asset_no like '%%'";
        if (!empty($assetNo)) {
            $searchString .= " and asset_no like '%$assetNo%'";
        }
        if (!empty($assetName)) {
            $searchString .= " and assetname like '%$assetName%'";
        }
        $filter = $searchString;
        $actionType = 'search';
        // Get all accounts list
        $accounts = $model->findAllAccounts('Accounts');
        foreach ($accounts['result'] as $accounsData) {
            $resultAccounts[$accounsData['id']] = $accounsData['accountname'];
        }
        // Get products list
        $products = $model->findAllProducts('Products');
        foreach ($products['result'] as $productsData) {
            $resultProducts[$productsData['id']] = $productsData['productname'];
        }
        // Get filtered assets data
        $records = $model->findAll($module, $actionType, $filter);
        $this->render('searchasset', array('model' => $model,
            'result' => $records,
            'resultAccounts' => $resultAccounts,
            'resultProducts' => $resultProducts,
            'session' => Yii::app()->session)
        );
    }

    /*
     * Funcation Name:- actionAdd
     * Description:- with this function we are rendering asset add form with accounts and product picklist
     * Return Type: Json
     */

    public function actionAdd()
    {
        $model = new Assets;
        $module = 'Assets';
        $this->LoginCheck();
        // Get trailer type
        $trailerType = $model->getPicklist($module, 'trailertype');
        // Get asset status
        $assetstatus = $model->getPicklist($module, 'assetstatus');
        // Get accounts list
        $accounts = $model->findAllAccounts('Accounts');
        // Get products list
        $products = $model->findAllProducts('Products');
        $this->render('add', array(
            'accounts' => $accounts,
            'products' => $products,
            'assetstatus' => $assetstatus,
            'trailerType' => $trailerType,
            'session' => Yii::app()->session)
        );
    }

    /*
     * Funcation Name:- actionCreate
     * Description:- with this function we are creating new asset
     * Return Type: Json
     */

    public function actionCreate()
    {
        $model = new Assets;
        $module = 'Assets';
        $this->LoginCheck();
        unset($_POST['submit']);
        // call function createAsset to create new asset
        $model->createAsset($module, $_POST);
    }

    /*
     * Funcation Name:- actionDelete
     * Description:- with this function we are deleting asset by id
     * Return Type: Json
     */

    public function actionDelete()
    {
        $model = new Assets;
        $module = 'Assets';
        $this->LoginCheck();
        $id = $_POST['id'];
        // call function deleteAsset to delete selected asset
        $model->deleteAsset($module, $id);
    }

    /*
     * Funcation Name:- actionEdit
     * Description:- with this function we are rendering asset edit form with accounts and product picklist
     * Return Type: Json
     */

    public function actionEdit()
    {
        $id = $_GET['id'];
        if (empty($id)) {
            $protocol = Yii::app()->params['protocol'];
            $servername = Yii::app()->request->getServerName();
            $returnUrl = $protocol . $servername . Yii::app()->homeUrl . "?r=assets/list";
            Yii::app()->getController()->redirect($returnUrl);
        }
        $model = new Assets;
        $module = 'Assets';
        $this->LoginCheck();
        // Get trailer type
        $trailerType = $model->getPicklist($module, 'trailertype');
        // Get asset status
        $assetstatus = $model->getPicklist($module, 'assetstatus');
        // Get accounts list
        $accounts = $model->findAllAccounts('Accounts');
        // Get products list
        $products = $model->findAllProducts('Products');
        // Get asset by id
        $result = $model->findById($module, $id);
        $this->render('edit', array(
            'accounts' => $accounts,
            'products' => $products,
            'assetstatus' => $assetstatus,
            'trailerType' => $trailerType,
            'result' => $result['result'],
            'session' => Yii::app()->session)
        );
    }

    /*
     * Funcation Name:- actionUpdate
     * Description:- with this function we are updating asset by asset id
     * Return Type: Json
     */

    public function actionUpdate()
    {
        $model = new Assets;
        $module = 'Assets';
        $this->LoginCheck();
        unset($_POST['submit']);
        $id = $_POST['id'];
        if (empty($id)) {
            $protocol = Yii::app()->params['protocol'];
            $servername = Yii::app()->request->getServerName();
            $returnUrl = $protocol . $servername . Yii::app()->homeUrl . "?r=assets/list";
            Yii::app()->getController()->redirect($returnUrl);
        }
        // call function updateAsset to create new asset
        $model->updateAsset($module, $id, $_POST);
    }

}
