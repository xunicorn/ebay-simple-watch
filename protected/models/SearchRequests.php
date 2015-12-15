<?php

/**
 * This is the model class for table "search_requests".
 *
 * The followings are the available columns in table 'search_requests':
 * @property string $id
 * @property string $user_id
 * @property string $date_update
 * @property string $request_name
 * @property string $end_time_from
 * @property string $price_min
 * @property string $price_max
 * @property string $auction_type_id
 * @property integer $condition
 * @property integer $ebay_category_id
 * @property string $keyword
 * @property string $lots_count
 * @property integer $ignore_list
 * @property integer $ebay_global_id
 * @property integer $only_new
 * @property Users $user
 * @property SearchItems[] $items
 */
class SearchRequests extends BaseModel
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'search_requests';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, date_update, request_name, keyword, lots_count, price_min, price_max, end_time_from, ebay_global_id', 'required'),
			array('condition, ebay_category_id, ignore_list, auction_type_id, only_new', 'numerical', 'integerOnly'=>true),
			array('user_id, date_update, end_time_from, price_min, price_max, lots_count', 'length', 'max'=>10),
            array('price_max', 'compare', 'compareAttribute' => 'price_min', 'operator' => '>'),
			array('request_name,  keyword', 'length', 'max'=>255),
			array('ebay_global_id', 'length', 'max'=>16),
            array(
                'request_name,  keyword, end_time_from, price_min, price_max, lots_count, ebay_global_id',
                'filter', 'filter'=>array($obj=new CHtmlPurifier(),'purify')),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array(
                'id,
                user_id,
                date_update,
                request_name,
                end_time_from,
                price_min,
                price_max,
                auction_type_id,
                condition,
                ebay_category_id,
                keyword,
                lots_count,
                ignore_list,
                ebay_global_id,
                only_new',
                'safe',
                'on'=>'search'
            ),
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
            'user'  => array(self::BELONGS_TO, 'Users', 'user_id'),
            'items' => array(self::MANY_MANY, 'SearchItems', SearchRequestsItems::model()->tableName() . '(search_request_id, search_item_id)'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'user_id' => 'User',
			'date_update' => 'Date Update',
			'request_name' => 'Request',
			'end_time_from' => 'End Time From',
			'price_min' => 'Min Price',
			'price_max' => 'Max Price',
			'auction_type_id' => 'Auction Type',
			'condition' => 'Condition',
			'ebay_category_id' => 'Category',
			'keyword' => 'Keyword',
			'lots_count' => 'Lots Count',
			'ignore_list' => 'Ignore List',
            'ebay_global_id' => 'Global eBay ID',
            'only_new' => 'Only New',
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
		$criteria->compare('date_update',$this->date_update,true);
		$criteria->compare('request_name',$this->request_name,true);
		$criteria->compare('end_time_from',$this->end_time_from,true);
		$criteria->compare('price_min',$this->price_min,true);
		$criteria->compare('price_max',$this->price_max,true);
		$criteria->compare('auction_type_id',$this->auction_type_id);
		$criteria->compare('condition',$this->condition);
		$criteria->compare('ebay_category_id',$this->ebay_category_id);
		$criteria->compare('keyword',$this->keyword,true);
		$criteria->compare('lots_count',$this->lots_count,true);
		$criteria->compare('ignore_list',$this->ignore_list);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return SearchRequests the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public function getAuctionTypes() {
        return include('static/ebay_auction_types.php');
    }

    public function getGlobalEbayIds() {
        return include('static/ebay_global_ids.php');
    }

    public function getSiteEbayIds() {
        return include('static/ebay_site_ids.php');
    }

    public function getItemsConditions() {
        return include('static/ebay_conditions.php');
    }

    public function getSiteEbayId($global_id) {
        $site_ids = $this->getSiteEbayIds();

        return isset($site_ids[$global_id]) ? $site_ids[$global_id] : 0;
    }

    public function getAuctionType($type_id) {
        $types = $this->getAuctionTypes();

        return isset($types[$type_id]) ? $types[$type_id] : '';
    }

    public function getGlobalEbayId($id) {
        $ids = $this->getGlobalEbayIds();

        return isset($ids[$id]) ? $ids[$id] : '';
    }

    /**
     * @param $user_id
     * @return SearchRequests[]
     */
    public function getUserRequests($user_id) {
        $criteria = new CDbCriteria();
        $criteria->compare('user_id', $user_id);
        $criteria->order = 'date_update DESC';

        return SearchRequests::model()->findAll($criteria);
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

    public function addItemsToRequest($request_id, $ebay_ids) {
        $date = time();

        $attributes = array();

        $items_ids  = SearchItems::model()->getItemsIdByEbayIds($ebay_ids);

        foreach($items_ids as $_id) {
            $attributes[] = array(
                'search_item_id'    => $_id,
                'search_request_id' => $request_id,
                'date_update' => $date,
            );
        }

        SearchRequestsItems::model()->saveMultipleIgnore($attributes);
    }
}
