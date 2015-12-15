<?php
/* @var $this SiteController */
/* @var $model RegisterForm */
/* @var $form CActiveForm  */

$this->pageTitle=Yii::app()->name . ' - Register';
$this->breadcrumbs=array(
	'Register',
);
?>

<h3>Create User</h3>

<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'register-form',

    'enableAjaxValidation' => true,

    'enableClientValidation'=>true,


    'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),

)); ?>

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
		<?php echo $form->labelEx($model,'password_verify'); ?>
		<?php echo $form->passwordField($model,'password_verify'); ?>
		<?php echo $form->error($model,'password_verify'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'email'); ?>
		<?php echo $form->emailField($model,'email'); ?>
		<?php echo $form->error($model,'email'); ?>
	</div>

    <div class="row">
        <?php echo $form->labelEx($model,'verified'); ?>
        <?php echo $form->checkBox($model,'verified'); ?>
    </div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Register', array( 'class' => 'btn btn-primary')); ?>
	</div>

<?php $this->endWidget(); ?>
</div><!-- form -->
