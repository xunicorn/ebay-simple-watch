<?php

/**
 * This is the model class for table "search_items".
 *
 * The followings are the available columns in table 'search_items':
 * @property integer $id
 * @property string $ebay_id
 * @property string $title
 * @property string $url_picture
 * @property string $url_item
 * @property integer $buy_it_now
 * @property integer $date_of_added
 */
class SearchItems extends BaseModel
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'search_items';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('title, url_picture, url_item, date_of_added', 'required'),
			array('buy_it_now, date_of_added', 'numerical', 'integerOnly'=>true),
			array('ebay_id', 'length', 'max'=>20),
			array('title, url_picture, url_item', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, ebay_id, title, url_picture, url_item, buy_it_now, date_of_added', 'safe', 'on'=>'search'),
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
			'ebay_id' => 'Ebay',
			'title' => 'Title',
			'url_picture' => 'Url Picture',
			'url_item' => 'Url Item',
			'buy_it_now' => 'Buy It Now',
			'date_of_added' => 'Date Of Added',
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

		$criteria->compare('id',$this->id);
		$criteria->compare('ebay_id',$this->ebay_id,true);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('url_picture',$this->url_picture,true);
		$criteria->compare('url_item',$this->url_item,true);
		$criteria->compare('buy_it_now',$this->buy_it_now);
		$criteria->compare('date_of_added',$this->date_of_added);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return SearchItems the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * @param $ebay_ids
     * @return array
     */
    public function getItemsIdByEbayIds($ebay_ids) {
        $criteria = new CDbCriteria();

        $criteria->select = 'id';
        $criteria->addInCondition('ebay_id', $ebay_ids);

        $records = $this->getRecordsWithLimit($criteria);

        $ids = CHtml::listData($records, 'id', 'id');

        return $ids;
    }
}
