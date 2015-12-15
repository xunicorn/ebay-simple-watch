<?php

/**
 * This is the model class for table "configs".
 *
 * The followings are the available columns in table 'configs':
 * @property string $id
 * @property string $name
 * @property string $key
 * @property string $value
 */
class Configs extends BaseModel {

    const RECORDS_SELECT_LIMIT = 1000;

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'configs';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name, key', 'required'),
            array('name, key', 'length', 'max' => 255),
            array('value', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, name, key, value, type', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array();
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id'    => 'ID',
            'name'  => 'Name',
            'key'   => 'Key',
            'value' => 'Value',
            'type'  => 'Type',
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
    public function search($type) {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id, true);
        $criteria->compare('t.name', $this->name, true);
        $criteria->compare('t.key', $this->key, true);
        $criteria->compare('t.value', $this->value, true);

        $criteria->compare('t.type', $type);

        $criteria->order = 't.name';

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => 100,
            ),
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Configs the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @param $key
     * @return null|mixed
     */
    public function getValue($key) {
        $record = $this->getConfigByKey($key);

        if (!empty($record)) {
            return $record->value;
        }

        return null;
    }

    /**
     * @param $value
     * @return array|null
     */
    public function getKeys($value) {
        $records = $this->getConfigsByValue($value);

        if (!empty($records)) {
            $results = array();

            foreach ($records as $record) {
                $results[] = $record->key;
            }

            return $results;
        }

        return null;
    }

    /**
     * @param $key
     * @param $value
     * @return bool
     */
    public function setValue($key, $value) {
        $record = $this->getConfigByKey($key);

        if (!empty($record)) {
            $record->value = $value;

            return $record->save();
        }

        return false;
    }

    /**
     * @param $id
     * @return Configs|null
     */
    public function getConfigById($id) {
        return Configs::model()->findByPk($id);
    }

    /**
     * @param $key
     * @return Configs|null
     */
    public function getConfigByKey($key) {
        return Configs::model()->find('t.key=:key', array(':key' => $key));
    }

    /**
     * @param $value
     * @return array|null
     */
    public function getConfigsByValue($value) {
        return Configs::model()->findAll('value=:value', array(':value' => $value));
    }

    /**
     * @param $key
     * @return bool
     */
    public function removeByKey($key) {
        return Configs::model()->delete('t.key=:key', array(':key' => $key));
    }

    /**
     * NOTE! Removes all records that match value
     * @param $value
     * @return bool
     */
    public function removeByValue($value) {
        return Configs::model()->deleteAll('value=:value', array(':value' => $value));
    }

    /**
     * @param $id
     * @return bool
     */
    public function removeConfig($id) {
        return Configs::model()->deleteByPk($id);
    }

}
