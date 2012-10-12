<?php
$this->pageTitle=Yii::app()->name . ' - Login';
$this->breadcrumbs=array(
	'Login',
);
?>
<center>
<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'login-form',
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
		<?php echo $form->labelEx($model,'username'); ?>
		<?php echo $form->textField($model,'username'); ?>
		<?php echo $form->error($model,'username'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'password'); ?>
		<?php echo $form->passwordField($model,'password'); ?>
		<?php echo $form->error($model,'password'); ?>
		</div>

   <div class="row">
		<?php echo $form->labelEx($model,'language'); ?>
		<?php echo $form->dropDownList($model,'language' ,Yii::app()->params['language']); ?>
		<?php echo $form->error($model,'language'); ?>
		</div>
	<div class="row rememberMe">
		<?php echo $form->checkBox($model,'rememberMe'); ?>
		<?php echo $form->label($model,'rememberMe'); ?>
		<?php echo $form->error($model,'rememberMe'); ?>
	</div>

	<div class="row buttons">
<?php echo CHtml::submitButton('Login'); ?> &nbsp;&nbsp;<a href="<?php echo Yii::app()->request->baseUrl; ?>/index.php?r=site/resetpassword/" id="resetp">Reset Password
</a>	</div>  

<?php $this->endWidget(); ?>
</div><!-- form -->
