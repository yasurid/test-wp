<?php

namespace ContentEgg\application\modules\Kelkoo;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataKelkoo class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2018 keywordrush.com
 * 
 */
class ExtraDataKelkoo extends ExtraData {

    public $lastModified;
    public $video = array();
    public $mobileFriendly;
    public $price = array();
    public $productClass;
    public $offensiveContent;
    public $promo = array();
    public $financingOption = array();
    public $merchantCategory;
    public $brandId;
    public $greenProduct;
    public $otherImages = array();
    public $flight = array();
    public $type;
    public $merchant = array();
}
