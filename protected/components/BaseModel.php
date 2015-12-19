<?php
/**
 * Created by PhpStorm.
 * User: xunicorn
 * Date: 11.10.14
 * Time: 15:16
 */

abstract class BaseModel extends CActiveRecord {

    protected function afterValidate()
    {
        if(PHP_SAPI === 'cli') {
            if(count($this->getErrors()) > 0) {
                echo "Validation Error: \n";
                print_r($this->getErrors());
            }
        }

        $this->onAfterValidate(new CEvent($this));
    }

    /**
     * @param bool $table_name
     * @return int|void
     */
    public function alterTableEngine($table_name = false) {
        if(empty($table_name)) {
            $table_name = $this->tableName();
        }

        $sql = sprintf('ALTER TABLE `%s` ENGINE=\'InnoDB\'', $table_name);

        /* @var $command CDbCommand */
        $command = Yii::app()->db->createCommand($sql);

        return $command->execute();
    }

    public function getRecordById($id) {
        return static::model()->findByPk($id);
    }

    /**
     * @param $attributes
     * @return int|void
     */
    public function saveMultiple($attributes) {
        /* @var $builder CDbCommandBuilder */
        $builder=Yii::app()->db->schema->commandBuilder;

        $command = $builder->createMultipleInsertCommand($this->tableName(), $attributes);

        return $command->execute();
    }

    public function saveMultipleIgnore($attributes) {
        if(empty($attributes)) {
            return false;
        }

        $columns = array_keys($attributes[0]);

        $placeholders = array();
        $params       = array();

        foreach($attributes as $indx => $_attr) {
            $_placeholder = array();
            foreach($columns as $_col) {
                $_placeholder[] = ':' . $_col . '_' . $indx;
                $params[':' . $_col . '_' . $indx] = $_attr[$_col];
            }

            $placeholders[] = '(' . implode(',', $_placeholder) . ')';
        }

        $sql = array(
            'INSERT IGNORE ' . $this->tableName() .' (' . implode(',', $columns) . ') VALUES',
            implode(', ', $placeholders)
        );

        $sql = implode(' ', $sql);

        $command = Yii::app()->db->createCommand($sql);

        return $command->execute($params);
    }

    /**
     * @param CDbCriteria $criteria
     * @return static[]
     */
    public function getRecordsWithLimit(CDbCriteria $criteria) {
        $limit = Configs::RECORDS_SELECT_LIMIT;

        $count = static::model()->count($criteria);

        $records = array();

        if($count > $limit) {
            $parts = floor($count/$limit);

            $criteria->limit = $limit;

            for($i=0; $i<$parts; $i++) {
                $criteria->offset = $i * $limit;

                $_records = static::model()->findAll($criteria);

                if(!empty($_records)) {
                    $records = array_merge($records, $_records);
                }
            }

            if(($limit * $parts) < $count) {
                $criteria->offset = $parts * $limit;

                $_records = static::model()->findAll($criteria);

                if(!empty($_records)) {
                    $records = array_merge($records, $_records);
                }
            }
        } else {
            $records = static::model()->findAll($criteria);
        }

        return $records;
    }
} 