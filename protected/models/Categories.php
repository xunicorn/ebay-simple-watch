<?php

/**
 * This is the model class for table "categories".
 *
 * The followings are the available columns in table 'categories':
 * @property string $id
 * @property string $ebay_category_id
 * @property string $category_name
 * @property integer $ebay_category_level
 * @property integer $ebay_parent_id
 * @property integer $auto_pay
 * @property integer $best_offer
 */
class Categories extends BaseModel
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'categories';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('ebay_category_id, category_name, ebay_category_level, ebay_parent_id', 'required'),
			array('ebay_category_level, ebay_parent_id, auto_pay, best_offer', 'numerical', 'integerOnly'=>true),
			array('ebay_category_id', 'length', 'max'=>20),
			array('category_name', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, ebay_category_id, category_name, ebay_category_level, ebay_parent_id, auto_pay, best_offer', 'safe', 'on'=>'search'),
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
			'ebay_category_id' => 'Ebay Category',
			'category_name' => 'Category Name',
			'ebay_category_level' => 'Ebay Category Level',
			'ebay_parent_id' => 'Ebay Parent',
			'auto_pay' => 'Auto Pay',
			'best_offer' => 'Best Offer',
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
		$criteria->compare('ebay_category_id',$this->ebay_category_id,true);
		$criteria->compare('category_name',$this->category_name,true);
		$criteria->compare('ebay_category_level',$this->ebay_category_level);
		$criteria->compare('ebay_parent_id',$this->ebay_parent_id);
		$criteria->compare('auto_pay',$this->auto_pay);
		$criteria->compare('best_offer',$this->best_offer);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Categories the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public function saveMultiple($attributes) {

    }
}
