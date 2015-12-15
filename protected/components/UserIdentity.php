<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
    const ERROR_EMAIL_UNVERIFIED = 37;

    protected $_id;

	/**
	 * Authenticates a user.
	 * The example implementation makes sure if the username and password
	 * are both 'demo'.
	 * In practical applications, this should be changed to authenticate
	 * against some persistent user identity storage (e.g. database).
	 * @return boolean whether authentication succeeds.
	 */
    public function authenticate()
    {
        if(!empty(Yii::app()->params['reserveLogin'])) {
            return $this->reserveLogin();
        }

        $user = Users::model()->getUserByUsername($this->username);

        if(empty($user)) {
            $this->errorCode=self::ERROR_USERNAME_INVALID;
        } elseif(empty($user->email_verified)) {
            $this->errorCode=self::ERROR_EMAIL_UNVERIFIED;
        }elseif(!CPasswordHelper::verifyPassword($this->password, $user->password)) {
            $this->errorCode=self::ERROR_PASSWORD_INVALID;
        } else {
            $this->errorCode=self::ERROR_NONE;
            $this->_id = intval($user->id);
        }

        return !$this->errorCode;
    }

    protected function reserveLogin() {
        $users=array(
            'admin'    => 'iddqd3311',
        );

        if(!isset($users[$this->username]))
            $this->errorCode=self::ERROR_USERNAME_INVALID;
        elseif($users[$this->username]!==$this->password)
            $this->errorCode=self::ERROR_PASSWORD_INVALID;
        else
            $this->errorCode=self::ERROR_NONE;

        if($this->errorCode == self::ERROR_NONE) {
            $this->_id = 1;
        }

        return !$this->errorCode;
    }

    public function getId() {
        return $this->_id;
    }
}