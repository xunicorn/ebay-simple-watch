<?php

/**
 * This is the model class for table "users".
 *
 * The followings are the available columns in table 'users':
 * @property string $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property integer $date_last_visit
 * @property integer $email_verified
 * @property string $url_maintenance
 */
class Users extends BaseModel
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'users';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('email, password, username', 'required'),
			array('date_last_visit, email_verified', 'numerical', 'integerOnly'=>true),
			array('email, username, url_maintenance', 'length', 'max'=>255),
			array('email', 'email'),
            array('email', 'email', 'message' => 'Email is not valid'),
            array('email', 'unique', 'attributeName' => 'email', 'className' => 'Users', 'allowEmpty' => false, 'message' => 'User with such email already exists', 'on' => 'create'),
            array('username', 'match', 'pattern' => '/.*admin.*/i', 'not' => true, 'message' => 'You could not get this username', 'on' => 'create'),
            array('username', 'match', 'pattern' => '/^([a-zA-Z0-9_-])+$/', 'message' => 'You could not get this username', 'on' => 'create'),
            array('username', 'unique', 'attributeName' => 'username', 'className' => 'Users', 'allowEmpty' => false, 'message' => 'User with such username already exists', 'on' => 'create'),
			array('password', 'length', 'max'=>60),
            array('email, username, url_maintenance, password', 'filter', 'filter'=>array($obj=new CHtmlPurifier(),'purify')),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, email, username, password, date_last_visit, email_verified', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'email' => 'Email',
            'username' => 'Username',
			'password' => 'Password',
			'date_last_visit' => 'Last Visit',
			'email_verified' => 'Verified',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('password',$this->password,true);
		$criteria->compare('date_last_visit',$this->date_last_visit);
		$criteria->compare('email_verified',$this->email_verified);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Users the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public function createAdmin() {
        $attributes = array(
            'username' => 'admin',
            'email'    => 'test@mail.kom',
            'password' => CPasswordHelper::hashPassword('iddqd3311'),
            'email_verified' => 1,
        );

        $user = new Users();
        $user->attributes = $attributes;
        $user->save();
    }

    /**
     * @param $email
     * @return Users
     */
    public function getUserByEmail($email) {
        return Users::model()->find('email=:email', array(':email' => $email));
    }

    /**
     * @param $username
     * @return Users
     */
    public function getUserByUsername($username) {
        return Users::model()->find('username=:username', array(':username' => $username));
    }

    /**
     * @param $id
     * @return Users
     */
    public function getUserById($id) {
        return Users::model()->findByPk($id);
    }

    public function createUser($attributes) {
        $user = new Users();
        $user->attributes = $attributes;

        return $user->save();
    }

    public function getMaintenanceUrl() {
        if(!empty($this->email)) {
            $this->url_maintenance = md5($this->email . time());
            $this->save();

            return $this->url_maintenance;
        }

        return md5(time());
    }

    /**
     * @param $url
     * @return Users
     */
    public function getUserByMaintenanceUrl($url) {
        return Users::model()->find('url_maintenance=:url', array(':url' => $url));
    }
}
