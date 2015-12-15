<?php
/* @var $this UsersController */
/* @var $model Users */

?>

<h3>Update user password <small>(<?php echo $model->username . ':' .$model->email; ?>)</small></h3>
<?php
/* @var $form TbActiveForm */
$form=$this->beginWidget('bootstrap.widgets.TbActiveForm',array(
    'id'=>'users-form',
    'enableAjaxValidation'=>false,
)); ?>

<?php echo $form->errorSummary($model); ?>

<?php //echo $form->textFieldRow($model,'password',array('class'=>'span3','maxlength'=>60));  ?>
<?php echo CHtml::label('New Password', 'password-field');  ?>
<?php echo CHtml::textField('Users[password]', null,array('class'=>'span3','maxlength'=>60, 'id' => 'password-field'));  ?>


    <div class="form-actions">
        <?php $this->widget('bootstrap.widgets.TbButton', array(
            'buttonType'=>'submit',
            'type'=>'primary',
            'label'=>$model->isNewRecord ? 'Create' : 'Save',
        )); ?>
    </div>

<?php $this->endWidget(); ?>