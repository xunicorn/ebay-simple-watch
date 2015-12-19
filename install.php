<?php
$install_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR;

function completeInstallation($message_type, $message) {
    global $install_dir;

    $user = Yii::app()->getComponent('user');

    $user->setFlash(
        $message_type, $message
    );

    rename($install_dir . 'installation', $install_dir . '.installation');

    Yii::app()->request->redirect('/index.php');
    Yii::app()->end();
}

if(file_exists($install_dir . '.installation')) {
    die('Service already installed');
}

// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

// change the following paths if necessary
$yii=dirname(__FILE__).'/_framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php';

require_once($yii);
Yii::createWebApplication($config);

$user = Users::model()->getRecordById(WebUser::ADMIN_ID);

$dirname = dirname(__FILE__);

if(!empty($user)) {
    completeInstallation('warning', 'Admin already created');
}

$model = new UserForm('create_admin');
$model->username = 'admin';

if(isset($_POST['UserForm'])) {
    $model->attributes = $_POST['UserForm'];

    if($model->validate()) {
        $user = new Users();
        $user->id = 1;
        $user->username = $model->username;
        $user->password = CPasswordHelper::hashPassword($model->password);
        $user->email    = $model->email;

        $user->email_verified = 1;

        if($user->save()) {
            MailHelper::sendUserCredentials($model->username, $model->email, $model->password);
            completeInstallation('success', 'Admin user successfully created');
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>Installation</title>

    <link rel="stylesheet" type="text/css" href="<?php echo Bootstrap::getBooster()->getAssetsUrl(); ?>/bootstrap/css/bootstrap.min.css" />
</head>
<body>
    <div class="span6 offset3">
        <h1 class="offset1">Installation</h1>
        <h2 class="offset1"><small>Enter admin credentials</small></h2>
        <?php if($model->hasErrors()): ?>
            <?php echo TbHtml::errorSummary($model); ?>
        <?php endif; ?>

        <form method="post" class="form-horizontal">

            <?php echo TbHtml::activeTextFieldControlGroup($model, 'username'); ?>
            <?php echo TbHtml::activePasswordFieldControlGroup($model, 'password'); ?>
            <?php echo TbHtml::activePasswordFieldControlGroup($model, 'password_verify'); ?>
            <?php echo TbHtml::activeEmailFieldControlGroup($model, 'email'); ?>

            <!--
                <div class="span3 control-group">

                    <div class="controls">
                        <?php //echo CHtml::textField('Users[username]', $model->username, array('maxlength' => 255, 'id' => 'Users_username')); ?>
                        <?php echo CHtml::activeTextField($model, 'username'); ?>
                    </div>
                </div>




                <div class="span3 control-group">
                    <div class="controls">
                        <?php //echo CHtml::passwordField('Users[password]', $model->password, array('maxlength' => 60)); ?>
                        <?php echo CHtml::activePasswordField($model, 'password'); ?>
                    </div>
                </div>



                <div class="span3 control-group">
                    <div class="controls">
                        <?php //echo CHtml::passwordField('Users[password_verified]', $model->password_verify, array('maxlength' => 60)); ?>
                        <?php echo CHtml::activePasswordField($model, 'password_verify'); ?>
                    </div>
                </div>



                <div class="span3 control-group">
                    <div class="controls">
                        <?php //echo CHtml::passwordField('Users[email]', $model->email, array('maxlength' => 255)); ?>
                        <?php echo CHtml::activeEmailField($model, 'email'); ?>
                    </div>
                </div>
            -->


            <div class=" control-group">
                <div class="controls">
                    <?php echo CHtml::submitButton('Create', array('class' => 'btn btn-primary')); ?>
                </div>
            </div>
        </form>
    </div>
</body>
</html>