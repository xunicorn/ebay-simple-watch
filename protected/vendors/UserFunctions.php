<?php

    class UserFunctions {

        private static $_instance = null;


        private function __construct() {}
        public function __destruct() {
            //$this->_saveLogResponses();
        }

        public static function instance() {
            if(empty(self::$_instance)) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        public function saveDebugData($data, $file_suffix = false, $append = false) {

            $data = sprintf('*****DEBUG DATA***** | Date: %s %s %s %s', date('Y-m-d H:i:s'), PHP_EOL, print_r($data, true), PHP_EOL);

            $filename = 'debug_data' . (!empty($file_suffix) ? '_' . $file_suffix : '') . '.txt';

            $filepath = Yii::getPathOfAlias('application.runtime') . '/debug/';

            if(!file_exists($filepath)) {
                mkdir($filepath, 0777);
            }

            $filepath .= $filename;

            if($append) {
                file_put_contents($filepath, $data, FILE_APPEND);
            } else{
                file_put_contents($filepath, $data);
            }
        }


    }
