<?php
/*
  Name: Product card
 */

__('Product card', 'content-egg-tpl');

use ContentEgg\application\helpers\TemplateHelper;
?>

<?php
\wp_enqueue_style('egg-bootstrap');
\wp_enqueue_style('egg-products');
?>


<div class="egg-container egg-item">

    <?php if ($title): ?>
        <h3 class="cegg-shortcode-title"><?php echo esc_html($title); ?></h3>
    <?php endif; ?>

    <div class="products">

        <?php foreach ($items as $item): ?>
            <?php $time_left = TemplateHelper::getTimeLeft($item['extra']['listingInfo']['endTimeGmt']); ?>
            <div class="row">
                <div class="col-md-6 text-center cegg-image-container cegg-mb20">
                    <?php if ($item['img']): ?>
                        <a rel="nofollow" target="_blank" href="<?php echo $item['url']; ?>">                    
                            <img src="<?php echo $item['img']; ?>" alt="<?php echo esc_attr($item['title']); ?>" />
                        </a>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <h2 class="cegg-no-top-margin"><?php echo esc_html($item['title']); ?></h2>
                    <div class="cegg-buyitnow-row cegg-mb10">
                        <?php if ($item['extra']['listingInfo']['buyItNowPrice']): ?>
                            <span class="text-muted"><?php _e('Buy It Now', 'content-egg-tpl'); ?>:</span>
                            <span class="cegg-price"><?php echo TemplateHelper::formatPriceCurrency($item['extra']['listingInfo']['buyItNowPrice'], $item['currencyCode'], '<small>', '</small>'); ?></span>
                        <?php endif; ?>

                    </div>
                    <div class="cegg-price-row cegg-mb10">
                        <?php if ($item['priceOld']): ?>
                            <span class="text-muted"><strike><?php echo TemplateHelper::formatPriceCurrency($item['priceOld'], $item['currencyCode'], '<small>', '</small>'); ?></strike></span><br>
                        <?php endif; ?>
                        <?php if ($item['price']): ?>
                            <span class="cegg-price"><?php echo TemplateHelper::formatPriceCurrency($item['price'], $item['currencyCode'], '<span class="cegg-currency">', '</span>'); ?></span>
                        <?php endif; ?>                        
                    </div>
                    <div class="cegg-btn-row cegg-mb20">
                        <a rel="nofollow" target="_blank" href="<?php echo $item['url']; ?>" class="btn btn-success cegg-btn-big"><?php $time_left ? TemplateHelper::buyNowBtnText(true, $item) : _e('VIEW THIS ITEM', 'content-egg-tpl'); ?></a>
                        <br><img src="<?php echo plugins_url('res/ebay_right_now.gif', __FILE__); ?>" />
                    </div>
                    <div class="cegg-last-update-row cegg-mb15">
                        <?php if ($item['extra']['sellingStatus']['bidCount'] !== ''): ?>
                            <div><?php _e('Bids:', 'content-egg-tpl'); ?> <?php echo $item['extra']['sellingStatus']['bidCount'] ?></div>
                        <?php endif; ?>

                        <?php if ($item['extra']['conditionDisplayName']): ?>
                            <div>
                                <?php _e('Item condition:', 'content-egg-tpl'); ?>
                                <?php echo $item['extra']['conditionDisplayName']; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($time_left): ?>
                            <div>
                                <?php _e('Time left:', 'content-egg-tpl'); ?>
                                <span <?php if (strstr($time_left, __('m', 'content-egg-tpl'))) echo 'class="text-danger"'; ?>><?php echo $time_left; ?></span>
                            </div>
                        <?php else: ?>
                            <div class='text-warning'>
                                <?php _e('Ended:', 'content-egg-tpl'); ?>
                                <?php echo date('M j, H:i', strtotime($item['extra']['listingInfo']['endTime'])); ?> <?php echo $item['extra']['listingInfo']['timeZone']; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($item['extra']['shippingInfo']['shippingType'] == 'Free'): ?>
                            <p class="muted"><?php _e('Free shipping', 'content-egg-tpl'); ?></p>
                        <?php endif; ?>

                        <?php if ($item['extra']['eekStatus']): ?>
                            <div class="muted"><?php _e('EEK:', 'content-egg-tpl'); ?> <?php _p($item['extra']['eekStatus']); ?></div>
                        <?php endif; ?>                   
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="cegg-mb25">
                        <?php if ($item['description']): ?>
                            <p><?php echo $item['description']; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>