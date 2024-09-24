<?php
/**
 * Build settings page
 *
 * @return void
 */
function SLR_Options_page()
{
    ?>
    <div class="wrap slider-checkbox sky-login-redirect-settings">
    <h1>Sky Login Redirect settings 🪃</h1>
        
        <?php settings_errors(); ?>  
        <?php SLR_Admin_tabs(); ?> 

    <form method="post" action="options.php">
    <div id="main-content" class="main">
        <div class="tab-content">
        
            <div id="tab-1" class="tab-pane active metatags">
        <?php
    settings_fields('metatags');
    do_settings_sections('metatags');
    ?>
            </div>
        
            <div id="tab-2" class="tab-pane assets">
        <?php
    settings_fields('assets');
    do_settings_sections('assets');
    ?>
            </div>
        
            <div id="tab-3" class="tab-pane analytics">
        <?php
    settings_fields('analytics');
    do_settings_sections('analytics');
    ?>
            </div>
        
            <div id="tab-4" class="tab-pane cache">
        <?php
    settings_fields('cache');
    do_settings_sections('cache');
    ?>
            </div>
        
            <div id="tab-5" class="tab-pane security-headers">
        <?php
    settings_fields('headers');
    do_settings_sections('headers');
    ?>
            </div>

            <div id="tab-6" class="tab-pane advanced">
        <?php
    settings_fields('advanced');
    do_settings_sections('advanced');
    ?>
            </div>

            <div id="tab-7" class="tab-pane agency">
        <?php
    settings_fields('agency');
    do_settings_sections('agency');
    ?>
            </div>

        </div>
        
        
        <div class="metabox-holder has-right-sidebar" id="sidebar">
    <?php
    SLR_Settings_Page_sidebar();

    SLR_Settings_Promo_sidebar();
    ?>
        </div> <!-- .metabox-holder -->
        </div>
    </form>
    </div>
        <?php
}


/**
 * Create admin tabs
 *
 * See: https://onedesigns.com/tutorials/separate-multiple-theme-options-pages-using-tabs/
 *
 * @param string $current current tab
 *
 * @return html
 */
function SLR_Admin_tabs($current = 'meta') // /!\: key for tab1
{
    $tabs = array(
        'meta' => 'Meta Tags',
        'assets' => 'Assets',
        'analytics' => 'Analytics',
        'cache' => 'Cache',
        'security_headers' => 'Headers',
        'advanced' => 'Advanced',
        //'agency' => 'Agency'
    );

    include_once 'lib/svg-icons.php';

    $links = array();
    $i = 1;
    foreach ($tabs as $tab => $name) {
        if ($tab == $current) {
            $links[] = "<li class='active' onclick='setTabNo(this)' id='{$i}'>{$icons[$tab]}<span><a href='#tab-{$i}'>{$name}</a></span></li>";
        } else {
            $links[] = "<li onclick='setTabNo(this)' id='{$i}'>{$icons[$tab]}<span><a href='#tab-{$i}'>{$name}</a></span></li>";
        }
        $i++;
    }
    echo '<ul class="nav nav-tabs">';
    foreach ($links as $link) {
        echo $link;
    }
    echo '</ul>';
}

/**
 * Build settings page sidebar
 *
 * @return html
 */
function SLR_Settings_Page_sidebar()
{
    // See: http://www.satoripress.com/2011/10/wordpress/plugin-development/clean-2-column-page-layout-for-plugins-70/
    ?>
    <div class="inner-sidebar utopique-submit">
        <div class="postbox">
        <h3>Actions</h3>
            <div class="inside">
            <?php submit_button(); ?>
            </div>
        </div>
    </div> <!-- .inner-sidebar -->
        <?php
}

/**
 * Promo box in sidebar
 *
 * @return html
 */
function SLR_Settings_Promo_sidebar()
{
    /* business users : bail early */
    if (Sky_Login_Redirect_fs()->is_plan('agency')) {
        //return;
    }

    /* define all plans */
    $features = [];

    $starter = [
        __('Local Google Analytics 4', 'flashspeed'),
        __('Custom code blocks', 'flashspeed'),
    ];

    $growth = [
        __('Caching and Page preloading', 'flashspeed'),
    ];

    $pro = [
        __('Security headers', 'flashspeed'),
        __('Content Security Policy', 'flashspeed'),
        __('Optimize resources', 'flashspeed'),
    ];

    $agency = [
        __('Advanced options for agencies and clients', 'flashspeed'),
        //__('Debugging management', 'flashspeed'),
        //__('Restrict plugin options for clients', 'flashspeed'),
        //__('Core, plugins, themes updates management', 'flashspeed'),
        //__('Email notifications management', 'flashspeed'),
    ];

    /* Free plan or non-paying user */
    if (Sky_Login_Redirect_fs()->is_not_paying() || Sky_Login_Redirect_fs()->is_free_plan()) {
        //$features = [...$starter, ...$growth, ...$pro, ...$agency];
        // PHP < 7.4
        $features = array_merge($starter, $growth, $pro, $agency);
    }

    /* Starter */
    if (Sky_Login_Redirect_fs()->is_plan('starter', true)) {
        //$features = [...$growth, ...$pro, ...$agency];
        // PHP < 7.4
        $features = array_merge($growth, $pro, $agency);
    }

    /* Growth */
    if (Sky_Login_Redirect_fs()->is_plan('growth', true)) {
        //$features = [...$pro, ...$agency];
        // PHP < 7.4
        $features = array_merge($pro, $agency);
    }

    /* Pro */
    if (Sky_Login_Redirect_fs()->is_plan('pro', true)) {
        //$features = [...$agency];
        // PHP < 7.4
        $features = $agency;
    }

    /* common features for all plans */
    $all_plans = [
        'Priority premium support'
    ];
    $features = array_merge($features, $all_plans);
    ?>
    <div class="inner-sidebar">
        <div class="postbox">
        <h3><?php _e('Upgrade your plan', 'flashspeed'); ?></h3>
            <div class="inside">
                <div id="promo-box" class="wp-core-ui"><h2>🚀</h2>
                <div id="buy"><a class="button" href="<?php echo Sky_Login_Redirect_fs()->get_upgrade_url(); ?>"><span class="trolley">🛒  </span> <?php _e('Upgrade', 'flashspeed'); ?></a></div>
                <p><strong><?php _e('And get access to:', 'flashspeed'); ?></strong></p>
                <ul>
                <?php
            foreach ($features as $f) {
                printf('<li>✔ ' . $f . '</li>');
            } ?>
                </ul>
            </div>
            </div>
        </div>
    </div> <!-- .inner-sidebar -->
        <?php
}
