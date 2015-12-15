<?php

/**
 * This is the model class for table "listing_items".
 *
 * The followings are the available columns in table 'listing_items':
 * @property string $id
 * @property string $ebay_id
 * @property string $title
 * @property string $url_picture
 * @property string $url_item
 * @property integer $buy_it_now
 * @property string $date_start
 * @property string $date_end
 * @property string $currency
 * @property ListingNames[] $listings
 */
class ListingItems extends BaseModel
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'listing_items';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('ebay_id, title', 'required'),
			array('buy_it_now', 'numerical', 'integerOnly'=>true),
			array('ebay_id', 'length', 'max'=>20),
			array('title, url_picture, url_item', 'length', 'max'=>255),
			array('date_start, date_end', 'length', 'max'=>10),
			array('currency', 'length', 'max'=>50),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, ebay_id, title, url_picture, url_item, buy_it_now, date_start, date_end', 'safe', 'on'=>'search'),
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
            'listings' => array(self::MANY_MANY, 'ListingNames', ListingNamesItems::model()->tableName() . '(listing_item_id, listing_name_id)'),
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
			'date_start' => 'Start Date',
			'date_end' => 'End Date',
			'currency' => 'Currency',
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
		$criteria->compare('ebay_id',$this->ebay_id,true);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('url_picture',$this->url_picture,true);
		$criteria->compare('url_item',$this->url_item,true);
		$criteria->compare('buy_it_now',$this->buy_it_now);
		$criteria->compare('date_start',$this->date_start);
		$criteria->compare('date_end',$this->date_end);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ListingItems the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public function saveMultiple($attributes) {
        $names = array(
            '`ebay_id`',
            '`title`',
            '`url_picture`',
            '`url_item`',
            '`buy_it_now`',
            '`date_start`',
            '`date_end`',
            '`currency`',
        );

        $updates = array(
            '`date_end`=VALUES(`date_end`)',
            '`url_picture`=VALUES(`url_picture`)',
        );

        $placeholders = array();
        $params       = array();

        foreach($attributes as $i => $_attr) {
            $placeholders[] = sprintf('(:ebay_id_%1$d, :title_%1$d, :url_picture_%1$d, :url_item_%1$d, :buy_it_now_%1$d, :date_start_%1$d, :date_end_%1$d, :currency_%1$d)', $i);

            $params[':ebay_id_' .       $i] = $_attr['ebay_id'];
            $params[':title_' .         $i] = $_attr['title'];
            $params[':url_picture_' .   $i] = $_attr['url_picture'];
            $params[':url_item_' .      $i] = $_attr['url_item'];
            $params[':buy_it_now_' .    $i] = $_attr['buy_it_now'];
            $params[':date_start_' .    $i] = $_attr['date_start'];
            $params[':date_end_' .      $i] = $_attr['date_end'];
            $params[':currency_' .      $i] = $_attr['currency'];
        }

        $sql = array(
            'INSERT ' . $this->tableName(),
            '(' . implode(',', $names) . ') VALUES',
            implode(', ', $placeholders),
            'ON DUPLICATE KEY UPDATE ' . implode(',', $updates),
        );

        $sql = implode(' ', $sql);

        $command = Yii::app()->db->createCommand($sql);

        return $command->execute($params);
    }

    public function getIdsByEbayIds($ebay_ids) {
        if(empty($ebay_ids)) {
            return false;
        }

        if(!is_array($ebay_ids)) {
            $ebay_ids = (array)$ebay_ids;
        }

        $ids = array();

        $sql = 'SELECT id FROM ' . $this->tableName() . ' WHERE ebay_id IN(' . implode(',', $ebay_ids) . ')';

        $command = Yii::app()->db->createCommand($sql);
        $command->setFetchMode(PDO::FETCH_OBJ);
        $results = $command->queryAll();

        foreach($results as $_res) {
            $ids[] = $_res->id;
        }

        return $ids;
    }

    public function getItemsFromList($list_id) {
        $criteria = new CDbCriteria();
        $criteria->with = array(
            'listings' => array(
                'select' => false,
                'condition' => 'listings.id=:list_id',
                'params'    => array(':list_id' => $list_id),
            ),
        );

        return ListingItems::model()->findAll($criteria);
    }

    public function deleteItemsFromList($list_id, $ebay_ids) {
        if(empty($list_id) or empty($ebay_ids)) {
            return false;
        }

        if(!is_array($ebay_ids)) {
            $ebay_ids = (array)$ebay_ids;
        }

        $sql = array();
        $sql[] = 'DELETE t.*';
        $sql[] = 'FROM ' . ListingNamesItems::model()->tableName() . ' t';
        $sql[] = 'JOIN ' . $this->tableName() . ' itm ON itm.id=t.listing_item_id';
        $sql[] = 'WHERE itm.ebay_id IN (' . implode(',', $ebay_ids) . ') AND t.listing_name_id=:list_id';

        $params = array(
            ':list_id'    => $list_id,
        );

        $sql = implode(' ', $sql);

        /* @var $command CDbCommand */
        $command = Yii::app()->db->createCommand($sql);
        return $command->execute($params);
    }
}
