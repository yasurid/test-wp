<div class="wrap">

    <h2>Content Egg <?php _e('License', 'content-egg') ?></h2>

    <?php if (\ContentEgg\application\Plugin::isActivated()): ?>
        <br>
        <?php _e('Plugin is activated', 'content-egg'); ?>
    <?php endif; ?>    


    <?php settings_errors(); ?>
    <form action="options.php" method="POST">
        <?php settings_fields($page_slug); ?>
        <table class="form-table">
            <?php do_settings_fields($page_slug, 'default'); ?>
        </table>
        <?php submit_button(); ?>
    </form>

    <?php if (\ContentEgg\application\Plugin::isEnvato() && \ContentEgg\application\Plugin::isActivated()): ?>
        <h2><?php _e('Deactivate license', 'content-egg'); ?></h2>
        <?php _e('You can transfer your license to another domain.', 'content-egg'); ?>
        <?php _e('After deactivating license, you must deactivate and delete Content Egg from current domain to use plugin on other domain.', 'content-egg'); ?>
        <br>
        <br>
        <form action="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=content-egg-lic'); ?>" method="POST">
            <input type="hidden" name="cmd" id="cmd" value="lic_reset"  />            
            <input type="hidden" name="nonce_reset" value="<?php echo \wp_create_nonce('license_reset'); ?>"/>
            <input type="submit" name="submit2" id="submit2" class="button submitdelete deletion" value="<?php _e('Deactivate license', 'content-egg'); ?>"  />            
        </form>
    <?php endif; ?>    
</div>