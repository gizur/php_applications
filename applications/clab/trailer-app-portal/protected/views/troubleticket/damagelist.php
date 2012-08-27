<!-- 
/**
	 * 
	 * 
	 * Created date : 04/07/2012
	 * Created By : Anil Singh
	 * @author Anil Singh <anil-singh@essindia.co.in>
	 * Flow : The basic flow of this page is List of Trouble tickets (Damage).
	 * Modify date : 13/08/2012
	*/

-->
<?php
$this->pageTitle=Yii::app()->name . ' - Damage Ticket List ';

echo CHtml::metaTag($content='My page description', $name='decription');

$this->breadcrumbs=array(
        'Trouble Ticket / Damage List',
);
?>
<div style="float:right; margin-bottom:10px">
<a href="index.php?r=troubleticket/damagelist/">New Damage Ticket</a></div>

<?php
$columnsArray = array('id','Ticket No.','Status','Title','Ticket Category','Type Of Damage',
'Position On Trailer For Damage','Create date');
$rowsArray = array();
$i=1;
foreach($result['result'] as $data)
{ 
	$viewdteails = '<a href="index.php?r=troubleticket/damagedetails/'.$data['id'].'">'.$data['ticket_title'].'</a>';
	$ticketNo = '<a href="index.php?r=troubleticket/damagedetails/'.$data['id'].'">'.$data['ticket_no'].'</a>';
	$rowsArray[] = array($i,$ticketNo,$data['ticketstatus'],
	$viewdteails,$data['ticketcategories'],$data['cf_635'],
		$data['cf_636'],$data['createdtime']);
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
    'title'=>'List of Damage Report',
));
?>
