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
        $this->render('list', array('session' => Yii::app()->session));
    }

    public function actionAdd()
    {
        $this->render('add', array('session' => Yii::app()->session));
    }

}
