<?php
/**
 * Created by PhpStorm.
 * User: Администратор
 * Date: 08.11.2015
 * Time: 10:00
 */

class EbayItem {

    public $id;
    public $request;

    public $itemId;
    public $title;

    public $pictureUrl;
    public $itemUrl;

    public $dateOfAdded;
    public $dateOfEnded;
    public $timeLeft;

    public $shipping;
    public $excludeShipLocations;
    public $globalShipping;

    public $price;
    public $currency;
    public $buyItNow;
    public $paymentMethods;

    public $quantitySold;
    public $quantity;

    public $bidCount;

    public $returnPolicy;

    public $condition;

    public $auctionType;

    public $sellerId;
    public $sellerFeedbackRatingStar;
    public $sellerFeedbackScore;
    public $sellerPositiveFeedbackPercent;

    private static $counter = 0;

    private function __construct() {
        self::$counter++;
        $this->id = self::$counter;
    }

    public static function getSearchItem($bidCount, $buyItNow, $currency, $dateOfAdded, $dateOfEnded, $itemId, $itemUrl, $pictureUrl, $price, $request, $shipping, $timeLeft, $title, $auctionType) {

        $obj = new EbayItem();

        $obj->bidCount    = $bidCount;
        $obj->buyItNow    = $buyItNow;
        $obj->currency    = $currency;
        $obj->dateOfAdded = $dateOfAdded;
        $obj->dateOfEnded = $dateOfEnded;
        $obj->itemId      = $itemId;
        $obj->itemUrl     = $itemUrl;
        $obj->pictureUrl  = $pictureUrl;
        $obj->price       = $price;
        $obj->request     = $request;
        $obj->shipping    = $shipping;
        $obj->timeLeft    = $timeLeft;
        $obj->title       = $title;
        $obj->auctionType = $auctionType;

        return $obj;
    }

    public static function getListingItem(
        $bidCount, $buyItNow, $currency, $dateOfAdded, $dateOfEnded, $itemId, $itemUrl, $pictureUrl, $price, $shipping,
        $timeLeft, $title, $excludeShipLocations, $quantitySold, $quantity, $returnPolicy, $paymentMethods, $condition, $globalShipping, $auctionType) {

        $obj = new EbayItem();

        $obj->bidCount    = $bidCount;
        $obj->buyItNow    = $buyItNow;
        $obj->currency    = $currency;
        $obj->dateOfAdded = $dateOfAdded;
        $obj->dateOfEnded = $dateOfEnded;
        $obj->itemId      = $itemId;
        $obj->itemUrl     = $itemUrl;
        $obj->pictureUrl  = $pictureUrl;
        $obj->price       = $price;

        $obj->shipping             = implode(' ', $shipping);
        $obj->globalShipping       = $globalShipping;
        $obj->excludeShipLocations = implode(', ', $excludeShipLocations);

        $obj->timeLeft    = $timeLeft;
        $obj->title       = $title;


        $obj->quantitySold         = $quantitySold;
        $obj->quantity             = $quantity;
        $obj->returnPolicy         = $returnPolicy;
        $obj->paymentMethods       = $paymentMethods;
        $obj->condition            = $condition;

        $obj->auctionType = $auctionType;

        return $obj;
    }
}