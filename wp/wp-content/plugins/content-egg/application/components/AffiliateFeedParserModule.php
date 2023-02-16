<?php

namespace ContentEgg\application\components;

use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\helpers\TemplateHelper;
use ContentEgg\application\components\ModuleManager;

/**
 * AffiliateFeedParserModule abstract class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2019 keywordrush.com
 */
abstract class AffiliateFeedParserModule extends AffiliateParserModule {

    const TRANSIENT_LAST_IMPORT_DATE = 'cegg_products_last_import_';
    const PRODUCTS_TTL = 86400;
    const MULTIPLE_INSERT_ROWS = 50;
    const IMPORT_TIME_LIMT = 300;
    const DATAFEED_DIR_NAME = 'cegg-datafeeds';

    protected $rmdir;
    protected $product_model;

    abstract public function getProductModel();

    abstract public function getFeedUrl();

    abstract protected function feedProductPrepare(array $data);

    public function __construct($module_id = null)
    {
        parent::__construct($module_id);
        $this->product_model = $this->getProductModel();
        
        // download feed in background
        \add_action('cegg_' . $this->getId() . '_init_products', array(get_called_class(), 'initProducts'));        
    }

    public static function initProducts()
    {
        ModuleManager::factory(static::getIdStatic())->maybeImportProducts();
    }

    public function requirements()
    {
        $required_version = '5.6.4';
        $mysql_version = $this->product_model->getDb()->get_var('SELECT VERSION();');
        $errors = array();

        if (version_compare($required_version, $mysql_version, '>'))
            $errors[] = sprintf('You are using MySQL %s. This module requires at least <strong>MySQL %s</strong>.', $mysql_version, $required_version);

        return $errors;
    }

    public function isZippedFeed()
    {
        return false;
    }

    public function maybeCreateProductTable()
    {
        if (!$this->product_model->isTableExists())
            $this->dbDelta();
    }

