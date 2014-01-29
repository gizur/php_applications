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

    public function actionList()
    {
        $module = "Assets";
        $model = new Assets;
        $this->LoginCheck();
        // Get all accounts list
        $accounts = $model->findAllAccounts('Accounts');
        foreach($accounts['result'] as $accounsData) {
            $resultAccounts[$accounsData['id']]=$accounsData['accountname'];
        }
        // Get products list
        $products = $model->findAllProducts('Products');
        foreach($products['result'] as $productsData) {
            $resultProducts[$productsData['id']]=$productsData['productname'];
        }
        // Get all assets list
        $records = $model->findAll($module, $actionType=NULL, $filter=NULL); 
        $this->render('list', array('model'=>$model, 
                                    'result'=>$records,
                                    'resultAccounts'=>$resultAccounts,
                                    'resultProducts'=>$resultProducts,
                                    'session' => Yii::app()->session)
                    );
    }
    
    function actionsearchasset()
    {
        $model = new Assets;
        $this->LoginCheck();
        //$assetNo = addslashes($_POST['assetNo']);
        //$assetName = addslashes($_POST['assetName']);
        $assetNo='AST1069';
        $assetName='CKK';
        $queryFilter = " asset_no like '%%' ";
                                if (!empty($assetNo)) {
                                  
                                   $queryFilter .= " and asset_no like '%$assetNo%'";
                                }
                                if (!empty($assetName)) {
                                  
                                  $queryFilter .= " and assetname like '%$assetName%'";
                                }
                                $filter =  urldecode($queryFilter);
                                $actionType='search';
        // Get all accounts list
        $accounts = $model->findAllAccounts('Accounts');
        foreach($accounts['result'] as $accounsData) {
            $resultAccounts[$accounsData['id']]=$accounsData['accountname'];
        }
        // Get products list
        $products = $model->findAllProducts('Products');
        foreach($products['result'] as $productsData) {
            $resultProducts[$productsData['id']]=$productsData['productname'];
        }
        // Get filtered assets data
        $records = $model->findAll($module, $actionType, $filter); 
        echo "<pre>";
        print_r($records);
        exit;
        $this->render('list', array('model'=>$model, 
                                    'result'=>$records,
                                    'resultAccounts'=>$resultAccounts,
                                    'resultProducts'=>$resultProducts,
                                    'session' => Yii::app()->session)
                    );   
    }

    public function actionAdd()
    {
        $this->render('add', array('session' => Yii::app()->session));
    }

}
