<?php

class UsersController extends Controller
{
    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    //public $layout='//layouts/column2';

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow',
                'actions' => array('update'),
                'users'   => array('@'),
            ),

            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('create'),
                'users'=>array('admin'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions'=>array('admin','delete', 'verifyUser'),
                'users'=>array('admin'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate()
    {
        $model = new UserForm('create');

        if(isset($_POST['ajax']) && $_POST['ajax']==='register-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        if(isset($_POST['UserForm'])) {
            $model->attributes = $_POST['UserForm'];

            if($model->registerByAdmin()) {
                $this->setFlashSuccess('User successfully created');
                $this->redirect(array('admin'));
            }
        }

        $this->render('create',array('model'=>$model));
    }

    //public function actionCreateAdmin

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id)
    {
        $model=$this->loadModel($id);

        if(!WebUser::isAdmin()) {
            if(WebUser::Id() != $id) {
                throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
            }
        }

// Uncomment the following line if AJAX validation is needed
// $this->performAjaxValidation($model);

        if(isset($_POST['Users']))
        {
            throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');

            $model->attributes=$_POST['Users'];

            $model->password = CPasswordHelper::hashPassword(trim($model->password));

            if($model->save()) {
                $this->setFlashSuccess('User password for <strong>' . $model->username . '</strong> successfully changed');
                $this->redirect(array('admin'));
            }
        }

        $this->render('update',array(
            'model'=>$model,
        ));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id)
    {
        if(Yii::app()->request->isPostRequest and in_array($id, array(1,2,3))) // for demo
        {
// we only allow deletion via POST request
            $this->loadModel($id)->delete();

// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if(!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
        }
        else
            throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
    }

    /**
     * Manages all models.
     */
    public function actionAdmin()
    {
        $model=new Users('search');
        $model->unsetAttributes();  // clear any default values
        if(isset($_GET['Users']))
            $model->attributes=$_GET['Users'];

        $this->render('admin',array(
            'model'=>$model,
        ));
    }

    public function actionVerifyUser($id) {
        $user = $this->loadModel($id);

        $user->email_verified = 1;
        $user->save();

        if(!isset($_GET['ajax']))
            $this->redirect(array('admin'));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     * @return Users
     */
    public function loadModel($id)
    {
        $model=Users::model()->findByPk($id);
        if($model===null)
            throw new CHttpException(404,'The requested page does not exist.');
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if(isset($_POST['ajax']) && $_POST['ajax']==='users-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