    protected function dbDelta()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $sql = $this->product_model->getDump();
        dbDelta($sql);
    }

    public function getLastImportDate()
    {
        return \get_transient(self::TRANSIENT_LAST_IMPORT_DATE . $this->getId());
    }

    public function setLastImportDate($time = null)
    {
        if ($time === null)
            $time = time();
        \set_transient(self::TRANSIENT_LAST_IMPORT_DATE . $this->getId(), $time);
    }

    public function maybeImportProducts()
    {
        $last_export = $this->getLastImportDate();

        // product import is in progress?
        if ($last_export && $last_export < 0)
        {
            if (time() + $last_export > static::IMPORT_TIME_LIMT)
                $last_export = 0;
            else
                throw new \Exception('Product import is in progress. Try later.');
        }

        if ($this->isImportTime())
        {
            // set in progress flag
            $this->setLastImportDate(time() * -1);
            $this->maybeCreateProductTable();
            $this->importProducts($this->getFeedUrl());
            return true;
        }
        return false;
    }

    public function isImportTime()
    {
        $last_export = $this->getLastImportDate();
        if (!$last_export || (time() - $last_export > self::PRODUCTS_TTL))
            return true;
        else
            return false;
    }

    public function importProducts($feed_url)
    {
        @set_time_limit(static::IMPORT_TIME_LIMT);
        \wp_raise_memory_limit();

        $this->product_model->truncateTable();

        //$start = microtime(true);
        $file = $this->downlodFeed($feed_url);
        //\ContentEgg\prn('Download + Unzip Feed: ' . (microtime(true) - $start));
        //$start = microtime(true);
        $this->processFeed($file);
        //\ContentEgg\prn('Save in DB: ' . (microtime(true) - $start));

        $this->setLastImportDate();

        @unlink($file);
        if ($this->rmdir)
        {
            @rmdir($this->rmdir);
            $this->rmdir = null;
        }
    }

    protected function downlodFeed($feed_url)
    {
        if (!function_exists('\download_url'))
            require_once( ABSPATH . "wp-admin" . '/includes/file.php');

        $tmp = \download_url($feed_url);
        if (\is_wp_error($tmp))
        {
            $this->setLastImportDate(0);
            throw new \Exception(sprintf('Feed URL could not be downloaded: %s.', $tmp->get_error_message()));
        }

        if (!$this->isZippedFeed())
            return $tmp;
        else
            return $this->unzipFeed($tmp);
    }

    protected function unzipFeed($file)
    {
        if (!function_exists('\unzip_file'))
            require_once( ABSPATH . 'wp-admin/includes/file.php' );

        global $wp_filesystem;
        if (!$wp_filesystem)
            \WP_Filesystem();

        //$to = $file . '.unzipped-dir';
        $to = trailingslashit($this->getDatafeedDir()) . basename($file) . '-unzipped-dir';

        $result = \unzip_file($file, $to);
        @unlink($file);
        if (\is_wp_error($result))
        {
            $this->setLastImportDate(0);
            throw new \Exception(sprintf('Unable to unzip feed archive: %s.', $result->get_error_message()));
        }

        $scanned = array_values(array_diff(scandir($to), array('..', '.')));
        if (!$scanned || !isset($scanned[0]))
        {
            $this->setLastImportDate(0);
            throw new \Exception('Unable to find unziped feed.');
        }

        $this->rmdir = $to;
        return $to . DIRECTORY_SEPARATOR . $scanned[0];
    }

    protected function processFeed($file)
    {
        $handle = fopen($file, "r");
        $fields = array();
        $products = array();

        $delimer = $this->detectCsvDelimiter($file);
        $in_stock_only = $this->config('in_stock', false);
        $i = 0;
        while (($data = fgetcsv($handle, 0, $delimer)) !== false)
        {
            if (!$fields)
            {
                $fields = $data;
                continue;
            }
            if (count($fields) != count($data))
                continue;
            $data = array_combine($fields, $data);
            if (!$product = $this->feedProductPrepare($data))
                continue;
            if ($in_stock_only && $product['stock_status'] == ContentProduct::STOCK_STATUS_OUT_OF_STOCK)
                continue;
            $products[] = $product;
            $i++;
            if ($i % static::MULTIPLE_INSERT_ROWS == 0)
            {
                $this->product_model->multipleInsert($products, static::MULTIPLE_INSERT_ROWS);
                $products = array();
            }
        }
        if ($products)
            $this->product_model->multipleInsert($products, static::MULTIPLE_INSERT_ROWS);

        fclose($handle);
    }

    public function getLastImportDateReadable()
    {
        $last_import = $this->getLastImportDate();

        if (empty($last_import))
            return '';

        if ($last_import < 0)
            return __('Product import is in progress.', 'content-egg');

        if (time() - $last_import <= 43200)
            return sprintf(__('%s ago', '%s = human-readable time difference', 'content-egg'), \human_time_diff($last_import, time()));

        return TemplateHelper::dateFormatFromGmt($last_import, true);
    }

    public function getProductCount()
    {
        return $this->product_model->count();
    }

    protected function getDatafeedDir()
    {
        $upload_dir = \wp_upload_dir();
        $datafeed_dir = $upload_dir['basedir'] . '/' . static::DATAFEED_DIR_NAME;

        if (is_dir($datafeed_dir))
            return $datafeed_dir;

        $files = array(
            array(
                'file' => 'index.html',
                'content' => '',
            ),
            array(
                'file' => '.htaccess',
                'content' => 'deny from all',
            ),
        );

        foreach ($files as $file)
        {
            if (\wp_mkdir_p($datafeed_dir) && !file_exists(trailingslashit($datafeed_dir) . $file['file']))
            {
                if ($file_handle = @fopen(trailingslashit($datafeed_dir) . $file['file'], 'w'))
                {
                    fwrite($file_handle, $file['content']);
                    fclose($file_handle);
                }
            }
        }
    }

    protected function detectCsvDelimiter($file)
    {
        $delimiters = array(
            ';' => 0,
            ',' => 0,
            "\t" => 0,
            "|" => 0
        );

        $handle = fopen($file, "r");
        $firstLine = fgets($handle);
        fclose($handle);
        foreach ($delimiters as $delimiter => &$count)
        {
            $count = count(str_getcsv($firstLine, $delimiter));
        }

        return array_search(max($delimiters), $delimiters);
    }

}
