<?php

class ContactsController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/main';

	/**
	 * @return array action filters
	 */

	

        /**
         * This Action are display all contacts from vtiger user account 
         */

public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index','contactlist'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('create','update'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','delete'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionList()
	{
       		$this->render('list',array(	
		));
	}

       public function actionIndex()
	{
            $a=1;
            $this->render('index',array('test'=>$a
                         ));	
       }

       public function actionAdd()
	{
            $a=1;
            $this->render('add',array('test'=>$a
                         ));	
       }
	
}
