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
		 $this->LoginCheck();
		 $records=$model->findAll($module,$tickettype);
		 $this->render('surveylist',array('model'=>$model,'result'=>$records));
		
	}
	/**
	 * This Action are display all Trouble Ticket base Record 
	 */
	public function actionsurveylist()
	{
	     $module="HelpDesk";
	     $tickettype="inoperation";
		 $model=new Troubleticket;
		 $this->LoginCheck();
		 $records=$model->findAll($module,$tickettype);
		 $this->render('surveylist',array('model'=>$model,'result'=>$records));
		
	}
	
	/**
	 * This Action are create new Trouble Ticket 
	 */
	public function actionsurvey()
	{
		 $model=new Troubleticket;
		 $this->LoginCheck();
		if(isset($_POST['submit']))
		{
		 $model->Save($_POST['Troubleticket']);
		 }
		$pickList_sealed=$model->getpickList('sealed');
		$pickList_category=$model->getpickList('ticketcategories');
		$pickList_damagetype=$model->getpickList('damagetype');
		$pickList_damagepostion=$model->getpickList('damageposition');
		$picklist_drivercauseddamage=$model->getpickList('drivercauseddamage');
		$Asset_List=$model->findAssets('Assets');
		$this->render('survey',array('model'=>$model,'Sealed'=>$pickList_sealed,'category'=>$pickList_category,'damagetype' => $pickList_damagetype ,'damagepos'=> $pickList_damagepostion,'drivercauseddamageList'=>$picklist_drivercauseddamage,'Assets'=>$Asset_List));
		
	} 

/* This Action are Filter Ajax base Record */	
	public function actionsurveysearch()
	{
		 $module="HelpDesk";
		 $tickettype=$_POST['tickettype'];  
         $year=$_POST['year'];
	     $month=$_POST['month'];
	     $trailer=$_POST['trailer'];
	     $model=new Troubleticket;
	     $this->LoginCheck();
		 $records=$model->findAll($module,$tickettype,$year,$month,$trailer);
		 $this->renderPartial('ajaxrequest', array('result'=>$records));
		 
	} 
	/**
	 * This Action are display releted Trouble Ticket details depand on trouble ticket ID 
	 */		
	public function actionsurveydetails()
	{
		 $model=new Troubleticket;
		 $this->LoginCheck();
		 $module="HelpDesk";
		 $urlquerystring=$_SERVER['QUERY_STRING'];
		 $paraArr=explode("/",$urlquerystring);
		 $ticketId=$paraArr['2'];
		 $storedata=$model->findById($module,$ticketId);
		 $this->render('surveydetails',array('result'=>$storedata));
		
	}
	
	/**
	 * This is the action to handle images.
	 */
	public function actionimages()
	{
		 $module="DocumentAttachments";
		 $urlquerystring=$_SERVER['QUERY_STRING'];
		 $paraArr=explode("/",$urlquerystring);
		 $ticketId=$paraArr['2'];
		 $model=new Troubleticket;
		 $imagedata=$model->getimage($module,$ticketId);
		 header("Content-Type: image/jpeg");
		 header("Content-Disposition: inline;filename=".$imagedata['result']['filename']);
		 echo base64_decode($imagedata['result'][filecontent]); die;
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
	
	/**
	 * This Action are check logged user. otherwise redirect to login poage  
	 */
	 
	public function LoginCheck()
	{
		$user=Yii::app()->session['username'];
		if(empty($user))
		{
		 $returnUrl=Yii::app()->homeUrl;
		 $this->redirect($returnUrl);
		 }
		
	 }
	
	
}

?>
