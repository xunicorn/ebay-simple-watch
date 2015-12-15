<?php
/* @var $this SiteController */
/* @var $model UserForm */
/* @var $user Users */

?>

    <h3>Change user password <small>(<?php echo $user->username . ':' .$user->email; ?>)</small></h3>
<?php
/* @var $form TbActiveForm */
$form=$this->beginWidget('bootstrap.widgets.TbActiveForm',array(
    'id'=>'change-password-form',
    'enableAjaxValidation' => true,

    'enableClientValidation'=>true,


    'clientOptions'=>array(
        'validateOnSubmit'=>true,
    ),
)); ?>

<?php echo $form->errorSummary($model); ?>

<?php echo $form->textFieldRow($model,'password',array('class'=>'span3','maxlength'=>60));  ?>
<?php echo $form->textFieldRow($model,'password_verify',array('class'=>'span3','maxlength'=>60));  ?>

<?php //echo CHtml::label('New Password', 'password-field');  ?>
<?php //echo CHtml::textField('Users[password]', null,array('class'=>'span3','maxlength'=>60, 'id' => 'password-field'));  ?>


    <div class="form-actions">
        <?php $this->widget('bootstrap.widgets.TbButton', array(
            'buttonType'=>'submit',
            'type'=>'primary',
            'label'=> 'Update',
        )); ?>
    </div>

<?php $this->endWidget(); ?>