<?php

namespace ContentEgg\application\libs\ebay;

use ContentEgg\application\libs\RestClient;

/**
 * EbayShopping class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 * 
 * Ebay Shopping API
 * @link: http://developer.ebay.com/DevZone/finding/Concepts/FindingAPIGuide.html
 */
class EbayShopping extends RestClient {

    const API_URI_BASE = 'http://open.api.ebay.com/shopping';
    const API_VERSION = 799;

    private $siteid;
    private $app_id;
    protected $_responseTypes = array(
        'json',
    );

    public function __construct($app_id, $global_id = 'EBAY-US', $responseType = 'json')
    {
        $this->app_id = $app_id;
        $this->setResponseType($responseType);
        $this->setUri(self::API_URI_BASE);
        $this->siteid = self::getSiteIdByGlobalId($global_id);
    }

    /**
     * This call retrieves publicly available data for one or more listings.
     * @link: http://developer.ebay.com/devzone/shopping/docs/callref/GetMultipleItems.html
     */
    public function getMultipleItems(array $item_ids, array $params = array())
    {
        /**
         * Standard URL Parameters and HTTP Header Values
         * @link: http://developer.ebay.com/DevZone/shopping/docs/Concepts/ShoppingAPI_FormatOverview.html#StandardURLParameters
         */
        $params['appid'] = $this->app_id;
        $params['callname'] = 'GetMultipleItems';
        $params['responseencoding'] = strtoupper($this->getResponseType());
        $params['siteid'] = $this->siteid;
        $params['version'] = self::API_VERSION;

        /**
         * The item ID that uniquely identifies the item listing for which to retrieve 
         * the data. You can provide a maximum of 20 item IDs.
         * Alternatively, as a shortcut for URL requests, you can specify the item 
         * IDs as a comma-separated list in a single ItemID parameter 
         * (e.g., ...<code>&ItemID=130310421484,300321408208,370214653822... ) for convenience. 
         */
        $item_ids = array_slice($item_ids, 0, 20);
        $params['ItemID'] = join(',', $item_ids);

        $response = $this->restGet('', $params);
        return $this->_decodeResponse($response);
    }

    /**
     * eBay Site ID to Global ID Mapping
     * @link: https://developer.ebay.com/DevZone/merchandising/docs/Concepts/SiteIDToGlobalID.html
     */
    public static function getSiteIdByGlobalId($global_id)
    {
        $map = array(
            'EBAY-US' => 0,
            'EBAY-ENCA' => 2,
            'EBAY-GB' => 3,
            'EBAY-AU' => 15,
            'EBAY-AT' => 16,
            'EBAY-FRBE' => 23,
            'EBAY-FR' => 71,
            'EBAY-DE' => 77,
            'EBAY-MOTOR' => 100,
            'EBAY-IT' => 101,
            'EBAY-NLBE' => 123,
            'EBAY-NL' => 146,
            'EBAY-ES' => 186,
            'EBAY-CH' => 193,
            'EBAY-HK' => 201,
            'EBAY-IN' => 203,
            'EBAY-IE' => 205,
            'EBAY-MY' => 207,
            'EBAY-FRCA' => 210,
            'EBAY-PH' => 211,
            'EBAY-PL' => 212,
            'EBAY-SG' => 216,
        );
        if (array_key_exists($global_id, $map))
            return $map[$global_id];
        else
            return 0;
    }

}
