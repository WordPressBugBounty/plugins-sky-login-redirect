<?php
/**
 * Plugin Name: Zen Settings
 * Plugin URI: https://utopiqe.net/zen-settings/
 * Description: Create beautiful WordPress settings pages.
 * Version: 1.0.4
 * Author: Utopique Plugins
 * Author URI: https://utopique.net/
 * License: SaaS
 * License URI: https://utopique.net/
 * Text Domain: zen-settings
 * Domain Path: /languages
 * PHP version 7
 *
 * @category Settings
 * @package  ZenSettings
 * @author   Utopique Plugins <support@utopique.net>
 * @license  SaaS https://utopique.net
 * @link     https://utopique.net
 */

namespace ZenSettings;

/**
 * Zen Settings constants
 *
 * @since 1.0.0
 */
if (!defined('ZEN_SETTINGS_VERSION')) {
    define('ZEN_SETTINGS_VERSION', '1.0.4');
}

/**
 * ZenSettings class.
 *
 * This class provides an easy way to create settings pages in WordPress and allows users to customize the appearance and functionality of a plugin or theme. It relies on the CodeMirror library to provide a syntax-highlighting code editor for CSS, HTML, and JavaScript code snippets.
 *
 * @category Settings
 * @package  ZenSettings
 * @author   Utopique Plugins <support@utopique.net>
 * @license  SasS https://utopique.net
 * @link     https://utopique.net
 */
class ZenSettings
{
    private $_namespace;
    private $_textdomain;
    private $_version;
    private $_page_title;
    private $_menu_title;
    private $_slug;
    public static $option_name;
    private $_callback;
    private $_icon;
    private $_position;
    private $_emoji;
    private $_freemius;
    private $_fs_upgrade;

    private $path;
    private $url;
    private $textdomain;

    private $_args;
    private $_menu_icon;
    private $_tabs;
    private $_icons;
    private $_features;

    public static $optionName;

