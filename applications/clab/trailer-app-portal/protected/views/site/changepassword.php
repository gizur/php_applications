<?php
$this->pageTitle=Yii::app()->name . ' - Login';
$this->breadcrumbs=array(
	'Change Password',
);
?>
<center>
<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'changepass-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
	'validateOnSubmit'=>true,
	),
)); ?>
   <?php
    foreach(Yii::app()->user->getFlashes() as $key => $message) {
        echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
    }
?>
	<div class="row">
		<?php echo $form->labelEx($model,'Old Password '); ?>
		<?php echo $form->passwordField($model,'oldpassword'); ?>
		<?php echo $form->error($model,'oldpassword'); ?>
	</div>
	
	<div class="row">
		<?php echo $form->labelEx($model,'New Password'); ?>
		<?php echo $form->passwordField($model,'newpassword'); ?>
		<?php echo $form->error($model,'newpassword'); ?>
	</div>
	
	<div class="row">
		<?php echo $form->labelEx($model,'Confirm Password'); ?>
		<?php echo $form->passwordField($model,'newpassword1'); ?>
		<?php echo $form->error($model,'newpassword1'); ?>
	</div>

	
	<div class="row buttons">
		<?php echo CHtml::submitButton('Change Password', array('id'=>'submit','name'=>'submit')); ?> &nbsp;&nbsp;<a href="<?php echo Yii::app()->request->baseUrl; ?>/index.php" id="resetp"> << Back >>
</a>	</div>  

<?php $this->endWidget(); ?>
</div><!-- form -->
