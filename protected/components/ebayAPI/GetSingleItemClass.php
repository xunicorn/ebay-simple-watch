<?php

	class GetSingleItemClass extends EbayAPI
	{

        protected $itemId;

		public function __construct($itemId)
		{
            parent::__construct();
			$this->itemId = $itemId;
		}

        protected function initVars()
        {
            // TODO: Implement initVars() method.
        }

        protected function initErrors()
        {
            // TODO: Implement initErrors() method.
        }


        /*
            Getting info for single item by item ID
        */
		public function makeAPICall()
		{
			//$url = $shoppingURL;
			$url = $this->shoppingURL;

			$headers = array(
				'Content-Type: text/xml',
				'X-EBAY-API-APP-ID:'  . $this->APP_ID,
				'X-EBAY-API-VERSION:' . $this->COMPATIBILITY_LEVEL,
				'X-EBAY-API-SITE-ID:0',
				'X-EBAY-API-CALL-NAME:GetSingleItem',
				'X-EBAY-API-REQUEST-ENCODING:XML'
			);

			$body = 
					'<?xml version="1.0" encoding="utf-8"?>'
				.	'<GetSingleItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">'
				.	  '<ItemID>' . $this->itemId . '</ItemID>'
				.	  '<IncludeSelector>TextDescription,ItemSpecifics,Details,ShippingCosts</IncludeSelector>'
				.	'</GetSingleItemRequest>'
			;

			$this->resp = $this->send($url, $headers, $body);;
			$this->xml = simplexml_load_string($this->resp);

			//$this->parseXML();

			if($this->debug)
			{
				echo "<pre>"; var_dump($this->resp); echo "</pre>";
				echo "<pre>"; var_dump($this->xml); echo "</pre>";
				echo '<b>' . $url . '</b><pre>' . print_r($headers, true) . '</pre><pre>' . htmlspecialchars($body) . '</pre>';
			}
		}

		/*
			Parsing single item XML
		*/
		public function parseXML()
		{
			global $placeOfferClass;

            if(empty($this->xml)) {
                $this->error_msg = $this->errors[EbayAPI::ERROR_RESPONSE_EMPTY];
                throw new Exception($this->error_msg);
            }

			if($this->xml->Ack == 'Failure')
			{
				$this->error_msg = $this->errors[EbayAPI::ERROR_RESPONSE_FAILED];
				throw new Exception($this->error_msg);
			}
			
			$this->title = $this->xml->Item->Title;
			
			$item_return_policy = ' - ';
			if(isset($this->xml->Item->ReturnPolicy->ReturnsAccepted))
			{
				if($this->xml->Item->ReturnPolicy->ReturnsAccepted == 'ReturnsNotAccepted')
					$item_return_policy = 'returns not accepted';
				else
				{
					$item_return_policy = $this->xml->Item->ReturnPolicy->ReturnsWithin . ' ';
					$item_return_policy .= $this->xml->Item->ReturnPolicy->Refund . ', ';
					$item_return_policy .= $this->xml->Item->ReturnPolicy->ShippingCostPaidBy . '  pays return shipping';

					$item_return_policy = strtolower($item_return_policy);
				}
			}
			
			$item_payment_method = '';
			foreach($this->xml->Item->PaymentMethods as $pay_method)
				$item_payment_method .= $pay_method . ', ';
			if(strlen($item_payment_method) > 6)
				$item_payment_method = substr($item_payment_method, 0, strlen($item_payment_method) - 2);
			else
				$item_payment_method = ' - ';
				
			$item_shipping_delivery = '';
			foreach($this->xml->Item->ShipToLocations as $loc)
				$item_shipping_delivery .= $loc . ', ';
			$item_shipping_delivery = substr($item_shipping_delivery, 0, strlen($item_shipping_delivery) - 2);

			$item_shipping_delivery_excl = '';
			if(isset($this->xml->Item->ExcludeShipToLocation))
			{
				foreach($this->xml->Item->ExcludeShipToLocation as $loc)
					$item_shipping_delivery_excl .= $loc . ', ';
				$item_shipping_delivery_excl = substr($item_shipping_delivery_excl, 0, strlen($item_shipping_delivery_excl) - 2);
			}
			if(empty($item_shipping_delivery_excl))
				$item_shipping_delivery_excl = ' - ';
				
			$shippingCosts = new GetShippingCosts($this->itemId);
			$shippingCosts->getShippingCosts();
			$item_shipping_service_name = $shippingCosts->shipping_service_name;
			$item_shipping_delivery_time = date("D. M. y", strtotime($shippingCosts->shipping_delivery_time));

		
			$this->xml_data_arr['Description'] 						= $this->xml->Item->Description;
			$this->xml_data_arr['ItemID'] 							= $this->itemId;
			$this->xml_data_arr['Title'] 							= $this->xml->Item->Title;
			
			foreach($this->xml->Item->PictureURL as $pic_url)
				$this->xml_data_arr['PictureURL'][] = $pic_url;

			$this->xml_data_arr['ConditionDisplayName'] 			= $this->xml->Item->ConditionDisplayName;
			$this->xml_data_arr['TimeLeft'] 						= $this->xml->Item->TimeLeft;
			$this->xml_data_arr['EndTime'] 							= date("M d, Y H:i:s", strtotime($this->xml->Item->EndTime)) . " PST";
			$this->xml_data_arr['Price'] 							= $this->xml->Item->ConvertedCurrentPrice;
			$this->xml_data_arr['ListingType'] 						= $this->xml->Item->ListingType;
			$this->xml_data_arr['BidCount'] 						= $this->xml->Item->BidCount;
			$this->xml_data_arr['Location'] 						= $this->xml->Item->Location;
			$this->xml_data_arr['BuyItNow'] 						= strcmp($this->xml->Item->BuyItNowAvailable, 'true') == 0 ? (double)$this->xml->Item->ConvertedBuyItNowPrice : -1;
			$this->xml_data_arr['ReturnPolicy'] 					= $item_return_policy;
			$this->xml_data_arr['PaymentMethods'] 					= $item_payment_method;
			$this->xml_data_arr['MinimumToBid'] 					= strpos($this->xml->Item->ListingType, "Fixed") === false ? $this->xml->Item->MinimumToBid : $this->xml_data_arr['Price'];
			$this->xml_data_arr['AvailableQuantity'] 				= strpos($this->xml->Item->ListingType, "Fixed") === false ? 1 : $this->xml->Item->Quantity - $this->xml->Item->QuantitySold;
			$this->xml_data_arr['SellerUserID'] 					= $this->xml->Item->Seller->UserID;
			$this->xml_data_arr['SellerFeedbackScore'] 				= $this->xml->Item->Seller->FeedbackScore;
			$this->xml_data_arr['SellerPositiveFeedbackPercent']	= $this->xml->Item->Seller->PositiveFeedbackPercent;
			$this->xml_data_arr['TopRatedSeller'] 					= $this->xml->Item->Seller->TopRatedSeller;
			$this->xml_data_arr['ShippingType'] 					= $this->xml->Item->ShippingCostSummary->ShippingType;
			$this->xml_data_arr['ShippingServiceName']				= $item_shipping_service_name;
			$this->xml_data_arr['ShipToLocations'] 					= $item_shipping_delivery;
			$this->xml_data_arr['ShippingServiceCost'] 				= strcmp($this->xml->Item->ShippingCostSummary->ShippingType, 'Calculated') == 0 ? ' - ' :  $this->xml->Item->ShippingCostSummary->ShippingServiceCost;
			$this->xml_data_arr['ExcludeShipToLocation'] 			= $item_shipping_delivery_excl;
			$this->xml_data_arr['ShippingDeliveryTime'] 			= $item_shipping_delivery_time;
			
			if(isset($this->xml->Item->ItemSpecifics))
				$this->xml_data_arr['ItemSpecifics']				= $this->xml->Item->ItemSpecifics;
			
		}
	}



?>