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
        $this->layout=false;
        $model = new Assets;
        $module = 'Assets';
        $this->LoginCheck();
        //$assetNo = addslashes($_POST['assetNo']);
        //$assetName = addslashes($_POST['assetName']);
        $assetNo = strtoupper($_POST['assetNo']);
        $assetName = strtoupper($_POST['assetName']);
        $searchString = array();
        if(!empty($assetNo)) {
            $searchString[]='asset_no0F0'.$assetNo;
        }
         if(!empty($assetName)) {
            $searchString[]='assetname0F0'.$assetName;
        }
        $filter = implode('0X0',$searchString);

        $actionType = 'search';
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
        $this->render('searchasset', array('model'=>$model,
                                    'result'=>$records,
                                    'resultAccounts'=>$resultAccounts,
                                    'resultProducts'=>$resultProducts,
                                    'session' => Yii::app()->session)
                    );
    }

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

}
