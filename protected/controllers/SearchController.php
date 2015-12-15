<?php

class SearchController extends Controller
{

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
            array('allow',  // allow all users to perform 'index' and 'view' actions
                'actions'=>array('index', 'listing'),
                'users'=>array('*'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions'=>array('admin','delete'),
                'users'=>array('admin'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
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
            $this->loadModel($id)->delete();

// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if(!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
        }
        else
            throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
    }

    /**
     * @param int $id
     * @param  $ebay_global_id
     * @throws CHttpException
     */
    public function actionIndex($id = 0, $ebay_global_id = false)
    {
        Yii::import('application.vendors.CategoryTree');

        if(empty($id)) {
            $model = new SearchRequests();
            //$model->unsetAttributes();  // clear any default values

            if(empty($ebay_global_id)) {
                $ebay_global_id = 'EBAY-US';
            }

            $model->ebay_global_id = $ebay_global_id;
        } else {
            $model = $this->loadModel($id);

            if(!empty($ebay_global_id)) {
                $model->ebay_global_id = $ebay_global_id;
            } else {
                $ebay_global_id = $model->ebay_global_id;
            }
        }

        if(isset($_POST['SearchRequests'])) {

            $attributes = $_POST['SearchRequests'];

            if(isset($_POST['request_type']) and $_POST['request_type'] == 'new') {
                //$model->is
                $model->isNewRecord = true;
                $model->id          = null;

                unset($attributes['id']);
            }

            if($model->isNewRecord) {
                $attributes['user_id'] = WebUser::Id(true);
            }

            $attributes['date_update'] = time();

            $model->attributes = $attributes;

            if($model->save()) {
                $this->redirect(array('listing', 'id' => $model->id));
            } else {
                $this->setFlashError(Yii::t('sniper_ebay', 'Correct those fields and try again'));
            }
        }

        $categories = false;

        if(empty($categories)) {
            $categoriesAPI = new GetCategoriesClass();

            $categories[0] = 'All';

            try {
                $ebay_site_id = SearchRequests::model()->getSiteEbayId($ebay_global_id);

                $categoriesAPI->siteID = $ebay_site_id;
                if($ebay_global_id == 'EBAY-MOTOR') {
                    $categoriesAPI->levelLimit = 4;
                }


                $ebay_cats = $categoriesAPI->makeAPICall();

                $cats_attributes = array();

                $categories_tree = array();

                foreach($ebay_cats as $_e_category) {
                    $cats_attributes[] = array(
                        'ebay_category_id'    => (int)$_e_category->CategoryID,
                        'category_name'       => (string)$_e_category->CategoryName,
                        'ebay_category_level' => (int)$_e_category->CategoryLevel,
                        'ebay_parent_id'      => (int)$_e_category->CategoryParentID,
                        'auto_pay'            => $_e_category->AutoPayEnabled == 'true' ? 1 : 0,
                        'best_offer'          => $_e_category->BestOfferEnabled == 'true' ? 1 : 0,
                    );

                    $categories[(int)$_e_category->CategoryID] = (string)$_e_category->CategoryName;

                    $_id = (int)$_e_category->CategoryID;
                    $_parent_id = (int)$_e_category->CategoryParentID;


                    $categories_tree[] = array(
                        'id'        => $_id,
                        'parent_id' => ($_id == $_parent_id ? 0 : $_parent_id),
                        'title'     => (string)$_e_category->CategoryName,
                    );

                }

                $tree = new CategoryTree($categories_tree);
                $categories = $tree->getOneDimTree();

                //Categories::model()->saveMultiple($cats_attributes);

            } catch (Exception $ex) {
                $this->setFlashError($ex->getMessage());
            }
        } else {
            $categories = CHtml::listData($categories, 'ebay_category_id', 'category_name');
        }

        $data = array(
            'categories' => $categories,
            'model'      => $model,
        );

        $this->render('index',$data);
    }

    public function actionListing($id) {//array $request = array()

        $request = $this->loadModel($id);

        $atributes = $request->attributes;
        $atributes['auction_type'] = SearchRequests::model()->getAuctionType($request->auction_type_id);

        $searchApi = new FindItemsAdvancedClass($atributes);

        $items_ignored = array();

        if($request->only_new) {
            $items_ignored = $request->items;
        } elseif($request->ignore_list) {
            $list = ListingNames::model()->getUserIgnoreList(WebUser::Id());

            $items_ignored = $list->items;
        }

        if(!empty($items_ignored)) {
            $ignore_ids = CHtml::listData($items_ignored, 'id', 'ebay_id');

            $searchApi->setIgnoreIds($ignore_ids);
        }

        try {
            $items = $searchApi->makeAPICall();

            $ebay_ids    = array();
            $items_attrs = array();

            $date = time();

            foreach($items as $_item) {
                $items_attrs[] = array(
                    'ebay_id'     => $_item->itemId,
                    'title'       => $_item->title,
                    'url_picture' => $_item->pictureUrl,
                    'url_item'    => $_item->itemUrl,
                    'buy_it_now'  => intval($_item->buyItNow>0),
                    'date_of_added' => $date,
                );

                $ebay_ids[] = $_item->itemId;
            }

            SearchItems::model()->saveMultipleIgnore($items_attrs);
            SearchRequests::model()->addItemsToRequest($id, $ebay_ids);

        } catch(Exception $ex) {
            $this->setFlashWarning($ex->getMessage());

            $items = $searchApi->getItems();
        }

        if(empty($items)) {
            $this->setFlashError($searchApi->getError());
        }

        $data = array(
            'items' => $items,
            'request' => $request,
        );

        $this->enableAddToListBtn();

        $this->render('listing', $data);
    }

    /**
     * Manages all models.
     */
    public function actionAdmin()
    {
        $model=new SearchRequests('search');
        $model->unsetAttributes();  // clear any default values
        if(isset($_GET['SearchRequests']))
            $model->attributes=$_GET['SearchRequests'];

        $this->render('admin',array(
            'model'=>$model,
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     * @return SearchRequests
     */
    public function loadModel($id)
    {
        $model=SearchRequests::model()->findByPk($id);
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
        if(isset($_POST['ajax']) && $_POST['ajax']==='search-requests-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
