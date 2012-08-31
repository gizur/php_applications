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
<style>
body{ margin:0px; padding:0px; font-family:Arial, Helvetica, sans-serif; font-size:12px;}
.warrper{ width:900px; margin:20px auto; border:#F00 solid 1px;}
.toppanel{ width:600px; height:auto; padding:10px 0 0px 0; background:#e3f0f7;border:#589fc8 solid 1px;}
.Survey{width:300px; height:auto; float:left; }
.Survey h2{ font-size:18px;  font-weight: normal; color:#2a7fae; margin:0px 0px 10px 0px; padding:0px;}
.Damage{width:850px; height:auto; float:right;}
.Damage h2{ font-size:18px;  font-weight: normal; color:#2a7fae; margin:0px 0px 10px 0px; padding:0px;}
.button{ background:#507aaa; border:#173d68 solid 1px; font-weight:bold; color:#fff; padding:2px 5px; margin-left:123px} 
.button a { font-weight:bold; color:#fff; padding:2px 5px 10px; text-decoration:none; } 
</style>
<?php
$this->pageTitle=Yii::app()->name . ' - New Ticket for Survey ';

echo CHtml::metaTag($content='My page description', $name='decription');

$this->breadcrumbs=array(
        'Trouble Ticket / Survey Details',
);
?>
<!--
<div style="float:right; margin-bottom:10px">
<a href="index.php?r=troubleticket/surveylist/">List of Survey Ticket</a></div>
-->
<div style="background:#E5E5E5"><strong>Ticket Information : <?php echo $result['result']['ticket_title']; ?></strong></div>	
<div class="Survey">

<h2>Survey</h2>
<?php 
/*
echo "<pre>";
print_r($result);
//die;
*/
?>
<table width="100%" border="0" bgcolor="#589fc8" cellspacing="1" cellpadding="5">
<tr>
    <td width="26%" bgcolor="e3f0f7"><strong>Tikect ID</strong></td>
    <td width="74%" bgcolor="e3f0f7"> <?php echo $result['result']['ticket_no']; ?></td>
  </tr>
   <tr>
    <td bgcolor="e3f0f7"><strong>Trailer ID</strong></td>
    <td bgcolor="e3f0f7"> <?php echo $result['result']['trailerid']; ?></td>
  </tr>
    <tr>
    <td bgcolor="e3f0f7"><strong>Date </strong></td>
    <td bgcolor="e3f0f7"><?php echo date('Y-m-d',strtotime($result['result']['createdtime'])); ?></td>
  </tr>
    <tr>
    <td bgcolor="e3f0f7"><strong>Time</strong></td>
    <td bgcolor="e3f0f7"><?php echo date('h:i',strtotime($result['result']['createdtime'])); ?></td>
  </tr>
    <tr>
    <td bgcolor="e3f0f7"><strong>Place </strong></td>
    <td bgcolor="e3f0f7"><?php echo $result['result']['damagereportlocation']; ?></td>
  </tr>
    <tr>
    <td bgcolor="e3f0f7"><strong>Acount</strong></td>
    <td bgcolor="e3f0f7"><?php echo $result['result']['accountname']; ?></td>
  </tr>
    <tr>
    <td bgcolor="e3f0f7"><strong>Contact</strong></td>
    <td bgcolor="e3f0f7"><?php echo $result['result']['contactname']['firstname'] . "&nbsp;".$result['result']['contactname']['lastname']; ?></td>
  </tr>
 
  </tr>
</table>
</div>

<div class="Damage">

<h2>Damage</h2>

<table width="100%" border="0" cellspacing="5" style="border:#589fc8 solid 1px; padding:5px;" cellpadding="0">
  <tr>
    <td width="50%" valign="top"><table width="100%" border="0" bgcolor="#589fc8" cellspacing="1" cellpadding="5">
  <tr>
    <td width="50%" bgcolor="7eb6d5"><strong>Type of Damage</strong></td>
    <td width="50%" bgcolor="7eb6d5">  <strong>Doors</strong></td>
  </tr>
   <tr>
    <td bgcolor="e3f0f7">Position on trailer</td>
    <td bgcolor="e3f0f7"><?php echo $result['result']['damageposition']; ?></td>
  </tr>
     <tr>
    <td bgcolor="e3f0f7">Status of damage</td>
    <td bgcolor="e3f0f7"><?php echo $result['result']['ticketstatus']; ?></td>
  </tr>
 
</table>

<br>
<input type="button" class="button" value="Mark damage repaired" />


</td>
    <td width="50%" valign="top"><table width="100%" border="0" bgcolor="#589fc8" cellspacing="1" cellpadding="5">
  <tr>
    <td colspan="2" bgcolor="7eb6d5"  valign="top"><strong>Pictures</strong></td>
    </tr>
   <tr>
    <td width="50%" bgcolor="e3f0f7" align="center"><img src="img.jpg" ></td>
    <td width="50%" bgcolor="e3f0f7" align="center"><img src="img.jpg" ></td>
  </tr>
     <tr>
    <td bgcolor="e3f0f7" align="center"><img src="img.jpg" ></td>
    <td bgcolor="e3f0f7" align="center"><img src="img.jpg" ></td>
  </tr>
</table>
</td>
  </tr>
</table>
</div>



