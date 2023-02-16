<?php

namespace ContentEgg\application\modules\Walmart;

use ContentEgg\application\components\ExtraData;

/**
 * WalmartDataEnvato class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 * 
 * @link: https://developer.walmartlabs.com/docs/read/Item_Field_Description
 */
class ExtraDataWalmart extends ExtraData {

    public $comments = array();
    public $productTrackingUrl;
    public $ninetySevenCentShipping;
    public $standardShipRate;
    public $twoThreeDayShippingRate;
    public $overnightShippingRate;
    public $specialBuy;
    public $customerRatingImage;
    public $size;
    public $color;
    public $marketplace;
    public $shipToStore;
    public $freeShipToStore;
    public $modelNumber;
    public $categoryNode;
    public $bundle;
    public $clearance;
    public $preOrder;
    public $offerType;
    public $isTwoDayShippingEligible;
    public $availableOnline;
    public $sellerInfo;
    public $shippingPassEligible;
    public $imageEntities = array();

}
