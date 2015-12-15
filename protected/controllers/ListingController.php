<?php

class ListingController extends Controller
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
            /*
            array('allow',  // allow all users to perform 'index' and 'view' actions
                'actions'=>array('index','view'),
                'users'=>array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('create','update'),
                'users'=>array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions'=>array('admin','delete'),
                'users'=>array('admin'),
            ),
            */
            array('allow',
                'actions' => array('index', 'admin', 'delete', 'list',
                    'deleteFromList', 'changeName', 'createFromUserList', 'updateFromUserList', 'doAction' ),
                'users'=>array('@'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public function actionCreateFromUserList() {
        if(Yii::app()->request->isPostRequest) {
            $results = array( 'success' => false );

            if(isset($_POST['ListingNames'])) {
                $date_create = time();

                $new_name    = $_POST['ListingNames']['name'];
                $items_attrs = json_decode($_POST['ListingNames']['items'], true);
/*
                if(isset($_POST['ListingNames']['items'])) {
                    $items_attrs = json_decode($_POST['ListingNames']['items'], true);
                }
*/
                $list_attributes = array(
                    'name'        => $new_name,
                    'user_id'     => WebUser::Id(),
                    'date_create' => time(),
                    'date_update' => time(),
                );

                $model=new ListingNames;
                $model->attributes = $list_attributes;

                if($model->save()) {
                    if(!empty($items_attrs)) {
                        $this->_addItemsToList($items_attrs, $model->id);

                        $results['success'] = true;
                    } else {
                        $results['success'] = true;
                    }

                    $results['list_id'] = $model->id;
                    $results['url']     = $this->createUrl('/listing/list', array('id' => $model->id));
                }
            }

            echo CJSON::encode($results);
        } else
            throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
    }

    public function actionUpdateFromUserList() {
        if(Yii::app()->request->isPostRequest) {
            $results = array( 'success' => false );

            if(isset($_POST['ListingNames'])) {
                $list_id = $_POST['ListingNames']['list_id'];
                $items_attrs = json_decode($_POST['ListingNames']['items'], true);

                $model = $this->loadModel($list_id);
                $model->date_update = time();
                $model->save();

                $this->_addItemsToList($items_attrs, $list_id);

                $results['success'] = true;
            }

            echo CJSON::encode($results);
        } else {
            throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
        }
    }

    protected function _addItemsToList($items, $list_id) {
        $date_create = time();

        ListingItems::model()->saveMultiple($items);

        $ebay_ids = array();

        foreach($items as $_attr) {
            $ebay_ids[] = $_attr['ebay_id'];
        }

        $listing_items_ids = ListingItems::model()->getIdsByEbayIds($ebay_ids);

        $intermediate_attrs = array();

        foreach($listing_items_ids as $_id) {
            $intermediate_attrs[] = array(
                'listing_name_id' => $list_id,
                'listing_item_id' => $_id,
                'date_add'        => $date_create,
            );
        }

        ListingNamesItems::model()->saveMultipleIgnore($intermediate_attrs);
    }

    public function actionChangeName() {
        if(Yii::app()->request->isPostRequest) {
            $model = $this->loadModel($_POST['pk']);
            $model->name = $_POST['value'];
            $model->save();

        } else
            throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
    }

    public function actionDoAction() {
        if(Yii::app()->request->isPostRequest) {
            if(isset($_POST['ListingNames'])) {
                $ids    = $_POST['ListingNames']['id'];
                $action = $_POST['action'];

                switch($action) {
                    case 'delete':
                        foreach($ids as $_id) {
                            $list = $this->loadModel($_id);

                            if($list->ignored) {
                                continue;
                            }

                            ListingNames::model()->deleteList($_id);
                        }

                        $this->setFlashSuccess(count($ids) . ' lists successfully deleted');
                        break;

                    case 'truncate':
                        foreach($ids as $_id) {
                            ListingNames::model()->truncateList($_id);
                        }

                        $this->setFlashSuccess(count($ids) . ' lists successfully erasing');
                        break;

                    default: break;
                }
            }

            $this->redirect(array('admin'));
        } else
            throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id)
    {
        if(Yii::app()->request->isPostRequest)
        {
// we only allow deletion via POST request
            $list = $this->loadModel($id);

            if($list->ignored) {
                throw new CHttpException(400, 'You could not delete ignore-list');
            }

            if($list->user_id != WebUser::Id() or !WebUser::isAdmin()) {
                throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
            }

            ListingNames::model()->deleteList($id);

// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if(!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
        }
        else
            throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
    }

    public function actionDeleteFromList($id) {
        if(Yii::app()->request->isPostRequest)
        {
            $response = array(
                'success' => false,
            );

            $list = $this->loadModel($id);

            if(isset($_POST['ListItems']['items'])) {
                $list->date_update = time();

                $items = array_filter(array_map('trim', explode(' ', $_POST['ListItems']['items'])));

                ListingItems::model()->deleteItemsFromList($id, $items);

                $response['success'] = true;
            }

            echo CJSON::encode($response);
        }else
            throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
    }

    public function actionList($id)
    {
        $listing = $this->loadModel($id);

        $items = ListingItems::model()->getItemsFromList($id);

        if(!empty($items)) {
            $ebay_ids = CHtml::listData($items, 'id', 'ebay_id');

            $listingApi = new GetMultipleItemsClass();
            $listingApi->setItemsIds($ebay_ids);
            $items = $listingApi->makeAPICall();

        } else {
            $this->setFlashWarning('Empty items list');
        }

        //$this->enableDeleteFromListBtn($id);

        $this->actions_menu = array(
            array( 'label' => 'Delete From List', 'url' => '#', 'itemOptions' => array( 'class' => 'delete-from-list-btn' )),
            array( 'label' => 'Delete From List (Ended)', 'url' => '#', 'itemOptions' => array( 'class' => 'delete-from-list-ended-btn' )),
        );

        $data = array(
            'items' => $items,
            'list'  => $listing,
        );

        $this->render('list', $data);
    }

    /**
     * Manages all models.
     */
    public function actionAdmin()
    {
        $model=new ListingNames('search');
        $model->unsetAttributes();  // clear any default values
        if(isset($_GET['ListingNames']))
            $model->attributes=$_GET['ListingNames'];

        //echo '<pre>'; print_r('Bingo!'); echo '</pre>';

        $this->render('admin',array(
            'model'=>$model,
        ));

    }

    /**
     * Lists all models.
     */
    public function actionIndex(){

        $_condition = !WebUser::isAdmin() ? 'user_id=' . WebUser::Id() : '';

        $dataProvider=new CActiveDataProvider('ListingNames', array(
            'criteria'    => array(
                'order' => 'user_id, ignored DESC',
                'condition' => $_condition,
            ),
        ));

        $this->registerXEditable();

        $this->render('index',array(
            'dataProvider'=>$dataProvider,
        ));
    }

    public function actionItem($id) {
        $itemAPI = new GetSingleItemClass($id);

        try {
            $itemAPI->makeAPICall();
        } catch(Exception $ex) {
            $this->setFlashError($ex->getMessage());
        }

        $this->render('item');
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     * @return ListingNames
     */
    public function loadModel($id)
    {
        $model=ListingNames::model()->findByPk($id);
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
        if(isset($_POST['ajax']) && $_POST['ajax']==='listing-names-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
