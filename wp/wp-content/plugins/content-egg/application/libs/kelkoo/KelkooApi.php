<?php

namespace ContentEgg\application\libs\kelkoo;

use ContentEgg\application\libs\RestClient;

/**
 * KelkooApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2018 keywordrush.com
 *
 * @link: https://www.kelkoogroup.com/kelkoo-customer-service/kelkoo-developer-network/
 */
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class KelkooApi extends RestClient {

    const API_URI_BASE = 'http://{{region}}.shoppingapis.kelkoo.com';

    protected $trackingId;
    protected $affiliateKey;
    protected $region;
    protected $timestamp;
    protected $_responseTypes = array(
        'json',
    );

    public function __construct($region, $trackingId, $affiliateKey)
    {
        $this->trackingId = $trackingId;
        $this->affiliateKey = $affiliateKey;
        $this->region = $region;
        $this->setUri(str_replace('{{region}}', $this->region, self::API_URI_BASE));
        $this->timestamp = time();
        $this->setResponseType('json');
    }

    /**
     * Product Search
     * @link: https://www.kelkoogroup.com/kelkoo-customer-service/kelkoo-developer-network/shopping-services/product-search-v3/
     */
    public function search($keywords, array $options)
    {
        $options['query'] = $keywords;
        return $this->productSearch($options);
    }

    public function searchEan($ean, array $options)
    {
        $options['ean'] = $ean;
        return $this->productSearch($options);
    }
    
    public function offer($offerId)
    {
        $options['offerId'] = $offerId;
        return $this->productSearch($options);
    }    

    /**
     * @link: https://www.kelkoogroup.com/kelkoo-customer-service/kelkoo-developer-network/shopping-services/merchant-search/
     */
    public function merchant($id)
    {
        $options['merchantId'] = $id;
        $response = $this->restGet('/V2/merchantSearch', $options);
        return $this->_decodeResponse($response);
    }

    private function productSearch(array $options)
    {
        $response = $this->restGet('/V3/productSearch', $options);
        return $this->_decodeResponse($response);
    }

    public function restGet($path, array $query = null)
    {
        $hash = $this->generateSign($path, $query);

        $query['aid'] = $this->trackingId;
        $query['timestamp'] = $this->timestamp;
        $query['hash'] = $hash;

        $this->setCustomHeaders(array('Content-Type' => 'application/json'));
        return parent::restGet($path, $query);
    }

    /**
     * In order to access the service, you’ll need first to “sign” your URLs.
     * @link: https://www.kelkoogroup.com/kelkoo-customer-service/kelkoo-developer-network/shopping-services/samples/signing-url-php/
     */
    private function generateSign($path, $query)
    {
        $url_parts = parse_url($this->getUri());
        $urlDomain = $url_parts['scheme'] . '://' . $url_parts['host'];
        $urlPath = '';
        if (isset($url_parts['path']))
            $urlPath .= $url_parts['path'];
        $urlPath .= $path . '?' . http_build_query($query);
        $partner = $this->trackingId;
        $key = $this->affiliateKey;

        settype($urlDomain, 'String');
        settype($urlPath, 'String');
        settype($partner, 'String');
        settype($key, 'String');

        $URL_sig = "hash";
        $URL_ts = "timestamp";
        $URL_partner = "aid";
        $URLreturn = "";
        $URLtmp = "";
        $s = "";
        // get the timestamp
        $time = $this->timestamp;

        // replace " " by "+"
        $urlPath = str_replace(" ", "+", $urlPath);
        // format URL
        $URLtmp = $urlPath . "&" . $URL_partner . "=" . $partner . "&" . $URL_ts . "=" . $time;

        // URL needed to create the tokken
        $s = $urlPath . "&" . $URL_partner . "=" . $partner . "&" . $URL_ts . "=" . $time . $key;
        $tokken = "";
        $tokken = base64_encode(pack('H*', md5($s)));
        $tokken = str_replace(array("+", "/", "="), array(".", "_", "-"), $tokken);

        return $tokken;

        //$URLreturn = $urlDomain . $URLtmp . "&" . $URL_sig . "=" . $tokken;
        return $URLreturn;
    }

}
