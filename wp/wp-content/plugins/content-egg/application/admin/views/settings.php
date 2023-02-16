<?php
/*
 * Некоторые иконы Yusuke Kamiyamane. Доступно по лицензии Creative Commons Attribution 3.0.
 * @link: http://p.yusukekamiyamane.com
 */
?>

<?php if (\ContentEgg\application\Plugin::isFree() || \ContentEgg\application\Plugin::isInactiveEnvato()): ?>
    <div class="cegg-maincol">
    <?php endif; ?>
    <div class="wrap">
        <h2>
            <?php _e('Content Egg Settings', 'content-egg'); ?>
            <?php if (\ContentEgg\application\Plugin::isPro()): ?>
                <span class="cegg-pro-label">pro</span>
            <?php endif; ?>
        </h2>

        <?php $modules = \ContentEgg\application\components\ModuleManager::getInstance()->getConfigurableModules(); ?>
        <h2 class="nav-tab-wrapper">
            <a href="?page=content-egg" 
               class="nav-tab<?php if (!empty($_GET['page']) && $_GET['page'] == 'content-egg') echo ' nav-tab-active'; ?>">
                   <?php _e('General settings', 'content-egg'); ?>
            </a>
            <?php foreach ($modules as $m): ?>
                <?php $config = $m->getConfigInstance(); ?>
                <a href="?page=<?php echo esc_attr($config->page_slug()); ?>" 
                   class="nav-tab<?php if (!empty($_GET['page']) && $_GET['page'] == $config->page_slug()) echo ' nav-tab-active'; ?>">

                    <?php
                    if ($m->isActive() && $m->isDeprecated())
                        $status = 'deprecated';
                    elseif ($m->isActive())
                        $status = 'active';
                    else
                        $status = 'inactive';
                    ?>

                    <img src="<?php echo ContentEgg\PLUGIN_RES; ?>/img/status-<?php echo $status; ?>.png" />
                    <?php echo esc_html($m->getName()); ?>                    
                    <?php if ($m->isNew()): ?><img src="<?php echo ContentEgg\PLUGIN_RES; ?>/img/new.png" alt="New" title="New" /><?php endif; ?>                    
                </a>
            <?php endforeach; ?>
        </h2> 

        <div class="ui-sortable meta-box-sortables">
            <div class="postbox1">
                <div class="inside">

                    <div class="cegg-wrap">

                        <div class="cegg-maincol">

                            <h3>
                                <?php
                                if (!empty($_GET['page']) && $_GET['page'] == 'content-egg')
                                    _e('General settings', 'content-egg');
                                else
                                {
                                    echo \esc_html($header);
                                    if (!empty($module) && $docs_uri = $module->getDocsUri())
                                        echo sprintf(' (<small><a target="_blank" href="%s">' . __('User guide', 'content-egg') . '</a></small>)', $docs_uri);
                                }
                                ?>                
                            </h3>

                            <?php if (!empty($module) && $module->isDeprecated()): ?>
                                <div class="cegg-warning">

                                    <?php if ($module->getId() == 'Amazon'): ?>
                                        <?php echo __('WARNING:', 'content-egg'); ?>
                                        <?php echo sprintf(__('Amazon PA-API v4 <a target="_blank" href="%s"> is deprecated</a>.', 'content-egg'), 'https://webservices.amazon.com/paapi5/documentation/faq.html'); ?>
                                        <?php echo sprintf(__('Only <a target="_blank" href="%s">Content Egg Pro</a> has support for the new PA-API v5.', 'content-egg'), 'https://www.keywordrush.com/contentegg/pricing'); ?>
                                        <?php echo _e('Please', 'content-egg'); ?> <a target="_blank" href="https://ce-docs.keywordrush.com/modules/affiliate/amazon#why-amazon-module-is-not-available-in-ce-free-version"><?php _e('read more...', 'content-egg'); ?></a>
                                    <?php endif; ?>

                                    <?php if ($module->getId() != 'Amazon'): ?>
                                        <strong>
                                            <?php echo __('WARNING:', 'content-egg'); ?>
                                            <?php echo __('This module is deprecated', 'content-egg'); ?>
                                            (<a target="_blank" href="<?php echo \ContentEgg\application\Plugin::pluginDocsUrl(); ?>/modules/deprecatedmodules"><?php _e('what does this mean', 'content-egg'); ?></a>).
                                        </strong>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($module) && $requirements = $module->requirements()): ?>
                                <div class="cegg-warning">  
                                    <strong>
                                        <?php echo _e('WARNING:', 'content-egg'); ?>
                                        <?php _e('This module cannot be activated!', 'content-egg') ?>
                                        <?php _e('Please fix the following error(s):', 'content-egg') ?>
                                        <ul>
                                            <li><?php echo join('</li><li>', $requirements) ?></li>
                                        </ul>

                                    </strong>
                                </div>
                            <?php endif; ?>                            

                            <?php \settings_errors(); ?>   
                            <form action="options.php" method="POST">
                                <?php \settings_fields($page_slug); ?>
                                <table class="form-table">
                                    <?php //do_settings_fields($page_slug, 'default'); ?>
                                    <?php \do_settings_sections($page_slug); ?>									
                                </table>        
                                <?php \submit_button(); ?>
                            </form>

                        </div>

                        <div class="cegg-rightcol">
                            <div>
                                <?php
                                if (!empty($description))
                                    echo '<p>' . $description . '</p>';

                                if (!empty($api_agreement))
                                    echo '<div style="text-align: right;"><small><a href="' . \esc_attr($api_agreement) . '" target="_blank">' . __('Conditions', 'content-egg') . '</a></small></div>';
                                ?>

                                <?php if (!empty($module) && $module->isFeedModule() && $last_date = $module->getLastImportDateReadable()): ?>
                                    <ul>
                                        <li><?php echo sprintf(__('Last feed import: %s.'), $last_date); ?></li>
                                        <li><?php echo sprintf(__('Total products: %d.'), $module->getProductCount()); ?></li>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>   
    </div>


    <?php if (\ContentEgg\application\Plugin::isFree() || \ContentEgg\application\Plugin::isInactiveEnvato()): ?>
    </div>    
    <?php include('_promo_box.php'); ?>
<?php endif; ?>