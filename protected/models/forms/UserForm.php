<?php
/**
 * Created by PhpStorm.
 * User: Администратор
 * Date: 24.11.2015
 * Time: 16:13
 */

class UserForm extends CFormModel {

    public $username;
    public $password;
    public $password_verify;
    public $email;

    public $verified;

    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        return array(

            array('username, password, password_verify, email', 'required', 'on' => 'create, create_admin'),
            array('password, password_verify', 'required', 'on' => 'change_password'),
            array('email', 'email', 'message' => 'Email is not valid'),
            array('email', 'unique', 'attributeName' => 'email', 'className' => 'Users', 'allowEmpty' => false, 'message' => 'User with such email already exists'),
            array('username', 'match', 'pattern' => '/.*admin.*/i', 'not' => true, 'message' => 'You could not get this username', 'on' => 'create'),
            array('username', 'match', 'pattern' => '/^([a-zA-Z0-9_-])+$/', 'message' => 'You could not get this username'),
            array('username', 'unique', 'attributeName' => 'username', 'className' => 'Users', 'allowEmpty' => false, 'message' => 'User with such username already exists'),
            array('password, password_verify', 'length', 'min' => 6, 'max' => 60),
            array('username, email', 'length', 'max' => 255),
            array('password_verify', 'compare', 'compareAttribute' => 'password', 'message' => 'Password & Password Repeat must match'),
            array('verified', 'boolean'),
        );
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array(
            'username' => 'Username',
            'password' => 'Password',
            'password_verify' => 'Password Repeat',
            'email'    => 'Email',
            'verified' => 'Verified',
        );
    }

    /**
     * Logs in the user using the given username and password in the model.
     * @return boolean whether login is successful
     */
    public function register()
    {
        $this->formAfterCheck();

        if(!$this->hasErrors()) {
            $user = new Users('create');
            $user->username = $this->username;
            $user->email    = $this->email;
            $user->password = CPasswordHelper::hashPassword($this->password);

            if($user->save()) {
                $url_maintenance = $user->getMaintenanceUrl();

                ListingNames::model()->getUserIgnoreList($user->id);

                MailHelper::sendUserCredentials($this->username, $this->email, $this->password);
                MailHelper::sendRegisterConfirmMail( $user->username, $user->email, $url_maintenance);

                return true;
            } else {
                $this->addErrors($user->getErrors());
            }
        }

        return false;
    }

    public function registerByAdmin() {
        $this->formAfterCheck();

        if(!$this->hasErrors()) {
            $user = new Users('create');
            $user->username = $this->username;
            $user->email    = $this->email;
            $user->password = CPasswordHelper::hashPassword($this->password);
            $user->email_verified = intval($this->verified);

            if($user->save()) {
                MailHelper::sendUserCredentials($this->username, $this->email, $this->password);

                if(!$this->verified) {
                    $url_maintenance = $user->getMaintenanceUrl();
                    $user->save();

                    MailHelper::sendRegisterConfirmMail( $user->username, $user->email, $url_maintenance);
                }

                ListingNames::model()->getUserIgnoreList($user->id);

                return true;
            }else {
                $this->addErrors($user->getErrors());
            }
        }

        return false;
    }

    protected function formAfterCheck() {
        $record = Users::model()->getUserByUsername($this->username);

        if(!empty($record)) {
            $this->addError('username', 'Such username already exists');
        }


        if(stristr($this->username, 'admin')) {
            $this->addError('username', 'You could not get this username');
        }


        $record = Users::model()->getUserByEmail($this->email);
        if(!empty($record)) {
            $this->addError('email', 'User with such email already exists');
        }


        if($this->password !== $this->password_verify) {
            $this->addError('password', 'Not matched');
            $this->addError('password_verify', 'Not matched');
        }
    }
} 