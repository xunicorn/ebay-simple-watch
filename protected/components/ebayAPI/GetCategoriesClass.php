<?php

	class GetCategoriesClass extends EbayAPI {

        public $categories;

        public $siteID = 0;
        public $levelLimit = 2;

        protected function initVars()
        {
            // TODO: Implement initVars() method.
        }

        protected function initErrors()
        {
            // TODO: Implement initErrors() method.
        }


        /*
                Getting categories from the eBay site
        */
        public function makeAPICall()
        {
            $url = $this->tradingURL;

            $user_token = '';
            if(isset(Yii::app()->session['token'])) {
                $user_token = Yii::app()->session['token'];
            }
            else {
                $user_token = $this->USER_TOKEN;
            }

            $headers = array(
                    'Content-Type: text/xml',
                    'X-EBAY-API-COMPATIBILITY-LEVEL:' . $this->COMPATIBILITY_LEVEL,
                    'X-EBAY-API-DEV-NAME:'  . $this->DEV_ID,
                    'X-EBAY-API-APP-NAME:'  . $this->APP_ID,
                    'X-EBAY-API-CERT-NAME:' . $this->CERT_ID,
                    'X-EBAY-API-SITEID: ' . $this->siteID,
                    'X-EBAY-API-CALL-NAME:GetCategories',
            );

            $body = '<?xml version="1.0" encoding="utf-8"?>'
                    .	'<GetCategoriesRequest xmlns="urn:ebay:apis:eBLBaseComponents">'
                    .	'<RequesterCredentials>'
                    .		'<eBayAuthToken>' . $user_token . '</eBayAuthToken>'
                    .	'</RequesterCredentials>'
                    .	'<CategorySiteID>' . $this->siteID . '</CategorySiteID>'
                    .	'<DetailLevel>ReturnAll</DetailLevel>'
                    .	'<LevelLimit>' . $this->levelLimit . '</LevelLimit>'
                    .	'</GetCategoriesRequest>'
            ;

            $this->resp = $this->send($url, $headers, $body);
            $this->xml  = simplexml_load_string($this->resp);

            if($this->debug) {
                echo '<pre>'; var_dump($this->resp); echo '</pre>';
                echo '<pre>'; var_dump($this->xml); echo '</pre>';
            }

            if($this->xml->Ack == "Success") {
                $this->categories = array();
                foreach( $this->xml->CategoryArray->Category as $cat) {
                    $this->categories[] = $cat;
                }

                if(isset($this->xml->HardExpirationWarning)) {
                    $message = 'This user token will be expired on ' . $this->xml->HardExpirationWarning;

                    throw new CException($message);
                }

            } else {
                $this->categories = null;

                $message = $this->xml->Errors->LongMessage . ' [Errno: ' . $this->xml->Errors->ErrorCode . ']';

                throw new Exception($message);
            }

            return $this->categories;
        }

        public static function inst() {
            return new GetCategoriesClass();
        }
	}

?>