    /**
     * Constructor
     *
     * @param array $args      args
     * @param array $menu_icon menu icon
     * @param array $tabs      tabs
     * @param array $icons     icons
     * @param array $features  features
     *
     * @return void
     */
    public function __construct(array $args, array $menu_icon, array $tabs, array $icons, array $features)
    {
        // $args
        $this->_namespace   = $args['namespace'] ?? '';
        $this->_textdomain  = $args['textdomain'] ?? '';
        $this->_version     = $args['version'] ?? '';
        $this->_page_title  = $args['page_title'] ?? '';
        $this->_menu_title  = $args['menu_title'] ?? '';
        $this->_slug        = $args['slug'] ?? '';
        $this->_callback    = $args['callback'] ?? '';
        $this->_icon        = $args['icon'] ?? '';
        $this->_position    = $args['position'] ?? '';
        $this->_emoji       = $args['emoji'] ?? '';
        $this->_freemius    = $args['freemius'] ?? '';
        $this->_fs_upgrade  = $args['fs_upgrade'] ?? '';

        // Assign array
        $this->_args = $args;
        $this->_menu_icon = $menu_icon;
        $this->_tabs = $tabs;
        $this->_icons = $icons;
        $this->_features = $features;

        // create the constant for our option name
        //define("ZEN_SETTINGS_PLUGIN_OPTION_NAME", $args['option_name']);
        //$this->optionName = $args['option_name'];
        self::$optionName = $args['option_name'];

        // Set default values for needed arguments
        $this->path = isset($args['path']) ? $args['path'] : plugin_dir_path(__FILE__);
        $this->url = isset($args['url']) ? $args['url'] : plugin_dir_url(__FILE__);
        $this->textdomain = 'zen-settings';

        // Register the text domain for localization
        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));

        // Register your plugin's initialization function
        add_action('init', array($this, 'init'));

        // Detect if Zen Settings is standalone or a dependency
        if (is_plugin_active('zen-settings/zen-settings.php')) {
            // ZenSettings is installed as a standalone plugin
            $this->path = WP_PLUGIN_DIR . '/zen-settings';
            $this->url  = plugins_url('', $this->path);
        }
    }

    /**
     * Init function to fire hooks
     *
     * @return void
     */
    public function init()
    {
        // Plugin initialization code
        // Use $this->namespace to namespace all the settings related code
        add_action('admin_menu', array($this, 'create_menu'));

        // enqueue styles and scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));

        // notices
        add_action('admin_notices', array($this, 'displayCustomNotices'));

        // include files
        $this->load_files();

        // Clear carbonade cache when the option array is updated
        add_action('update_option_' . self::getOptionName(), array($this, 'clear_carbonade_cache'));
    }

    /**
     * Custom notices
     *
     * @return void
     */
    public function displayCustomNotices()
    {
        \settings_errors();
    }

    /**
     * Get slug from out external plugins
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->_slug;
    }

    /**
     * Get path
     *
     * @return void
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get url
     *
     * @return void
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get textdomain
     *
     * @return void
     */
    public function getTextDomain()
    {
        return $this->textdomain;
    }

    /**
     * Get option_name from external plugins
     *
     * @return string
     */
    /*
    public function getOptionName()
    {
        //return ZEN_SETTINGS_PLUGIN_OPTION_NAME;
        return $this->optionName;
    }
    */
    public static function getOptionName()
    {
        return self::$optionName;
    }


    /**
     * Retrieve cached options from transient.
     *
     * This function retrieves the value for the specified key
     * from the cached options array, which is populated from the database
     * only once per page load for performance optimization.
     *
     * @param string $key  The key of the option to retrieve.
     * @param bool   $echo Optional. Whether to echo the value or return it. Default false (return value).
     *
     * @return mixed The value for the given key. If the key is not found, an empty string is returned.
     */
    public static function carbonade($key, $echo = false)
    {
        static $cached_options = array();

        //$option_name = $zen_settings->optionName;
        $option_name = self::$optionName;

        if (!isset($cached_options[$option_name])) {
            $cached_options[$option_name] = get_option($option_name);
        }

        $sanitized_key = sanitize_key($key);

        if ($echo) {
            echo esc_html($cached_options[$option_name][$sanitized_key] ?? '');
        } else {
            return $cached_options[$option_name][$sanitized_key] ?? '';
        }
    }

    /**
     * Clear the cached options when the option array is updated.
     *
     * This function should be hooked to the appropriate action or filter
     * that is triggered when the option array is updated. It clears the
     * cached options array, so the next call to carbonade will fetch the
     * updated values from the database.
     *
     * @return void
     */
    public function clear_carbonade_cache()
    {
        static $cached_options = array();

        //$option_name = ZEN_SETTINGS_PLUGIN_OPTION_NAME;
        $option_name = $this->optionName;  // Use the class property

        // Clear the cached options for the specified option name
        if (isset($cached_options[$option_name])) {
            unset($cached_options[$option_name]);
        }
    }

    /**
     * Load plugin text domain
     *
     * @return void
     */
    public function load_plugin_textdomain()
    {
        load_plugin_textdomain($this->getTextDomain(), false, $this->getUrl() . '/languages');
    }

    /**
     * Include all fields functions
     *
     * @return void
     */
    public function load_files()
    {
        include_once $this->getPath() . 'admin/fields.php';
        // Add more files here as needed
    }

    /**
     * Enqueue scripts
     *
     * @param string $hook page hook
     *
     * @return void
     */
    public function enqueue_scripts($hook)
    {
        wp_add_inline_style(
            $this->_menu_icon['parent'],
            $this->_menu_icon['style'] . $this->_menu_icon['animation']
        );

        $plugin_pages = [
            "toplevel_page_{$this->_slug}",
            "{$this->_slug}_page_{$this->_slug}-account",
            "{$this->_slug}_page_{$this->_slug}-contact",
            "{$this->_slug}_page_{$this->_slug}-pricing",
        ];

        // our code should only load on our plugin pages
        if ($hook === $plugin_pages[0]) {
            // CodeMirror CSS Editor
            $cm_css['codeEditor'] = wp_enqueue_code_editor(['type' => 'text/css']);
            wp_localize_script('jquery', 'cm_css', $cm_css);

            // CodeMirror JS Editor
            $cm_js['codeEditor'] = wp_enqueue_code_editor(['type' => 'text/html']);
            wp_localize_script('jquery', 'cm_js', $cm_js);

            // Load scripts
            wp_enqueue_script('wp-theme-plugin-editor');
            wp_enqueue_style('wp-codemirror');

            wp_enqueue_style(
                'zen-settings',
                $this->getUrl() . 'assets/css/elements.css',
                false,
                ZEN_SETTINGS_VERSION,
                'all'
            );
            wp_enqueue_style(
                'zen-settings-tabs',
                $this->getUrl() . 'assets/css/tabs.css',
                false,
                ZEN_SETTINGS_VERSION,
                'all'
            );
            wp_enqueue_script(
                'zen-settings-tabs',
                $this->getUrl() . '/assets/js/tabs.js',
                false,
                ZEN_SETTINGS_VERSION,
                true
            );
            wp_enqueue_script(
                'zen-settings',
                $this->getUrl() . '/assets/js/elements.js',
                false,
                ZEN_SETTINGS_VERSION,
                true
            );

            // remove WP emoji on our pages
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
            remove_action('admin_print_styles', 'print_emoji_styles');
        }
    }

    /**
     * Create menu
     *
     * @return null
     */
    public function create_menu()
    {
        add_menu_page(
            $this->_page_title,
            $this->_menu_title,
            'manage_options',
            $this->_slug,
            array( $this, $this->_callback ),
            $this->_icon,
            (int)$this->_position
        );
    }

    /**
     * Create admin tabs
     *
     * See: https://onedesigns.com/tutorials/separate-multiple-theme-options-pages-using-tabs/
     *
     * @return void
     */
    public function create_tabs()
    {
        $current = array_keys($this->_tabs)[0]; // Set $current to first $this->_tabs key
        $links = array();
        $i = 1;
        foreach ($this->_tabs as $tab => $name) {
            $active_class = $tab == $current ? 'active' : '';
            $links[] = sprintf(
                '<li class="%1$s" id="%2$s">%3$s<span><a href="#tab-%2$s">%4$s</a></span></li>',
                esc_attr($active_class),
                esc_attr($i),
                $this->_icons[$tab],
                esc_html($name)
            );
            $i++;
        }
        echo '<ul class="nav nav-tabs">';
        foreach ($links as $link) {
            echo $link; // can't use wp_kses_post() as it breaks SVGs
        }
        echo '</ul>';
    }

    /**
     * Create settings page sidebar
     *
     * @return html
     */
    public function create_sidebar()
    {
        // See: http://www.satoripress.com/2011/10/wordpress/plugin-development/clean-2-column-page-layout-for-plugins-70/
        ?>
    <div class="inner-sidebar zen-settings-submit">
        <div class="postbox">
            <h3>Actions</h3>
            <div class="inside">
            <input type="hidden" name="<?php echo $this->getOptionName() . '_form'; ?>" value="1">
            <?php wp_nonce_field(
                $this->getOptionName() . '_action',
                $this->getOptionName() . '_nonce'
            ); ?>
                <?php submit_button(); ?>
            </div>
        </div>
    </div> <!-- .inner-sidebar -->
        <?php
    }

    /**
     * Create promobox in sidebar
     *
     * @return void
     */
    public function create_promobox()
    {
        ?>
    <div class="inner-sidebar">
        <div class="postbox">
            <h3><?php _e('Upgrade your plan', $this->_textdomain); ?></h3>
            <div class="inside">
                <div id="promo-box" class="wp-core-ui"><h2>🚀</h2>
                    <div id="buy"><a class="button" href="<?php echo esc_url($this->_fs_upgrade); ?>"><span class="trolley">🛒  </span> <?php esc_html_e('Upgrade', $this->_textdomain); ?></a></div>
                    <p><strong><?php esc_html_e('And get access to:', $this->_textdomain); ?></strong></p>
                    <ul>
                <?php
                foreach ($this->_features as $f) {
                    printf('<li>✔ %s</li>', esc_html($f));
                } ?>
                    </ul>
                </div>
            </div>
        </div>
    </div> <!-- .inner-sidebar -->
        <?php
    }

    /**
     * Create settings page
     *
     * @return void
     */
    public function create_page()
    {
        $current_tab = array_keys($this->_tabs)[0];
        ?>
    <div class="wrap slider-checkbox zen-settings">

        <header class="zen-settings-header">
            <h1><?php echo esc_html("{$this->_page_title} {$this->_emoji}"); ?></h1>
        </header>

        <main class="zen-settings-content">
        <?php $this->create_tabs(); ?>
        <form method="post" action="options.php">
            <div id="main-content" class="main">
                <div class="tab-content">
        <?php
        $i = 1;
        foreach ($this->_tabs as $slug => $name) {
            $class = $slug === $current_tab ? 'active' : '';
            ?>
                    <div id="tab-<?php echo esc_attr($i); ?>" class="tab-pane <?php echo esc_attr($class); ?>">
            <?php
            settings_fields($this->_slug . "_$slug");       // MUST be unique
            do_settings_sections($this->_slug . "_$slug");  // MUST be unique
            ?>
                    </div>
            <?php
            $i++;
        }
        ?>
                </div>
                <div class="metabox-holder has-right-sidebar" id="sidebar">
        <?php
        // create sidebar
        $this->create_sidebar();

        // create promo metabox for the lower plans (exclude the highest plan)
        if (is_array($this->_features)          // must be array
            && is_countable($this->_features)   // must be countable
            && count($this->_features) > 1      // more than 1 element means we are on the highest plan
        ) {
            $this->create_promobox();
        }
        ?>
                </div> <!-- .metabox-holder -->
            </div>
        </form>
        </main>
    </div> <!-- .wrap -->
        <?php
    }

} // end of class
