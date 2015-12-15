<?php

class SiteController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

    protected function beforeAction($action)
    {
        $this->requests = array();
        $this->listings = array();

        return parent::beforeAction($action);
    }


    /**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'
		$this->render('index');
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}

	/**
	 * Displays the contact page
	 */
	public function actionContact()
	{
		$model=new ContactForm;
		if(isset($_POST['ContactForm']))
		{
			$model->attributes=$_POST['ContactForm'];
			if($model->validate())
			{
				$name='=?UTF-8?B?'.base64_encode($model->name).'?=';
				$subject='=?UTF-8?B?'.base64_encode($model->subject).'?=';
				$headers="From: $name <{$model->email}>\r\n".
					"Reply-To: {$model->email}\r\n".
					"MIME-Version: 1.0\r\n".
					"Content-Type: text/plain; charset=UTF-8";

				mail(Yii::app()->params['adminEmail'],$subject,$model->body,$headers);
				Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		$model=new LoginForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
            try {

                if ($model->validate() && $model->login())
                    $this->redirect(array('/search/index'));

            } catch(Exception $ex) {
                $this->setFlashError($ex->getMessage());
            }
		}
		// display the login form
		$this->render('login',array('model'=>$model));
	}

    public function actionRegister() {
        $model = new UserForm('create');

        if(isset($_POST['ajax']) && $_POST['ajax']==='register-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        if(isset($_POST['UserForm'])) {
            $model->attributes = $_POST['UserForm'];

            if($model->register()) {
                $this->setFlashSuccess('An email was sent to you email for confirmation');
                $this->redirect(array('login'));
            }
        }

        $this->render('register',array('model'=>$model));
    }

    public function actionForgotPassword($code = false) {
        if(!empty($code)) {
            $user = Users::model()->getUserByMaintenanceUrl($code);

            if(empty($user)) {
                throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
            }

            $user->url_maintenance = false;
            $user->save();

            Yii::app()->session['forgot-password'] = 1;
            Yii::app()->session['user-model'] = $user;

            $this->setFlashSuccess('Now you can change your password');

            $this->redirect(array('changePassword'));
        }

        if(isset($_POST['credentials'])) {
            $user = Users::model()->getUserByEmail($_POST['credentials']);

            if(empty($user)) {
                $user = Users::model()->getUserByUsername($_POST['credentials']);
            }

            if(empty($user)) {
                $this->setFlashError('Username / Email is invalid');
            } else {
                $url_maintenance = $user->getMaintenanceUrl();
                $user->save();

                MailHelper::sendForgotPasswordMail($user->email, $url_maintenance);

                $this->setFlashSuccess('Instructions were sent to your e-mail. Please follow them for password change.');

                $this->redirect(array('login'));
            }
        }

        $this->render('forgot_password');
    }

    public function actionChangePassword() {
        if(!isset(Yii::app()->session['forgot-password'])) {
            $this->setFlashError('Url is invalid');
            $this->redirect(array('index'));
        }

        /* @var $user Users */
        $user  = Yii::app()->session['user-model'];

        $model = new UserForm('change_password');

        if(isset($_POST['ajax']) && $_POST['ajax']==='change-password-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        if(isset($_POST['UserForm'])) {
            $model->attributes = $_POST['UserForm'];

            if($model->validate()) {
                $user->password = CPasswordHelper::hashPassword($model->password);
                $user->save();

                $this->setFlashSuccess('You successfully changed password');

                unset(Yii::app()->session['forgot-password']);
                unset(Yii::app()->session['user-model']);

                $this->redirect(array('login'));
            } else {
                $this->setFlashError('Some errors in your inputs. Fix them and retry');
            }
        }

        $data = array(
            'model' => $model,
            'user'  => $user,
        );

        $this->render('change_password', $data);
    }

    public function actionEmailConfirm($code) {
        $user = Users::model()->getUserByMaintenanceUrl($code);

        if(empty($user)) {
            throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
        }

        $user->url_maintenance = false;
        $user->email_verified  = 1;
        $user->save();

        MailHelper::sendRegisterGreetingMail($user->email);

        $this->setFlashSuccess('You successfully confirmed email. Now you can use your account');

        $this->redirect(array('/site/login'));
    }

    public function actionResendConfirmation($user_id) {
        $user = Users::model()->getUserById($user_id);

        if(empty($user)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } elseif($user->email_verified) {
            $this->setFlashInfo('User email already verified');

            $this->redirect(array('login'));
        }

        $url_maintenance = $user->getMaintenanceUrl();

        MailHelper::sendRegisterConfirmMail($user->username, $user->email, $url_maintenance);

        $this->setFlashSuccess('New confirm email was sent to your email');

        $this->redirect(array('login'));
    }

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}

    public function actionCreateUrlAjax() {

        if(!Yii::app()->request->isAjaxRequest) {
            throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
        }

        $response = array(
            'result' => false,
        );

        if(isset($_POST['NewUrl'])) {
            $new_url_params = $_POST['NewUrl'];

            $action = $new_url_params['action'];
            $params = $new_url_params['params'];

            //$new_url_arr = array_merge(array($action), $params);
            $new_url_str = $this->createUrl($action, $params);

            $response = array(
                'result' => true,
                'url'    => $new_url_str,
            );
        }

        echo CJSON::encode($response);

        Yii::app()->end();

    }
}