<?php

/**
 * Plugin Class
 *
 * @author Alfi Rizka
 * @copyright Atomic55
 */
class Atomic55_Plugin
{

    public static $bootstrap;

    public static $basename;

    public static $path;

    public static $url;

    private static $option_name = 'atomic55_theme_customizer';

    private static $check_update_server = 'https://raw.github.com/avikaco/Atomic55-Theme-Toolkit/master/package.json';

    private static $default_options = array(
        'theme_supports' => array(),
        'disabled' => array(
            'sections' => array(),
            'controls' => array()
        ),
        'sections' => array(),
        'control2builtin_section' => array(),
        'priority' => array(
            'sections' => array(),
            'controls' => array()
        )
    );
    
    // --------------------------------------
    /**
     *
     * @author Alfi Rizka
     */
    public static function initialize($bootstrap)
    {
        self::$bootstrap = $bootstrap;
        self::$basename = plugin_basename(self::$bootstrap);
        self::$path = plugin_dir_path(self::$bootstrap);
        self::$url = plugin_dir_url(self::$bootstrap);
        
        self::hook();
        
        Atomic55_Settings::initialize();
        Atomic55_Customizer::initialize();
        Customizer_Hooks::initialize();
        Atomic55_Widgets::initialize();
    }
    
    // --------------------------------------
    /**
     * set plugin hook
     *
     * @author Alfi Rizka
     */
    private static function hook()
    {
        register_activation_hook(self::$bootstrap, array(
            __CLASS__,
            'activate'
        ));
        register_deactivation_hook(self::$bootstrap, array(
            __CLASS__,
            'deactivate'
        ));
        add_action('admin_init', array(
            __CLASS__,
            'admin_init'
        ));
        add_filter('plugin_action_links_' . self::$basename, array(
            __CLASS__,
            'plugin_action_links'
        ));
        add_filter('pre_set_site_transient_update_plugins', array(
            __CLASS__,
            'pre_set_site_transient_update_plugins'
        ));
        add_filter('plugins_api', array(
            __CLASS__,
            'plugins_api'
        ), 10, 3);
    }
    
    // --------------------------------------
    /**
     * hook admin init
     *
     * @author Alfi Rizka
     */
    public static function admin_init()
    {
        wp_register_style('atomic55-themeoptions-admin', self::$url . 'css/admin.css');
        wp_register_script('atomic55-themeoptions-admin.js', self::$url . 'js/admin.min.js', array(
            'jquery',
            'jquery-ui-draggable',
            'jquery-ui-sortable',
            'underscore'
        ), NULL, TRUE);
    }
    
    // --------------------------------------
    /**
     * hook plugin activated
     * used by hook: register_activation_hook
     *
     * @author Alfi Rizka
     */
    public static function activate()
    {
        // add initial option, add_option() will return false if option already exist, no problem.
        add_option(self::$option_name, self::$default_options);
    }
    
    // --------------------------------------
    /**
     * hook plugin deactivated
     * used by hook: register_deactivation_hook
     *
     * @author Alfi Rizka
     */
    public static function deactivate()
    {}
    // --------------------------------------
    /**
     * hook admin menu
     *
     * @author Alfi Rizka
     */
    public static function admin_menu()
    {
        $setting_hook = add_options_page('Atomic55 - Theme Settings', 'Theme Settings', 'manage_options', 'atomic55-theme-settings', array(
            __CLASS__,
            'options_manage'
        ));
        $theme_hook = add_theme_page('Atomic55 - Theme Options', 'Theme Options', 'manage_options', 'atomic55-theme-options', array(
            __CLASS__,
            'options_manage'
        ));
        
        add_action('admin_print_styles-' . $setting_hook, array(
            __CLASS__,
            'admin_print_style'
        ));
        add_action('admin_print_styles-' . $theme_hook, array(
            __CLASS__,
            'admin_print_style'
        ));
    }
    
    // --------------------------------------
    /**
     * Retrieve plugin options from database
     *
     * @return array
     * @author Alfi Rizka
     */
    public static function &options_get()
    {
        static $options;
        
        if (is_null($options)) {
            $options = get_option(self::$option_name);
            
            // merge default option with current option from database
            if (is_array($options)) {
                $options = shortcode_atts(self::$default_options, $options);
            } else {
                $options = self::$default_options;
            }
        }
        
        return $options;
    }

    /**
     * Save plugin options to database
     *
     * @return bool
     * @author Alfi Rizka
     */
    public static function options_save($new_options = array())
    {
        return update_option(self::$option_name, $new_options);
    }
    
    // --------------------------------------
    /**
     * add setting link to manage plugin page
     *
     * @author Alfi Rizka
     */
    public static function plugin_action_links($actions)
    {
        return array_merge($actions, array(
            'settings' => sprintf('<a href="%s">%s</a>', 'options-general.php?page=atomic55-theme-settings', __('Settings', AVI_i18n))
        ));
    }
    
    // --------------------------------------
    /**
     * Add our self-hosted description to the filter
     *
     * @param boolean $false            
     * @param array $action            
     * @param object $arg            
     * @return bool object
     * @author Alfi Rizka
     */
    public static function plugins_api($false, $action = '', $arg = '')
    {
        if ($arg->slug === self::$basename) {
            $response = wp_remote_get(self::$check_update_server);
            if (! is_wp_error($response) || wp_remote_retrieve_response_code($response) === 200) {
                $response = json_decode($response['body']);
                $response->sections = (array) $response->sections;
                return $response;
            }
        }
        return false;
    }
    
    // --------------------------------------
    /**
     * Check update
     *
     * @see http://wp.tutsplus.com/tutorials/plugins/a-guide-to-the-wordpress-http-api-automatic-plugin-updates/
     *
     * @author Alfi Rizka
     * @param OBJECT $transient            
     * @return stdClass
     */
    public static function pre_set_site_transient_update_plugins($transient)
    {
        $response = wp_remote_get(self::$check_update_server);
        
        if (! is_wp_error($response) || wp_remote_retrieve_response_code($response) === 200) {
            $response = (object) json_decode($response['body']);
            $plugin_info = get_plugin_data(self::$bootstrap);
            
            if ($response->version != $plugin_info['Version']) {
                $transient->response[self::$basename] = $response;
            }
        }
        
        return $transient;
    }
}

