<?php
/**
 * Created by PhpStorm.
 * User: Администратор
 * Date: 20.11.2015
 * Time: 14:38
 */

class MailHelper {
    const TEMPLATE_REGISTRATION_CONFIRM         = 'registration-confirm.php';
    const TEMPLATE_REGISTRATION_CONFIRM_SUCCESS = 'registration-confirm-success.php';
    const TEMPLATE_REGISTRATION_GREETING        = 'registration-greetings.php';
    const TEMPLATE_REGISTRATION_FORGOT_PASSWORD = 'forgot-password.php';
    const TEMPLATE_USER_CREDENTIALS           = 'user-credentials.php';

    public static function sendUserCredentials($username, $user_email, $password) {
        $serviceUrl      = Yii::app()->request->getHostInfo();
        $serviceName     = Yii::app()->name;
        $admin_email     = Yii::app()->params['adminEmail'];


        $filepath = Yii::getPathOfAlias('application.data.email-templates') . '/' . MailHelper::TEMPLATE_USER_CREDENTIALS;

        $content = file_get_contents($filepath);

        $search = array(
            '{serviceUrl}',
            '{serviceName}',
            '{login}',
            '{password}',
            PHP_EOL,
        );

        $replace = array(
            $serviceUrl,
            $serviceName,
            $username,
            $password,
            '<br/>',
        );

        $content = str_replace($search, $replace, $content);

        /* @var $mailer MultiMailer */
        $mailer = Yii::app()->MultiMailer;

        $mailer->from($admin_email, $serviceName)->to($user_email, $username)->subject('New user account')->body($content)->send();
    }

    public static function sendRegisterConfirmMail($username, $user_email, $user_confirm_url) {
        $serviceUrl      = Yii::app()->request->getHostInfo();
        $serviceName     = Yii::app()->name;
        $confirmationUrl = Yii::app()->createAbsoluteUrl('/site/emailConfirm', array('code' => $user_confirm_url));
        $admin_email     = Yii::app()->params['adminEmail'];

        $filepath = Yii::getPathOfAlias('application.data.email-templates') . '/' . MailHelper::TEMPLATE_REGISTRATION_CONFIRM;

        $content = file_get_contents($filepath);

        $search = array(
            '{serviceUrl}',
            '{serviceName}',
            '{confirmationUrl}',
            PHP_EOL,
        );

        $replace = array(
            $serviceUrl,
            $serviceName,
            $confirmationUrl,
            '<br/>',
        );

        $content = str_replace($search, $replace, $content);

        /* @var $mailer MultiMailer */
        $mailer = Yii::app()->MultiMailer;

        $mailer->from($admin_email, $serviceName)->to($user_email, $username)->subject('User registration')->body($content)->send();
    }

    public static function sendRegisterConfirmSuccess($user_email) {
        $serviceUrl      = Yii::app()->request->getHostInfo();
        $serviceName     = Yii::app()->name;
        $admin_email     = Yii::app()->params['adminEmail'];

        $filepath = Yii::getPathOfAlias('application.data.email-templates') . '/' . MailHelper::TEMPLATE_REGISTRATION_CONFIRM_SUCCESS;

        $content = file_get_contents($filepath);

        $search = array(
            '{serviceUrl}',
            '{serviceName}',
            '{email}',
            PHP_EOL,
        );

        $replace = array(
            $serviceUrl,
            $serviceName,
            $user_email,
            '<br/>',
        );

        $content = str_replace($search, $replace, $content);

        /* @var $mailer MultiMailer */
        $mailer = Yii::app()->MultiMailer;

        $mailer->from($admin_email, $serviceName)->to($user_email)->subject('Welcome to our service')->body($content)->send();
    }

    public static function sendForgotPasswordMail($user_email, $user_confirm_url) {
        $serviceUrl      = Yii::app()->request->getHostInfo();
        $serviceName     = Yii::app()->name;
        $admin_email     = Yii::app()->params['adminEmail'];
        $confirmationUrl = Yii::app()->createAbsoluteUrl('/site/forgotPassword', array('code' => $user_confirm_url));


        $filepath = Yii::getPathOfAlias('application.data.email-templates') . '/' . MailHelper::TEMPLATE_REGISTRATION_FORGOT_PASSWORD;

        $content = file_get_contents($filepath);

        $search = array(
            '{serviceUrl}',
            '{serviceName}',
            '{email}',
            '{confirmationUrl}',
            PHP_EOL,
        );

        $replace = array(
            $serviceUrl,
            $serviceName,
            $user_email,
            $confirmationUrl,
            '<br/>',
        );

        $content = str_replace($search, $replace, $content);

        /* @var $mailer MultiMailer */
        $mailer = Yii::app()->MultiMailer;

        $mailer->from($admin_email, $serviceName)->to($user_email)->subject('Change password for account')->body($content)->send();
    }
} 