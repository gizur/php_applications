<?php

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

    public function actionList()
    {
        $module = "Assets";
        $model = new Assets;
        $this->LoginCheck();
        $records = $model->findAll($module, $assetNo='', $assetName=''); 
         echo "<pre>"; print_r($records);
        $this->render('list', array('model'=>$model, 
                                    'result'=>$records, 
                                    'session' => Yii::app()->session)
                    );
    }

    public function actionAdd()
    {
        $this->render('add', array('session' => Yii::app()->session));
    }

}
