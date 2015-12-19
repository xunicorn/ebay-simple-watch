<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{
    const FLASH_TYPE_SUCCESS = 'success';
    const FLASH_TYPE_ERROR   = 'error';
    const FLASH_TYPE_WARNING = 'warning';
    const FLASH_TYPE_INFO    = 'info';

    /**
     * @var GetCategoriesClass
     */
    protected $categories;

    /**
     * @var FindItemsAdvancedClass
     */
    protected $search;

    public $main_menu = array();

    public $actions_menu = array();
    public $requests     = array();
    public $listings     = array();

    public $add_to_list_btn = 0;

    public $url_referrer;

    public $sidebar = '/widgets/sidebar';

    public $assets;

    /**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout='//layouts/column1';
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu=array();
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs=array();

    public function __construct($id, $module = null) {
        //$this->initAPI();

        parent::__construct($id, $module);

        $this->initMenu();

        $this->initAssets();

        $this->url_referrer = Yii::app()->request->urlReferrer;

        $this->requests = SearchRequests::model()->getUserRequests(WebUser::Id(true));
        $this->listings = ListingNames::model()->getUserLists(WebUser::Id());

        if(!WebUser::isGuest()) {
            $user = WebUser::getModel();
            $user->date_last_visit = time();
            $user->save();
        }
    }

    #region Init
    private function initMenu() {
        $this->main_menu = array(
            array('label'=>'Home',     'url'=>array('/site/index')),
            array('label'=>'Search',   'url'=>array('/search/index')),
            array('label'=>'Lists',    'url'=>array('/listing/index'),    'visible' => !WebUser::isGuest()),
            array('label'=>'|',        'url'=>'#',                        'visible' => !WebUser::isGuest()),
            array('label'=>'Users',    'url'=>array('/users/admin'),      'visible' => WebUser::isAdmin()),
            array('label'=>'Profile',  'url'=>array('/users/update',
                                                'id' => WebUser::Id()),   'visible' => !WebUser::isGuest()),
            array('label'=>'|',        'url'=>'#',                        'visible' => !WebUser::isGuest()),
            array('label'=>'Login',    'url'=>array('/site/login'),       'visible' => WebUser::isGuest()),
            array('label'=>'Register', 'url'=>array('/site/register'),  'visible' => WebUser::isGuest()),
            array('label'=>'Logout ('.Yii::app()->user->name.')', 'url'=>array('/site/logout'), 'visible'=>!WebUser::isGuest())
        );
    }

    protected function initAssets() {
        //parent::initAssets();
/*
        Yii::app()->clientScript->registerCssFile(
            Yii::app()->assetManager->publish(Yii::getPathOfAlias('webroot.media.css') . '/ebay.css'));
*/
        $this->assets['img']['arrow-top'] = Yii::app()->assetManager->publish(Yii::getPathOfAlias('webroot.media.img') . '/arrow-top.png');
        $this->assets['img']['no-image']  = Yii::app()->assetManager->publish(Yii::getPathOfAlias('webroot.media.img') . '/no_image.png');
        $this->assets['img']['delete']    = Yii::app()->assetManager->publish(Yii::getPathOfAlias('webroot.media.img') . '/delete.png');
    }

    protected function initJS() {
        Yii::app()->clientScript->registerScript(
            'date-compatibility',
            'if (!Date.now) {
                Date.now = function() { return new Date().getTime(); }
            }'
        );
    }
    #endregion

    #region setFlash
    public function setFlash($type, $message) {
        $user = Yii::app()->getComponent('user');

        $user->setFlash(
            $type, $message
        );
    }

    public function setFlashSuccess($message) {
        $this->setFlash(Controller::FLASH_TYPE_SUCCESS, $message);
    }

    public function setFlashError($message) {
        $this->setFlash(Controller::FLASH_TYPE_ERROR, $message);
    }

    public function setFlashWarning($message) {
        $this->setFlash(Controller::FLASH_TYPE_WARNING, $message);
    }

    public function setFlashInfo($message) {
        $this->setFlash(Controller::FLASH_TYPE_INFO, $message);
    }
    #endregion

    #region addToListBtn visibility
    public function enableAddToListBtn() {
        $this->add_to_list_btn = 1;
    }

    public function disableAddToListBtn() {
        $this->add_to_list_btn = 0;
    }
    #endregion

    public function registerXEditable() {
        Bootstrap::getBooster()->registerPackage('x-editable');
    }
}