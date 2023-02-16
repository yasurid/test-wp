<?php

namespace ContentEgg\application\libs\amazon;

/**
 * AmazonLocales class
 *  
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2019 keywordrush.com
 */
class AmazonLocales {

    static public $locales = array(
        'au' => array(
            'Australia',
            'amazon.com.au',
            'webservices.amazon.com.au',
            'us-west-2',
        ),
        'br' => array(
            'Brazil',
            'amazon.com.br',
            'webservices.amazon.com.br',
            'us-east-1',
        ),
        'ca' => array(
            'Canada',
            'amazon.ca',
            'webservices.amazon.ca',
            'us-east-1',
        ),
        'fr' => array(
            'France',
            'amazon.fr',
            'webservices.amazon.fr',
            'eu-west-1',
        ),
        'de' => array(
            'Germany',
            'amazon.de',
            'webservices.amazon.de',
            'eu-west-1',
        ),
        'in' => array(
            'India',
            'amazon.in',
            'webservices.amazon.in',
            'eu-west-1',
        ),
        'it' => array(
            'Italy',
            'amazon.it',
            'webservices.amazon.it',
            'eu-west-1',
        ),
        'jp' => array(
            'Japan',
            'amazon.co.jp',
            'webservices.amazon.co.jp',
            'us-west-2',
        ),
        'mx' => array(
            'Mexico',
            'amazon.com.mx',
            'webservices.amazon.com.mx',
            'us-east-1',
        ),
        'nl' => array(
            'Netherlands',
            'amazon.nl',
            'webservices.amazon.nl',
            'eu-west-1',
        ),
        'sg' => array(
            'Singapore',
            'amazon.sg',
            'webservices.amazon.sg',
            'us-west-2',
        ),
        'es' => array(
            'Spain',
            'amazon.es',
            'webservices.amazon.es',
            'eu-west-1',
        ),
        'tr' => array(
            'Turkey',
            'amazon.com.tr',
            'webservices.amazon.com.tr',
            'eu-west-1',
        ),
        'ae' => array(
            'United Arab Emirates',
            'amazon.ae',
            'webservices.amazon.ae',
            'eu-west-1',
        ),
        'uk' => array(
            'United Kingdom',
            'amazon.co.uk',
            'webservices.amazon.co.uk',
            'eu-west-1',
        ),
        'us' => array(
            'United States',
            'amazon.com',
            'webservices.amazon.com',
            'us-east-1',
        ),
    );

    static public function locales()
    {
        return self::$locales;
    }

    static public function getLocale($locale)
    {
        $locales = self::$locales;
        if (isset($locales[$locale]))
            return $locales[$locale];
        else
            throw new \Exception("Locale {$locale} does not exist.");
    }

    static public function getApiHost($locale)
    {
        $data = self::getLocale($locale);
        return $data[2];
    }

    static public function getApiEndpoint($locale)
    {
        return 'https://' . self::getApiHost($locale);
    }

    static public function getRegion($locale)
    {
        $data = self::getLocale($locale);
        return $data[3];
    }

    static public function getDomain($locale)
    {
        $data = self::getLocale($locale);
        return $data[1];
    }

}
