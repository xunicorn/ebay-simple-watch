<?php
/**
 * Created by PhpStorm.
 * User: xunicorn
 * Date: 19.11.2014
 * Time: 1:55
 */

class WebUser extends CWebUser {

    const ADMIN_ID = 1;
    const DEFAULT_USER_ID = 0;

    protected static $model;

    public function getUsername() {
        if($this->id === 0) {
            return 'admin';
        }

        return $this->getModel()->username;
    }

    /**
     * @return Users
     */
    public static function getModel() {
        $model = self::$model;
        if(empty($model)) {
            $model = Users::model()->getUserById(Yii::app()->user->id);

            self::$model = $model;
        }


        return self::$model;
    }

    /**
     * @return bool
     */
    public static function isAdmin() {
        return Yii::app()->user->id === WebUser::ADMIN_ID;
    }

    /**
     * @return bool
     */
    public static function isGuest() {
        return Yii::app()->user->isGuest;
    }

    /**
     * @param bool $use_default_id
     * @return mixed
     */
    public static function Id($use_default_id = false) {
        if(self::isGuest()) {
            if($use_default_id) {
                return self::DEFAULT_USER_ID;
            }
        }

        return Yii::app()->user->id;
    }

    public static function getAdminId() {
        return WebUser::ADMIN_ID;
    }

} 