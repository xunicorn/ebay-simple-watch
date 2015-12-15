<?php	

    class FindItemsAdvancedClass extends EbayAPI
    {
        const ERROR_MAX_STEPS_LIMIT = 'max-steps';
        const MAX_STEPS_LIMIT = 5;

        //private $error_msg;
        public $request;

        /**
         * @var EbayItem[]
         */
        private $items;
        private $ignore_ids;
        
        private $step;
        private $steps_limit;

        private $duplicates_count = 0;

        private $duplicates_ids = array();

        private $pageNumber = 1;

        /*
            Constructor of the class
        */
        public function __construct($request_attributes, $sandbox = true)
        {
            parent::__construct($sandbox);

            $this->initErrors();
            /*
            if($this->isFalseRequest()) {
                $this->error_msg = $this->errors['search-data'];
                return;
            }
            */
            $this->initRequest($request_attributes);
        }
        
        protected function initVars() {
            $this->error_msg  = null;
            $this->items      = array();
            $this->request    = array();
            $this->ignore_ids = array();
            $this->step       = 0;
        }


        protected function initErrors() {
           $errors = array(
                'request'         => 'Concrete your request for lots searching.',
                'search-data'     => 'Fill all form fields for success search.',
               self::ERROR_MAX_STEPS_LIMIT => 'Max API call steps limit reached.',
            );

            $this->errors = array_merge($this->errors, $errors);
        }

        private function initRequest($request_attributes) {
            $end_time_from = gmdate('Y-m-d\TH:i:s.000\Z', strtotime(" + " . $request_attributes['end_time_from'] . " minutes"));

            $this->request['request']       = trim($request_attributes['request_name']);
            $this->request['end-time-from-minutes'] = $request_attributes['end_time_from'];
            $this->request['end-time-from'] = $end_time_from;
            $this->request['min-price']     = floatval($request_attributes['price_min']);
            $this->request['max-price']     = floatval($request_attributes['price_max']);
            $this->request['listing-type']  = $request_attributes['auction_type'];
            $this->request['condition']     = $request_attributes['condition'];
            $this->request['category']      = $request_attributes['ebay_category_id'];
            $this->request['keyword']       = trim($request_attributes['keyword']);
            $this->request['lots-count']    = intval($request_attributes['lots_count']);
            $this->request['ignore-list']   = $request_attributes['ignore_list'];
            $this->request['sort-method']   = isset($request_attributes['sortMethod']) ? $request_attributes['sortMethod'] : false;
            $this->request['global-ebay-id'] = $request_attributes['ebay_global_id'];
            $this->request['only-new']       = $request_attributes['only_new'];

            $this->steps_limit = ceil(intval($request_attributes['lots_count']) / 100) + self::MAX_STEPS_LIMIT;
        }


        /*
            Making Call to eBay 'makeAPICall'
        */
        public function makeAPICall()
        {       
            if(!empty($this->error_msg)) {
                throw new Exception($this->error_msg);
            }
            
            $this->step++;
            if(count($this->items) >= $this->request['lots-count'] || $this->step > $this->steps_limit) {
                $this->error_msg = $this->errors[self::ERROR_MAX_STEPS_LIMIT];
                return false;
            }
            
                                                
            $url = $this->findingURL;            

            $headers = array(
                    'Content-Type: text/xml',
                    'X-EBAY-SOA-OPERATION-NAME: findItemsAdvanced',
                    'X-EBAY-SOA-SECURITY-APPNAME: ' . $this->APP_ID,
                    'X-EBAY-SOA-GLOBAL-ID: ' . $this->request['global-ebay-id'],
            );
            
            $itemsFilters = array();
            //if(!$this->sandbox) { 157055
            //if(true) {
                $itemsFilters[] = "\t<itemFilter><name>MinPrice</name><value>". $this->request['min-price'] . '</value></itemFilter>';
                $itemsFilters[] = "\t<itemFilter><name>MaxPrice</name><value>" . $this->request['max-price'] . '</value></itemFilter>';
                $itemsFilters[] = "\t<itemFilter><name>ListingType</name><value>" . $this->request['listing-type'] . '</value></itemFilter>';
                $itemsFilters[] = "\t<itemFilter><name>HideDuplicateItems</name><value>true</value></itemFilter>";

                if($this->request['condition'] != 0) {
                    $itemsFilters[] = "\t<itemFilter><name>Condition</name><value>" . $this->request['condition'] . '</value></itemFilter>';
                }

                if(!empty($this->request['end-time-from-minutes'])) {
                    $itemsFilters[] = "\t<itemFilter><name>EndTimeFrom</name><value>" . $this->request['end-time-from'] . '</value></itemFilter>';
                }
            //}

            //<?xml version="1.0" encoding="utf-8"? >
            $body = array();
            $body[] = '<findItemsAdvancedRequest xmlns="http://www.ebay.com/marketplace/search/v1/services">';

            if($this->request['category'] != 0) {
                $body[] = "\t<categoryId>" . $this->request['category'] . '</categoryId>';
            }

            $body[] = "\t<outputSelector>PictureURLLarge</outputSelector>";

            $body[] = implode(PHP_EOL, $itemsFilters);
            $body[] = "\t<keywords>" . htmlspecialchars($this->request['keyword']) . '</keywords>';
            $body[] = "\t<paginationInput>\n\t\t<pageNumber>" . $this->pageNumber . "</pageNumber>\n\t\t<entriesPerPage>" . $this->request['lots-count'] . "</entriesPerPage>\n\t</paginationInput>";
            $body[] = '</findItemsAdvancedRequest>';

            $body = implode(PHP_EOL, $body);

            /*
            $debug = array(
                'url'     => $url,
                'headers' => $headers,
                'body'    => $body,
            );

            UserFunctions::instance()->saveDebugData($debug, 'FindItemsAdvancedClass_makeAPICall');
*/
            $this->resp = $this->send($url, $headers, $body);
            $this->xml  = simplexml_load_string($this->resp);            
            
            $this->parseXML();

            if($this->debug)
            {
                echo '<b>' . $url . '</b><pre>' . print_r($headers, true) . '</pre><pre>' . htmlspecialchars($body) . '</pre>';
                echo "<pre>"; var_dump($this->resp); echo "</pre>";
                echo "<pre>"; var_dump($this->xml); echo "</pre>";
                //echo '<pre>' . print_r($this->items, true) . '</pre>';
            }



            return $this->items;
        }

        /**
            Parsing XML from current class
        */
        protected function parseXML()
        {                    
            if($this->xml == null)
            {
                $this->error_msg = $this->errors[self::ERROR_RESPONSE_EMPTY];

                throw new Exception($this->error_msg);
            }

            if ($this->xml->ack == "Success") 
            {

                foreach($this->xml->searchResult->item as $item)
                {
                    if($this->request['ignore-list'] or $this->request['only-new']) {
                        $item_id = (string)$item->itemId;

                        if(in_array($item_id, $this->ignore_ids)) {
                            continue;
                        }
                    }
                    
                    $this->parseItem($item);
                }


                if(count($this->items) != $this->request['lots-count']) {
                    $this->pageNumber++;
                    $this->makeAPICall();
                }
            } else {
                $this->error_msg  = $this->errors[self::ERROR_RESPONSE_FAILED] . ': ' . $this->xml->error_msgs->LongMessage;

                throw new Exception($this->error_msg);
            }

        }

        private function parseItem($item) {
            $itemId = (string)$item->itemId;

            foreach($this->items as $_item) {
                if($_item->itemId == $itemId) {
                    @$this->duplicates_ids[$itemId]++;
                    $this->duplicates_count++;
                    return true;
                }
            }

            $buyItNow = -1;
            if($item->listingInfo->buyItNowAvailable == 'true') {
                $buyItNow = (double)$item->listingInfo->buyItNowPrice; //$new_item['BuyItNow'] =
            }

            $picture_url = (string)$item->pictureURLLarge;
            if(empty($picture_url)) {
                $picture_url = (string)$item->galleryURL;
            }

            $new_item = EbayItem::getSearchItem(
                isset($item->sellingStatus->bidCount) ? (int)$item->sellingStatus->bidCount : '0',
                $buyItNow,
                (string)$item->sellingStatus->currentPrice['currencyId'],
                strtotime($item->listingInfo->startTime),
                strtotime($item->listingInfo->endTime),
                $itemId,
                (string)$item->viewItemURL,
                $picture_url,
                (double)$item->sellingStatus->currentPrice,
                $this->request['request'],
                $this->getShipping($item->shippingInfo->shippingServiceCost, $item->shippingInfo->shipToLocations),
                $this->parseTimeLeft($item->sellingStatus->timeLeft),
                (string)$item->title,
                (string)$item->listingInfo->listingType
            );
                                                          
            $this->items[] = $new_item;            
            
            return TRUE;
        }
                
        protected function getShipping($shipping, $loc_arr)
        {
//            $shipping_arr = array(
//                'cost' => $shipping,
//                'locations' => $loc_arr
//            );
            
            $shipping_str = '';
            
            if($shipping != "") {
                $shipping_str = "$" . $shipping . " ";
            }

            foreach($loc_arr as $location) {
                $shipping_str .= $location . " ";
            }
            $shipping_str = trim($shipping_str);

            return $shipping_str;
        }


        /**
         * @return EbayItem[]
         */
        public function getItems() {
            return $this->items;
        }
        
        public function getRequest() {
            return $this->request;
        }
        
        public function includeIgnoreList() {
            return isset($this->request['ignore-list']) ? $this->request['ignore-list'] : false;
        }

        public function setIgnoreIds($ignore_ids) {
            if(!is_array($ignore_ids)) {
                $ignore_ids = (array)$ignore_ids;
            }

            $this->ignore_ids = $ignore_ids;
        }
    }
?>