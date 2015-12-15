<?php

    class GetMultipleItemsClass extends EbayAPI
    {
        const ERROR_IDS_EMPTY = 'ids-empty';

        private $sortMethod;
        private $xml_data_arr;
        private $items_iterator;

        /**
         * @var []
         */
        private $itemsIds;

        /**
         * @var EbayItem []
         */
        private $items;

        protected function initVars()
        {
            $this->error_msg = '';
            $this->sortMethod = '';
            $this->xml_data_arr = array(array());
            unset($this->xml_data_arr[0]);
            $this->items_iterator = 0;
        }

        protected function initErrors()
        {
            $errors = array(
                'ids-empty' => 'Set eBay IDs for getting items'
            );

            $this->errors = array_merge($this->errors, $errors);
        }

        /**
         * @param mixed $itemsIds
         */
        public function setItemsIds($itemsIds)
        {
            $this->itemsIds = $itemsIds;
        }


        /**
         * @return EbayItem[]
         * @throws Exception
         */
        public function makeAPICall()
        {
            if(empty($this->itemsIds)) {
                $this->error_msg = $this->errors[self::ERROR_IDS_EMPTY];

                throw new Exception($this->error_msg);
            }

            $url = $this->shoppingURL;

            $items_ids_formatted = array();

            foreach($this->itemsIds as $_id) {
                $items_ids_formatted[] = "<ItemID>$_id</ItemID>";
            }

            $items_ids_chunk = array_chunk($items_ids_formatted, 20);

            foreach($items_ids_chunk as $items_ids) {

                $items_ids_str = implode("", $items_ids);

                $headers = array(
                        'Content-Type: text/xml',
                        'X-EBAY-API-APP-ID:' . $this->APP_ID,
                        'X-EBAY-API-VERSION:' . $this->COMPATIBILITY_LEVEL,
                        'X-EBAY-API-SITE-ID:0',
                        'X-EBAY-API-CALL-NAME:GetMultipleItems',
                        'X-EBAY-API-REQUEST-ENCODING:XML',
                );
                
                $body = '<?xml version="1.0" encoding="utf-8"?>'
                    .	'<GetMultipleItemsRequest xmlns="urn:ebay:apis:eBLBaseComponents">'
                    .       $items_ids_str 
                    .	'<IncludeSelector>Details</IncludeSelector>'
                    .	'</GetMultipleItemsRequest>';

                $this->resp = $this->send($url, $headers, $body);
                $this->xml  = simplexml_load_string($this->resp);

                $this->parseXML();
                sleep(1);

                if($this->debug)
                {
                        echo "<pre>"; var_dump($this->resp); echo "</pre>";
                        echo "<pre>"; var_dump($this->xml); echo "</pre>";
                        echo '<b>' . $url . '</b><pre>' . print_r($headers, true) . '</pre><pre>' . htmlspecialchars($body) . '</pre>';
                }

            }

            return $this->items;
        }

        /**
         *
         */
        protected function parseXML()
        {
            if(empty($this->xml))
            {
                    $this->error_msg = $this->errors['response-empty'];
                    return;
            }
            elseif ($this->xml->Ack != "Success") 
            {
                    $this->error_msg = $this->errors['response-filed'];
                    return;
            }

            $i = $this->items_iterator;
            foreach($this->xml->Item as $item)
            {
                $currency_attrs = $item->CurrentPrice->attributes();

                $itemId       = (string)$item->ItemID;
                $title        = (string)$item->Title;

                $pictureUrls  = (array)$item->PictureURL;

                $pictureUrl   = $pictureUrls[0];
                $itemUrl      = (string)$item->ViewItemURLForNaturalSearch;

                $dateOfAdded  = strtotime($item->StartTime);
                $dateOfEnded  = strtotime($item->EndTime);
                $timeLeft     = $this->parseTimeLeft($item->TimeLeft);

                $quantity     = (int)$item->Quantity;
                $quantitySold = (int)$item->QuantitySold;

                $bidCount     = (int)$item->BidCount;

                $price        = (double)$item->CurrentPrice;
                $currency     = (string)$currency_attrs['currencyID'];
                $buyItNow     = (isset($item->BuyItNowAvailable) and $item->BuyItNowAvailable == 'true') ? (double)$item->ConvertedBuyItNowPrice : -1;
                $paymentMethods = (string)$item->PaymentMethods;

                $returnPolicy   = (string)$item->ReturnPolicy->ReturnsAccepted;

                $shipping       = (array)($item->ShipToLocations);
                $excludeShipLocations = (array)$item->ExcludeShipToLocation;
                $globalShipping = intval($item->GlobalShipping == 'true');

                $condition      = (string)$item->ConditionDescription;

                $auctionType    = (string)$item->ListingType;

                $item = EbayItem::getListingItem($bidCount, $buyItNow, $currency, $dateOfAdded, $dateOfEnded, $itemId, $itemUrl, $pictureUrl,
                    $price, $shipping, $timeLeft, $title, $excludeShipLocations, $quantitySold, $quantity, $returnPolicy, $paymentMethods, $condition, $globalShipping, $auctionType);

                $this->items[] = $item;

/*
                $this->xml_data_arr[$i]['ItemID'] = $item->ItemID;
                $this->xml_data_arr[$i]['PictureURL'] = $item->PictureURL[0];
                $this->xml_data_arr[$i]['ItemURL'] = $item->ViewItemURLForNaturalSearch;
                $this->xml_data_arr[$i]['Title'] = $item->Title;
                $this->xml_data_arr[$i]['Price'] = $item->ConvertedCurrentPrice;
                $this->xml_data_arr[$i]['BidCount'] = $item->BidCount;
                $this->xml_data_arr[$i]['TimeLeft'] = $item->TimeLeft;				
                $this->xml_data_arr[$i]['AvailableQuantity'] = (int)$item->Quantity - (int)$item->QuantitySold;				
                $this->xml_data_arr[$i]['BuyItNow'] = -1;

                if(isset($item->BuyItNowAvailable))
                {
                    if(strcmp($item->BuyItNowAvailable, 'true') == 0)
                    {
                        $this->xml_data_arr[$i]['BuyItNow'] = (double)$item->ConvertedBuyItNowPrice;
                    }
                }

                //$query = "SELECT `ShippingInfo` FROM `articles` WHERE `EbayId`='" . $item->ItemID . "'";
                //$resp_shipInfo = mysql_query($query);
                //$row_shipInfo = mysql_fetch_row($resp_shipInfo);
                //$this->xml_data_arr[$i]['Shipping'] = $row_shipInfo[0];
*/

                $i++;
                $this->items_iterator++;
            }

            if(!empty($this->sortMethod))
            {
                //$this->xml_data_arr = $mainClass->sortItemsXML($this->xml_data_arr, $this->sortMethod);
            }
        }
    }

