<?php

use ContentEgg\application\helpers\TemplateHelper;

if (TemplateHelper::isModuleDataExist($items, 'Amazon'))
    \wp_enqueue_script('cegg-frontend', \ContentEgg\PLUGIN_RES . '/js/frontend.js', array('jquery'));


if (empty($cols) || $cols > 12)
    $cols = 4;
$col_size = ceil(12 / $cols);
?>

<div class="egg-container egg-grid">
    <?php if ($title): ?>
        <h3><?php echo esc_html($title); ?></h3>
    <?php endif; ?>

    <div class="container-fluid">
        <?php $i = 0; ?>
        <div class="row">
            <?php foreach ($items as $item): ?>
                <div class="col-md-<?php echo $col_size; ?> cegg-gridbox"> 
                    <a rel="nofollow" target="_blank" href="<?php echo esc_url($item['url']) ?>">

                        <div class="cegg-thumb">
                            <?php if ($item['percentageSaved'] && $item['percentageSaved'] < 100 && $item['percentageSaved'] > 0): ?>
                                <div class="cegg-promotion">
                                    <span class="cegg-discount">- <?php echo round($item['percentageSaved']); ?>%</span>
                                </div>              
                            <?php endif; ?>

                            <?php if ($item['img']): ?>
                                <img src="<?php echo esc_attr($item['img']) ?>" alt="<?php echo esc_attr($item['title']); ?>" />
                            <?php endif; ?>
                        </div>

                        <div class="producttitle">
                            <?php if ($merhant = TemplateHelper::getMerhantName($item)): ?>
                                <div class="cegg-mb10">
                                    <?php if (!empty($item['domain'])): ?><img src="<?php echo esc_attr(TemplateHelper::getMerhantIconUrl($item, true)); ?>" /> <?php endif; ?><small class="title-case"><?php echo esc_html($merhant); ?></small>
                                </div>
                            <?php endif; ?>
                            <?php echo esc_html(TemplateHelper::truncate($item['title'], 80)); ?>                 
                        </div>

                        <?php if ((int) $item['rating'] > 0 && (int) $item['rating'] <= 5): ?>
                            <div class="cegg-title-rating">
                                <span class="rating_small"><?php
                                    echo str_repeat("<span>???</span>", (int) $item['rating']);
                                    echo str_repeat("<span>???</span>", 5 - (int) $item['rating']);
                                    ?></span>
                                <?php if (!empty($item['reviewsCount'])): ?><small class="cegg-reviews-count small-text">(<?php echo (int) $item['reviewsCount']; ?>)</small><?php endif; ?>

                            </div>
                        <?php elseif (!empty($item['extra']['data']['rating'])): ?>
                            <div class="cegg-title-rating">
                                <span class="rating_small"><?php
                                    echo str_repeat("<span>???</span>", (int) $item['extra']['data']['rating']);
                                    echo str_repeat("<span>???</span>", 5 - (int) $item['extra']['data']['rating']);
                                    ?></span>
                            </div>           
                        <?php endif; ?>

                        <div class="productprice">
                            <?php if ($item['price']): ?>
                                <?php if ($item['priceOld']): ?><strike><?php echo TemplateHelper::formatPriceCurrency($item['priceOld'], $item['currencyCode'], '<small>', '</small>'); ?></strike>&nbsp;<?php endif; ?>
                                <span class="cegg-price"><?php echo TemplateHelper::formatPriceCurrency($item['price'], $item['currencyCode']); ?></span>
                            <?php endif; ?>

                            <?php if (isset($item['stock_status']) && $item['stock_status'] == \ContentEgg\application\components\ContentProduct::STOCK_STATUS_OUT_OF_STOCK): ?>
                                <span title="<?php echo \esc_attr(sprintf(__('Last updated on %s', 'content-egg-tpl'), TemplateHelper::getLastUpdateFormatted($item['module_id'], $post_id))); ?>" class="stock-status status-<?php echo \esc_attr(TemplateHelper::getStockStatusClass($item)); ?>">
                                    <?php echo \esc_html(TemplateHelper::getStockStatusStr($item)); ?>
                                </span>
                            <?php endif; ?>                              
                        </div>


                        <?php if (!empty($item['extra']['sellingStatus']['bidCount'])): ?>
                            <div class="cegg-ebay-grid-bids"><small>
                                    <?php _e('Bids:', 'content-egg-tpl'); ?> <?php echo $item['extra']['sellingStatus']['bidCount']; ?>
                                </small></div>
                        <?php endif; ?>

                        <div class="cegg-btn-grid cegg-hidden">
                            <a rel="nofollow" target="_blank" href="<?php echo $item['url']; ?>" class="btn btn-success"><?php TemplateHelper::buyNowBtnText(true, $item); ?></a> 
                        </div>
                    </a>
                </div>
                <?php
                $i++;
                if ($i % $cols == 0 || $i == count($items)):
                    ?>
                    <div class="clearfix"></div>
                <?php endif; ?>             
            <?php endforeach; ?>  
            <?php if ($module_id == 'Amazon'): ?>
                <div class="text-muted text-right">
                    <small>
                        <?php _e('Last updated on', 'content-egg-tpl'); ?> <?php echo TemplateHelper::getLastUpdateFormatted($module_id, $post_id); ?>
                        <?php TemplateHelper::printAmazonDisclaimer(); ?>                        
                    </small>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

