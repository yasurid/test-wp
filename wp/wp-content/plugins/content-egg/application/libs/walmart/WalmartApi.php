<?php

namespace ContentEgg\application\libs\walmart;

use ContentEgg\application\libs\RestClient;

/**
 * WalmartApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link httpS://www.keywordrush.com
 * @copyright Copyright &copy; 2019 keywordrush.com
 *
 * @link: https://developer.walmartlabs.com/docs/
 * @link: https://walmart.io/docs/affiliate/specification
 */
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class WalmartApi extends RestClient {

    const API_URI_BASE = 'http://api.walmartlabs.com/v1';

    protected $apiKey;
    protected $_responseTypes = array(
        'json',
    );

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->setUri(self::API_URI_BASE);
        $this->setResponseType('json');
    }

    /**
     * Search for items
     * @link: https://developer.walmartlabs.com/docs/read/Search_API
     */
    public function search($keywords, array $options)
    {
        $options['query'] = $keywords;
        $response = $this->restGet('/search', $options);
        return $this->_decodeResponse($response);
    }

    /**
     * Product Lookup API
     * @link: https://developer.walmartlabs.com/docs/read/Home
     */
    public function products($item_ids, $options = array())
    {
        if (is_array($item_ids))
            $item_ids = join(',', $item_ids);
        $options['ids'] = $item_ids;

        $response = $this->restGet('/items', $options);
        return $this->_decodeResponse($response);
    }

    /*
     * @link: https://developer.walmartlabs.com/docs/read/Reviews_Api
     */

    public function reviews($item_id, $options = array())
    {
        $response = $this->restGet('/reviews/' . urlencode($item_id), $options);
        return $this->_decodeResponse($response);
    }

    public function restGet($path, array $query = null)
    {
        $query['apiKey'] = $this->apiKey;
        $query['format'] = 'json';

        return parent::restGet($path, $query);
    }

}
