<!-- 
/**
	 * 
	 * 
	 * Created date : 04/07/2012
	 * Created By : Anil Singh
	 * @author Anil Singh <anil-singh@essindia.co.in>
	 * Flow : The basic flow of this page is List of Trouble tickets (Survey).
	 * Modify date : 13/08/2012
	*/

-->
<?php
$this->pageTitle=Yii::app()->name . ' - Trouble Ticket List ';

echo CHtml::metaTag($content='My page description', $name='decription');

$this->breadcrumbs=array(
        'Trouble Ticket / Trouble Ticket List',
);
?>
<div style="float:right; margin-bottom:10px">
<a href="index.php?r=troubleticket/survey/">Create Trouble Ticket</a></div>

<?php
$columnsArray = array('id','Date.','Time','Account (Tronspoter)','Contact (Driver)','Place',
'Type of Damage','Position on trailer');
$rowsArray = array();
$i=1;
$result['result']=array(1,2,3,4,5);
foreach($result['result'] as $data)
{ 
	
	$date=date('y-m-d',strtotime($data['createdtime']));
	$time=date('h:i',strtotime($data['createdtime']));
	$viewdteails='<a href="index.php?r=troubleticket/surveydetails/'.$data['id'].'">'.$data['account'].'</a>';
	$ticketNo = '<a href="index.php?r=troubleticket/surveydetails/'.$data['id'].'">'.$data['date'].'</a>';
	$rowsArray[] = array($i,$date,$time,$viewdteails,$data['contact'],
	$data['cf_634'],$data['cf_635'],$data['cf_636']);
	$i++;
}

$this->widget('ext.htmltableui.htmlTableUi',array(
    'ajaxUrl'=>'site/handleHtmlTable',
    'arProvider'=>'',    
    'collapsed'=>false,
    'columns'=>$columnsArray,
    'cssFile'=>'',
    'editable'=>false,
    'enableSort'=>true,
    'footer'=>'Total rows: '.count($rowsArray),
    'formTitle'=>'Form Title',
    'rows'=>$rowsArray,
    'sortColumn'=>1,
    'sortOrder'=>'desc',
    'title'=>'List of Trouble Ticket',
));
?>
