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
        $module = "HelpDesk";
        $tickettype = "Survey";
        $model = new Troubleticket;
        $this->LoginCheck();
        $records = $model->findAll($module, $tickettype);
        $this->render('surveylist', array('model' => $model, 'result' => $records));
    }

    /**
     * This Action are display all Trouble Ticket base Record 
     */
    public function actionsurveylist()
    {
        $module = "HelpDesk";
        $tickettype = "all";

        if (!isset(Yii::app()->session['gizur_table_id_index'])) {
            Yii::app()->session['gizur_table_id_index'] = 1;
            setcookie("SpryMedia_DataTables_table_id_index.php", "", time() - 3600);
            unset($_COOKIE['SpryMedia_DataTables_table_id_index.php']);
        }

        $model = new Troubleticket;
        $this->LoginCheck();
        $Asset_List = $model->findAssets('Assets');
        $Asset_List = array("0" => "--All Trailers--") + $Asset_List;

        $currentyear = isset(Yii::app()->session['Search_year']) ? Yii::app()->session['Search_year'] : date('Y');
        $curr_month = isset(Yii::app()->session['Search_month']) ? Yii::app()->session['Search_month'] : date("m");
        $trailer = isset(Yii::app()->session['Search_trailer']) ? Yii::app()->session['Search_trailer'] : 0;
        $reportdamage = isset(Yii::app()->session['Search_reportdamage']) ? Yii::app()->session['Search_reportdamage'] : 'all';

        if ($trailer == "--All Trailers--")
            $trailer = "0";

        //$records = $model->findAll($module, $tickettype, $currentyear, $curr_month, $trailer, $reportdamage);
        //$assetstatus = $model->findById('Assets', $firstkey);
        $this->render('surveylist', array('model' => $model, 'result' => $records, 'Assets' => $Asset_List, 'session' => Yii::app()->session));
    }
    
    
    public function actionsurveylistdata()
    {
        $module = "HelpDesk";
        $tickettype = "all";

        if (!isset(Yii::app()->session['gizur_table_id_index'])) {
            Yii::app()->session['gizur_table_id_index'] = 1;
            setcookie("SpryMedia_DataTables_table_id_index.php", "", time() - 3600);
            unset($_COOKIE['SpryMedia_DataTables_table_id_index.php']);
        }
       $minLimit = $_POST['minLimit'];
       $maxLimit = $_POST['maxLimit'];
        $model = new Troubleticket;
        $this->LoginCheck();

        $currentyear = isset(Yii::app()->session['Search_year']) ? Yii::app()->session['Search_year'] : date('Y');
        $curr_month = isset(Yii::app()->session['Search_month']) ? Yii::app()->session['Search_month'] : date("m");
        $trailer = isset(Yii::app()->session['Search_trailer']) ? Yii::app()->session['Search_trailer'] : 0;
        $reportdamage = isset(Yii::app()->session['Search_reportdamage']) ? Yii::app()->session['Search_reportdamage'] : 'all';

        if ($trailer == "--All Trailers--")
            $trailer = "0";

        $records = $model->findAll($module, $tickettype, $currentyear, $curr_month, $trailer, $reportdamage, $minLimit, $maxLimit);

$dataArray = array();
foreach ($records['result'] as $data) { 
                     $date = date('y-m-d', strtotime(Yii::app()->localtime->toLocalDateTime($data['createdtime'])));
                     $time = date('H:i', strtotime(Yii::app()->localtime->toLocalDateTime($data['createdtime'])));
                     $viewdteails = '<span id=' . $data['id'] . '></span><a href="index.php?r=troubleticket/surveydetails/' . $data['id'] . '" onclick=waitprocess("' . $data['id'] . '")>' . $data['accountname'] . '</a>';
                      $arrData = array('ticket_no'=>$data['ticket_no'],
                               'date'=>$date,
                               'time'=>$time,
                               'trailerid'=>$data['trailerid'],
                               'viewdteails' => $viewdteails,
                               'contactname' => $data['contactname'],
                               'damagereportlocation' => htmlentities($data['damagereportlocation'], ENT_QUOTES, "UTF-8"),
                               'damagestatus' => $data['damagestatus'],
                               'reportdamage' => $data['reportdamage'],
                               'damagetype' => htmlentities($data['damagetype'], ENT_QUOTES, "UTF-8"),
                               'damageposition' => htmlentities($data['damageposition'], ENT_QUOTES, "UTF-8"),
                               'drivercauseddamage' =>  $data['drivercauseddamage']
                   );
                   array_push($dataArray,$arrData);
                 }
     echo json_encode($dataArray);
   exit;
    }

    

    /**
     * This Action are create new Trouble Ticket 
     */
    public function actionsurvey()
    {
        $model = new Troubleticket;
        $this->LoginCheck();
        if (isset($_POST['submit'])) {
            $model->Save($_POST['Troubleticket']);
        }
        $pickList_sealed = $model->getpickList('sealed');
        $pickList_category = $model->getpickList('ticketcategories');
        $pickList_damagetype = $model->getpickList('damagetype');
        $pickList_damagepostion = $model->getpickList('damageposition');
        $picklist_drivercauseddamage = $model->getpickList('drivercauseddamage');
        $picklist_reportdamage = $model->getpickList('reportdamage');
        $picklist_ticketstatus = $model->getpickList('ticketstatus');
        $Asset_List = $model->findAssets('Assets');
        $postdata = @$_POST['Troubleticket'];
        $this->render('survey', array('model' => $model,
            'Sealed' => $pickList_sealed,
            'category' => $pickList_category,
            'damagetype' => $pickList_damagetype,
            'damagepos' => $pickList_damagepostion,
            'drivercauseddamageList' => $picklist_drivercauseddamage,
            'reportdamage' => $picklist_reportdamage,
            'Assets' => $Asset_List,
            'ticketstatus' => $picklist_ticketstatus,
            'postdata' => $postdata)
        );
    }

    /* This Action are Filter Ajax base Record */

    public function actionsurveysearch()
    {
        $module = "HelpDesk";
        $year = $_POST['year'];
        Yii::app()->session['Search_year'] = $year;
        $month = $_POST['month'];
        Yii::app()->session['Search_month'] = $month;
        $reportdamage = $_POST['reportdamage'];
        Yii::app()->session['Search_reportdamage'] = $reportdamage;
        $trailer = $_POST['trailer'];
        Yii::app()->session['Search_trailer'] = $trailer;
        $trailerid = $_POST['trailerid'];
        Yii::app()->session['Search_trailerid'] = $trailerid;
        if ($trailer == "--All Trailers--")
            $trailer = "0";
        $model = new Troubleticket;
        $this->LoginCheck();
$minLimit = $_POST['minLimit'];
$maxLimit = $_POST['maxLimit'];
        $records = $model->findAll($module, 'all', $year, $month, $trailer, $reportdamage, $minLimit, $maxLimit);
$dataArray = array();
foreach ($records['result'] as $data) {
                     $date = date('y-m-d', strtotime(Yii::app()->localtime->toLocalDateTime($data['createdtime'])));
                     $time = date('H:i', strtotime(Yii::app()->localtime->toLocalDateTime($data['createdtime'])));
                     $viewdteails = '<span id=' . $data['id'] . '></span><a href="index.php?r=troubleticket/surveydetails/' . $data['id'] . '" onclick=waitprocess("' . $data['id'] . '")>' . $data['accountname'] . '</a>';

                   $arrData = array('ticket_no'=>$data['ticket_no'],
                               'date'=>$date,
                               'time'=>$time,
                               'trailerid'=>$data['trailerid'],
                               'viewdteails' => $viewdteails,
                               'contactname' => $data['contactname'],
                               'damagereportlocation' => htmlentities($data['damagereportlocation'], ENT_QUOTES, "UTF-8"),
                               'damagestatus' => $data['damagestatus'],
                               'reportdamage' => $data['reportdamage'],
                               'damagetype' => htmlentities($data['damagetype'], ENT_QUOTES, "UTF-8"),
                               'damageposition' => htmlentities($data['damageposition'], ENT_QUOTES, "UTF-8"),
                               'drivercauseddamage' =>  $data['drivercauseddamage']
                   );
                   array_push($dataArray,$arrData);
                 }
 echo json_encode($dataArray);
exit;
    }

    /**
     * This Action are display releted Trouble Ticket details depand on trouble ticket ID 
     */
    public function actionsurveydetails()
    {
        $model = new Troubleticket;
        $this->LoginCheck();
        $module = "HelpDesk";
        $urlquerystring = $_SERVER['QUERY_STRING'];
        $paraArr = explode("/", $urlquerystring);
        $ticketId = $paraArr['2'];
        $storedata = $model->findById($module, $ticketId);

        $picklist_damagestatus = $model->getpickList('damagestatus');
        $this->render('surveydetails', array('model' => $model,
            'result' => $storedata,
            'damagestatus' => $picklist_damagestatus)
        );
    }

    /*
     *  Change Mark damage required function
     */

    public function actionmarkdamagestatus()
    {
        $model = new Troubleticket;
        $this->LoginCheck();
        $module = "HelpDesk";
        $ticketID = $_POST['ticketid'];
        $storedata = $model->Markdamagerequired($module, $ticketID);
        echo $storedata['result']['ticketstatus'];
        //$this->render('surveydetails',array('result'=>$storedata));  
    }

    /*
     * Update Damage Status and Notes
     */

    public function actiondamagestatusandnotes()
    {
        $model = new Troubleticket;
        $this->LoginCheck();
        $ticketID = $_POST['id'];
        $storedata = $model->updateDamageStatusAndNotes($ticketID, $_POST);
        echo $storedata['result']['ticketstatus'];
    }

    /**
     * This is the action to handle images.
     */
    public function actionimages()
    {
        $module = "DocumentAttachments";
        $urlquerystring = $_SERVER['QUERY_STRING'];
        $paraArr = explode("/", $urlquerystring);
        $ticketId = $paraArr['2'];
        $model = new Troubleticket;
        $imagedata = $model->getimage($module, $ticketId);
        header("Content-Type: image/jpeg");
        header("Content-Disposition: inline;filename=" . $imagedata['result']['filename']);
        echo base64_decode($imagedata['result'][filecontent]);
        die;
    }

    /**
     * This is the action to handle external exceptions.
     */
    public function actionError()
    {
        if ($error = Yii::app()->errorHandler->error) {
            if (Yii::app()->request->isAjaxRequest)
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
        $protocol = Yii::app()->params['protocol'];
        $servername = Yii::app()->request->getServerName();
        $user = Yii::app()->session['username'];
        if (empty($user)) {
            $returnUrl = $protocol . $servername . Yii::app()->homeUrl;
            $this->redirect($returnUrl);
        }
    }

    /**
     * This Action are Update Asset Status on click on inprocess operation  
     */
    function actionchangeassets()
    {
        $model = new Troubleticket;
        $this->LoginCheck();
        $tickettype = $_POST['tickettype'];
        $currentasset = $_POST['trailer'];
        $records = $model->ChangeAssetStatus($tickettype, $currentasset);

        if ($records['success']) {
            echo "Successfully Changed.";
        } else {
            echo "UnSuccessfully Changed.";
        }
        //$this->render('surveylist', array('msg'=>$records));
    }

}

?>
