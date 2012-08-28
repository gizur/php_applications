<?php
/**
	 * 
	 * 
	 * Created date : 04/07/2012
	 * Created By : Anil Singh
	 * @author Anil Singh <anil-singh@essindia.co.in>
	 * Flow : The basic flow of this page is Create new trouble tickets.
	 * Modify date : 27/04/2012
	*/

class TroubleticketController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	
	public function actionindex()
	{
		 $module="HelpDesk";
	     $tickettype="Survey";
		 $model=new Troubleticket;
		 $records=$model->findAll($module,$tickettype);
		 $this->render('surveylist',array('model'=>$model,'result'=>$records));
		
	}
	
	public function actionsurveylist()
	{
	     $module="HelpDesk";
	     $tickettype="inoperation";
		 $model=new Troubleticket;
		 $records=$model->findAll($module,$tickettype);
		 $this->render('surveylist',array('model'=>$model,'result'=>$records));
		
	}
	
	public function actionsurvey()
	{
		 $model=new Troubleticket;
		if(isset($_POST['submit']))
		{
		 $model->Save($_POST['Troubleticket']);
		 }
		$pickList_sealed=$model->getpickList('sealed');
		$pickList_category=$model->getpickList('ticketcategories');
		$pickList_damagetype=$model->getpickList('damagetype');
		$pickList_damagepostion=$model->getpickList('damageposition');
		$picklist_drivercauseddamage=$model->getpickList('drivercauseddamage');
		$this->render('survey',array('model'=>$model,'Sealed'=>$pickList_sealed,'category'=>$pickList_category,'damagetype' => $pickList_damagetype ,'damagepos'=> $pickList_damagepostion,'drivercauseddamageList'=>$picklist_drivercauseddamage));
		
	} 
	
	public function actiondamage()
	{
		 $model=new Troubleticket;
		 if(isset($_POST['submit']))
		{
		 $model->Save($_POST['Troubleticket']);
		// echo "<pre>";
		 //print_r($_POST['Troubleticket']);
		 }
		$pickList_damagetype=$model->getpickList('cf_635');
		$pickList_damagepostion=$model->getpickList('cf_636');
		$pickList_category=$model->getpickList('ticketcategories');
	 	$this->render('damage',array('model' => $model,'damagetype' => $pickList_damagetype , 'damagepos' => $pickList_damagepostion,'category'=>$pickList_category));

	} 
		
		
		
	public function actionview()
	{
		 $model=new Troubleticket;
		 $customerid=Yii::app()->session['customerid'];
		 $sessionid=Yii::app()->session['sessionid'];
		 $module="HelpDesk";
		 $ticketId=112;
		 $onlymine="";
		
		 $params = Array('x_ticketid' => $ticketId,'x_block'=>$module,'x_id'=>"$customerid", 'x_sessionid'=>"$sessionid");
		$storedata=$model->view($params);
		 $this->render('view',array('result'=>$storedata));
		
	}
	
	
	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
	    if($error=Yii::app()->errorHandler->error)
	    {
	    	if(Yii::app()->request->isAjaxRequest)
	    		echo $error['message'];
	    	else
	        	$this->render('error', $error);
	    }
	}
	
	
}

?>
