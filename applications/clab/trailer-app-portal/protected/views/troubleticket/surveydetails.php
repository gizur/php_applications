<!-- 
/**
	 * 
	 * 
	 * Created date : 04/07/2012
	 * Created By : Anil Singh
	 * @author Anil Singh <anil-singh@essindia.co.in>
	 * Flow : The basic flow of this page is Details of Trouble tickets.
	 * Modify date : 13/08/2012
	*/

-->
<?php 
include_once 'protected/extensions/langauge/'.Yii::app()->session['Lang'].'.php';
?>
<?php
$this->pageTitle=Yii::app()->name . ' - New Ticket for Survey ';

echo CHtml::metaTag($content='My page description', $name='decription');

$this->breadcrumbs=array(
        getTranslatedString('Trouble Ticket').'/'.getTranslatedString('Survey Details'),
);
?>
<div style="float:right; margin-bottom:10px" class="button">
<a href="index.php?r=troubleticket/surveylist/"><?php echo getTranslatedString('Trouble ticket List');?></a></div>

<div style="background:#E5E5E5; width:550px"><strong>Ticket Information : <?php echo $result['result']['ticket_title']; ?></strong></div>	
<div class="Survey">

<h2><?php echo getTranslatedString('Survey');?></h2>
<table width="100%" border="0" bgcolor="#589fc8" cellspacing="1" cellpadding="5">
<tr>
    <td width="26%" bgcolor="e3f0f7"><strong><?php echo getTranslatedString('Tikect ID');?></strong></td>
    <td width="74%" bgcolor="e3f0f7"> <?php echo $result['result']['ticket_no']; ?></td>
  </tr>
   <tr>
    <td bgcolor="e3f0f7"><strong><?php echo getTranslatedString('Trailer ID');?></strong></td>
    <td bgcolor="e3f0f7"> <?php echo $result['result']['trailerid']; ?></td>
  </tr>
    <tr>
    <td bgcolor="e3f0f7"><strong>Date </strong></td>
    <td bgcolor="e3f0f7"><?php echo date('Y-m-d',strtotime($result['result']['createdtime'])); ?></td>
  </tr>
    <tr>
    <td bgcolor="e3f0f7"><strong><?php echo getTranslatedString('Time');?></strong></td>
    <td bgcolor="e3f0f7"><?php echo date('h:i',strtotime($result['result']['createdtime'])); ?></td>
  </tr>
    <tr>
    <td bgcolor="e3f0f7"><strong><?php echo getTranslatedString('Place');?> </strong></td>
    <td bgcolor="e3f0f7"><?php echo $result['result']['damagereportlocation']; ?></td>
  </tr>
    <tr>
    <td bgcolor="e3f0f7"><strong><?php echo getTranslatedString('Acount');?></strong></td>
    <td bgcolor="e3f0f7"><?php echo $result['result']['accountname']; ?></td>
  </tr>
    <tr>
    <td bgcolor="e3f0f7"><strong><?php echo getTranslatedString('Contact');?></strong></td>
    <td bgcolor="e3f0f7"><?php echo $result['result']['contactname']['firstname'] . "&nbsp;".$result['result']['contactname']['lastname']; ?></td>
  </tr>
 
  </tr>
</table>
</div>

<div class="Damage">

<h2><?php echo getTranslatedString('Damage');?></h2>

<table width="100%" border="0" cellspacing="5" style="border:#589fc8 solid 1px; padding:5px;" cellpadding="0">
  <tr>
    <td width="50%" valign="top"><table width="100%" border="0" bgcolor="#589fc8" cellspacing="1" cellpadding="5">
  <tr>
    <td width="50%" bgcolor="7eb6d5"><strong><?php echo getTranslatedString('Type of damage');?></strong></td>
    <td width="50%" bgcolor="7eb6d5">  <strong><?php echo getTranslatedString('Doors');?></strong></td>
  </tr>
   <tr>
    <td bgcolor="e3f0f7"><?php echo getTranslatedString('Position on trailer');?></td>
    <td bgcolor="e3f0f7"><?php echo $result['result']['damageposition']; ?></td>
  </tr>
     <tr>
    <td bgcolor="e3f0f7"><?php echo getTranslatedString('Status of damage');?></td>
    <td bgcolor="e3f0f7"><?php echo $result['result']['ticketstatus']; ?></td>
  </tr>
 
</table>

<br>
<input type="button" class="button" value="<?php echo getTranslatedString('Mark damage repaired');?>" />


</td>
    <td width="15%" valign="top"><table width="100%" border="0" bgcolor="#589fc8" cellspacing="1" cellpadding="5">
  <tr>
    <td colspan="2" bgcolor="7eb6d5"  valign="top"><strong><?php echo getTranslatedString('Pictures');?></strong></td>
    </tr>
   <?php 
     $i=1;
  if(count($result['result']['documents'])>0){
   foreach($result['result']['documents'] as $image)
   {
       echo '<td width="50%" bgcolor="e3f0f7" align="center"><img src="http://localhost/gizurcloud/applications/clab/trailer-app-portal/index.php?r=troubleticket/images/'.$image['id'].'" width="100px" height="100px"></td>';  
         if($i%2==0)
         {
		  echo "</tr><tr>";
		  } 
		 $i++; 
	  }
	}  
    ?> 
  </tr>
</table>
</td>
  </tr>
</table>
</div>



