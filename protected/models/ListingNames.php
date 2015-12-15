<?php

/**
 * This is the model class for table "listing_names".
 *
 * The followings are the available columns in table 'listing_names':
 * @property string $id
 * @property string $user_id
 * @property string $image_url
 * @property string $name
 * @property string $date_create
 * @property string $date_update
 * @property string $ignored
 * @property ListingItems[] $items
 * @property int $items_count
 * @property Users $user
 */
class ListingNames extends BaseModel
{
    public $filter_user;
    public $filter_items_count;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'listing_names';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, name', 'required'),
			array('user_id, date_create, date_update, ignored', 'length', 'max'=>10),
			array('name, image_url', 'length', 'max'=>255),
			array(
                'name', 'filter',
                'filter'=>array($obj=new CHtmlPurifier(),'purify')),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, user_id, name, image_url, date_create, date_update, ignored, filter_user, filter_items_count', 'safe', 'on'=>'search'),
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
            'items'       => array(self::MANY_MANY, 'ListingItems', ListingNamesItems::model()->tableName() . '(listing_name_id, listing_item_id)'),
            'items_count' => array(self::STAT, 'ListingItems', ListingNamesItems::model()->tableName() . '(listing_name_id, listing_item_id)'),
            'user'        => array(self::BELONGS_TO, 'Users', 'user_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'          => 'ID',
			'user_id'     => 'User',
			'filter_user' => 'User',
			'name'        => 'Name',
			'image_url'   => 'Image URL',
			'date_create' => 'Create Date',
			'date_update' => 'Update Date',
			'ignored'     => 'Ignored',
            'filter_items_count' => 'Items Count',
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
		$criteria->compare('user_id',$this->user_id,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('date_create',$this->date_create,true);
		$criteria->compare('ignored',$this->ignored,true);

        if(!WebUser::isAdmin()) {
            $criteria->compare('user_id', WebUser::Id());
        }

        $criteria->order = 'user_id, ignored DESC, name';

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ListingNames the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * @param $user_id
     * @return ListingNames
     */
    public function getUserIgnoreList($user_id) {
        $list = ListingNames::model()->find('user_id=:user_id AND ignored=1', array(':user_id' => $user_id));

        if(empty($list)) {
            $list = new ListingNames();
            $list->date_create = time();
            $list->date_update = time();
            $list->ignored = 1;
            $list->user_id = $user_id;
            $list->name    = 'ignore-list';

            $list->save();
        }

        return $list;
    }

    /**
     * @param $user_id
     * @return ListingNames[]
     */
    public function getUserLists($user_id) {
        $criteria = new CDbCriteria();
        $criteria->compare('user_id', $user_id);
        $criteria->order = 'ignored DESC';

        return ListingNames::model()->findAll($criteria);// AND ignored=0
    }

    /**
     * @return Users
     */
    public function getUser() {
        if(empty($this->user)) {
            $this->user = new Users();
        }

        return $this->user;
    }

    public function deleteList($list_id) {
        $list = ListingNames::model()->findByPk($list_id);

        if(!empty($list)) {
            $this->truncateList($list_id);
            $list->delete();
        }
    }

    public function truncateList($list_id) {
        return ListingNamesItems::model()->deleteAll('listing_name_id=:list_id', array( ':list_id' => $list_id));
    }


}
