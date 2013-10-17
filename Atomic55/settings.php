<?php

/**
 * Plugin Settings page class, accessible from WP admin > Settings > Theme Settings
 *
 * @author Alfi Rizka
 * @copyright Atomic55
 */
class Atomic55_Settings
{
    // @var $page page hook name
    private static $page;

    private static $option_theme_support;
    // @var register default/builtin image size
    private static $wp_builtin_image_size = array(
        'thumbnail' => 'Wordpress Thumbnail Size',
        'medium' => 'Wordpress Medium Size',
        'large' => 'Wordpress Large Size'
    );

    
    // --------------------------------------
    /**
     * Class constructor
     *
     * @author Alfi Rizka
     */
    public static function initialize()
    {
        self::$option_theme_support = get_option('a55_theme_support');
        self::hooks();
        
        // hack for AngularJS AJAX for WP,
        // TODO if you know better method, please let me know
        $json = (array) json_decode(file_get_contents("php://input"), TRUE);
        if (isset($json['action'])) {
            $_POST = $json;
        }
    }
    
    // --------------------------------------
    /**
     * Register hook for plugin, if you'r looking for customizer related hook, see customizer_hoooks.php
     *
     * @author Alfi Rizka
     */
    private static function hooks()
    {
        add_action('admin_menu', array(
            __CLASS__,
            'admin_menu'
        ));
        
        add_action('admin_notices', array(
            __CLASS__,
            'admin_notices'
        ));
        
        add_action('wp_ajax_atomic55_settings_theme_customizer', array(
            __CLASS__,
            'ajax_admin_theme_customizer'
        ));
        
        add_action('wp_ajax_atomic55_settings_theme', array(
            __CLASS__,
            'ajax_admin_theme_setting'
        ));
        add_action('wp_ajax_atomic55_theme_customcss', array(
            __CLASS__,
            'ajax_admin_theme_css'
        ));
        add_action('wp_ajax_atomic55_theme_reset', array(
            __CLASS__,
            'ajax_theme_reset'
        ));
        add_action('wp_ajax_atomic55_theme_branding', array(
            __CLASS__,
            'ajax_theme_branding'
        ));
        add_action('init', array(
            __CLASS__,
            'init'
        ), 1000);
        add_action('widgets_init', array(
            __CLASS__,
            'widgets_init'
        ));
        add_filter('image_size_names_choose', array(
            __CLASS__,
            'image_size_names_choose'
        ));
        
        add_action('admin_xml_ns', array(
            __CLASS__,
            'admin_xml_ns'
        ));
        add_action('a55_settings_enqueue_scripts', array(
            __CLASS__,
            'a55_settings_enqueue_scripts'
        ));
    }
    
    // --------------------------------------
    /**
     * Register admin menu
     *
     * @author Alfi Rizka
     */
    public static function admin_menu()
    {
        if (defined('ATOMIC55_DISABLE_THEME_SETTING_PAGE') && ATOMIC55_DISABLE_THEME_SETTING_PAGE === TRUE) {
            return;
        }
        
        self::$page = add_options_page('Atomic55 - Theme Settings', 'Theme Settings', 'manage_options', 'atomic55-theme-settings', array(
            __CLASS__,
            'page'
        ));
        
        add_action('admin_print_styles-' . self::$page, array(
            __CLASS__,
            'admin_print_style'
        ));
        add_action('load-' . self::$page, array(
            __CLASS__,
            'contextual_help'
        ));
    }
    
    // --------------------------------------
    /**
     * Initialize Navigation (theme menu) & image size,
     * register/unregister widget base on user settings
     *
     * @author Alfi Rizka
     */
    public static function init()
    {
        global $_wp_additional_image_sizes;
        
        // --- REGISTER/UNREGISTER MENU
        if (empty(self::$option_theme_support['menus']) === FALSE && is_array(self::$option_theme_support['menus']) === TRUE) {
            foreach (self::$option_theme_support['menus'] as $args) {
                if (isset($args['disabled']) && $args['disabled'] === TRUE) {
                    unregister_nav_menu($args['id']);
                } else {
                    register_nav_menu($args['id'], $args['label']);
                }
            }
        }
        
        // --- REGISTER/UNREGISTER IMAGE SIZE
        if (empty(self::$option_theme_support['imageSizes']) === FALSE && is_array(self::$option_theme_support['imageSizes']) === TRUE) {
            foreach (self::$option_theme_support['imageSizes'] as $args) {
                // don't register built in WP image size.
                if (isset(self::$wp_builtin_image_size[$args['id']])) {
                    continue;
                }
                
                if (isset($args['disabled']) && $args['disabled'] === TRUE) {
                    unset($_wp_additional_image_sizes[$args['id']]);
                } else {
                    add_image_size($args['id'], $args['width'], $args['height'], ! ! $args['crop']);
                }
            }
        }
    }
    
    // --------------------------------------
    /**
     * Initialize widget,
     * register/unregister widget base on user settings
     *
     * @author Alfi Rizka
     */
    public static function widgets_init()
    {
        if (empty(self::$option_theme_support['sidebars']) === FALSE && is_array(self::$option_theme_support['sidebars']) === TRUE) {
            foreach (self::$option_theme_support['sidebars'] as $args) {
                if (isset($args['disabled']) && $args['disabled'] === TRUE) {
                    unregister_sidebar($args['id']);
                } else {
                    register_sidebar($args);
                }
            }
        }
    }
    
    // --------------------------------------
    /**
     * add HTML name space and ngApp to HTML tag
     *
     * @author Alfi Rizka
     */
    public static function admin_xml_ns()
    {
        if (isset($_GET['tab']) === FALSE) {
            return;
        }
        if ($_GET['tab'] === 'theme-support') {
            echo 'ng-app="a55_theme_support" xmlns:ng="http://angularjs.org"';
        } elseif ($_GET['tab'] === 'settings') {
            echo 'ng-app="a55_theme_settings" xmlns:ng="http://angularjs.org"';
        } else {
            echo 'ng-app="a55_theme_settings" xmlns:ng="http://angularjs.org"';
        }
    }
    // --------------------------------------
    /**
     * Filter to add readable name to image size
     *
     * @author Alfi Rizka
     */
    public static function image_size_names_choose($sizes)
    {
        $options = get_option('a55_theme_support');
        if (isset($options['imageSizes']) === TRUE && empty($options['imageSizes']) === FALSE) {
            foreach ($options['imageSizes'] as $k => $v) {
                $sizes[$k] = empty($v['name']) === FALSE ? $v['name'] : $k;
            }
        }
        return $sizes;
    }
    
    // --------------------------------------
    /**
     * Enqueue CSS & JS for custom control in admin page
     *
     * @author Alfi Rizka
     */
    public static function a55_settings_enqueue_scripts()
    {
        wp_enqueue_script('a55-controls.js', Atomic55_Plugin::$url . 'js/controls.min.js', '', '');
    }
    
    // --------------------------------------
    /**
     * Enqueue JS & CSS for page theme customizer.
     *
     * @author Alfi Rizka
     */
    private static function _script_theme_customizer()
    {
        // required in Atomic55/customizer.php
        define('ATOMIC55_THEME_CUSTOMIZER_SETTING', TRUE);
        
        require_once (ABSPATH . WPINC . '/class-wp-customize-manager.php');
        
        $wp_customize = new WP_Customize_Manager();
        $theme_customizer = Atomic55_Plugin::options_get();
        $theme_customizer['control_available'] = array();
        $theme_customizer['predefined_section'] = array();
        $theme_customizer['defaults'] = array(
            'section' => Atomic55_Customizer::$default_section_args,
            'control' => Atomic55_Customizer::$default_control_args
        );
        $theme_customizer['defaults']['control']['setting'] = Atomic55_Customizer::$default_setting_args;
        
        do_action('customize_register', $wp_customize);
        
        // control field available
        $_fields = Atomic55_Customizer::fields();
        $_cache_control_args = array();
        foreach ($_fields as $key => $field) {
            list ($control_label, $control_class) = $field;
            if ($control_class === NULL) {
                $control_class = 'WP_Customize_Control';
            }
            
            if (isset($_cache_control_args[$control_class]) === FALSE && class_exists($control_class) === TRUE) {
                $control_args = get_class_vars($control_class);
                unset($control_args['manager'], $control_args['settings'], $control_args['json']);
                $_cache_control_args[$control_class] = array_merge(Atomic55_Customizer::$default_control_args, $control_args);
            } else {
                $_cache_control_args[$control_class] = Atomic55_Customizer::$default_control_args;
            }
            
            $_control = $_cache_control_args[$control_class];
            $_control['label'] = $control_label;
            $_control['type'] = $key;
            $_control['setting'] = Atomic55_Customizer::$default_setting_args;
            
            $theme_customizer['control_available'][$key] = $_control;
        }
        
        $theme_customizer['control_available'] = array_values($theme_customizer['control_available']);
        
        foreach ($wp_customize->sections() as $id => $section) {
            $section = (array) $section;
            if (isset($theme_customizer['sections'][$id]) === TRUE) {
                $section['editable'] = $theme_customizer['sections'][$id]['editable'] === TRUE;
                $section['is_theme_option'] = $theme_customizer['sections'][$id]['is_theme_option'] === TRUE;
            } else {
                // builtin section not save to $theme_customizer['sections']
                $section['editable'] = FALSE;
            }
            
            // important, must integer, if not will display with wrong order
            $section['priority'] = intval($section['priority']);
            
            $section['disabled'] = in_array($id, $theme_customizer['disabled']['sections']) === TRUE;
            // not disabled by user, check from theme support
            if ($section['disabled'] === FALSE && empty($section['theme_supports']) === FALSE) {
                $theme_supports = array_map('trim', explode(',', $section['theme_supports']));
                $section['disabled'] = current_theme_supports($theme_supports[0]) === FALSE;
                $theme_supports_count = count($theme_supports);
                if ($theme_supports_count > 1) {
                    for ($x = 1; $x < $theme_supports_count; $x ++) {
                        $section['disabled'] = $section['disabled'] || current_theme_supports($theme_supports[$x]) === FALSE;
                    }
                }
            }
            
            // remove never use variable
            unset($section['manager']);
            
            // assign to active section
            $theme_customizer['sections'][$id] = array_merge(Atomic55_Customizer::$default_section_args, $section);
        }
        
        foreach ($wp_customize->controls() as $id => $control) {
            if (isset($control->editable) === TRUE) {
                $control->editable = $control->editable !== FALSE;
            } else {
                $control->editable = FALSE;
            }
            
            $control = get_object_vars($control);
            unset($control['manager'], $control['settings'], $control['setting']->manager, $control['setting']->sanitize_callback, $control['setting']->sanitize_js_callback, $control['json']);
            
            if (is_object($control['setting'])) {
                $control['setting'] = get_object_vars($control['setting']);
            } else {
                $control['setting'] = array();
            }
            $control['disabled'] = in_array($id, $theme_customizer['disabled']['controls']) === TRUE;
            // important, must integer, if not will display with wrong order
            $control['priority'] = intval($control['priority']);
            // convert choices to array numeric
            if(empty($control['choices']) === FALSE) {
                $new_choices = array();
                foreach ($control['choices'] as $_choice_id => $_choice) {
                    $new_choices[] = array($_choice_id, $_choice);
                }
                $control['choices'] = $new_choices;
            }
            
            $theme_customizer['sections'][$control['section']]['controls'][] = $control;
        }
        
        $theme_customizer['sections'] = array_values($theme_customizer['sections']);
        
        // remove some variables, not used in JS
        unset($theme_customizer['control2builtin_section'], $theme_customizer['disabled'], $theme_customizer['theme_supports']);
        
        // enqueue js & css
        wp_register_script('a55-angularjs', Atomic55_Plugin::$url . 'js/vendors/angular.min.js', NULL, '1.0.7', TRUE);
        wp_register_script('angular.sortable', Atomic55_Plugin::$url . 'js/vendors/angular.sortable.js', NULL, '1.0.7', TRUE);
        wp_register_script('angular.dragdrop', Atomic55_Plugin::$url . 'js/vendors/angular.dragdrop.min.js', NULL, '1.0.7', TRUE);
        wp_register_script('a55-codemirror', Atomic55_Plugin::$url . 'js/vendors/codemirror.min.js', '', '', TRUE);
        wp_register_script('a55-codemirror-css-hint', Atomic55_Plugin::$url . 'js/vendors/codemirror-css-hint.js', '', '', TRUE);
        wp_register_script('a55-ui-codemirror', Atomic55_Plugin::$url . 'js/vendors/ui-codemirror.js', array(
            'a55-codemirror',
            'a55-codemirror-css-hint'
        ), '', TRUE);
        wp_register_script('a55-ui-bootstrap-tpls', Atomic55_Plugin::$url . 'js/vendors/ui-bootstrap-tpls-0.5.0.min.js', array(
            'a55-angularjs'
        ), '0.5.0', TRUE);
        wp_register_script('a55-wp-toolkit-theme-support', Atomic55_Plugin::$url . 'js/theme-customizer.js', array(
            'underscore',
            'jquery-ui-draggable',
            'jquery-ui-sortable',
            'a55-angularjs',
            'a55-ui-codemirror',
            'angular.sortable',
            'angular.dragdrop',
            'a55-ui-bootstrap-tpls'
        ), '1', TRUE);
        wp_localize_script('a55-wp-toolkit-theme-support', 'a55_theme_options', $theme_customizer);
        wp_enqueue_script('a55-wp-toolkit-theme-support');
        wp_enqueue_style('a55-codemirror', Atomic55_Plugin::$url . 'css/vendors/codemirror.css');
        wp_enqueue_style('a55-codemirror-eclipse', Atomic55_Plugin::$url . 'css/vendors/eclipse.css');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_style('wp-color-picker');
        
        // run action a55_settings_enqueue_scripts
        do_action('a55_settings_enqueue_scripts');
    }
    
    // --------------------------------------
    /**
     * Enqueue JS & CSS for page theme setting.
     *
     * @author Alfi Rizka
     */
    private static function _script_theme_setting()
    {
        $option_name = 'a55_theme_branding';
        $options = get_option($option_name);
        $data = array_merge(array(
            'login' => '',
            'admin_bar' => '',
            'admin_header' => '',
            'admin_footer' => '',
            'favicon' => ''
        ), (array) $options);
        
        // enqueue js & css
        wp_register_script('a55-angularjs', Atomic55_Plugin::$url . 'js/vendors/angular.min.js', NULL, '1.0.7');
        wp_register_script('a55-codemirror', Atomic55_Plugin::$url . 'js/vendors/codemirror.min.js');
        wp_register_script('a55-codemirror-xml', Atomic55_Plugin::$url . 'js/vendors/codemirror-mode-xml.js');
        wp_register_script('a55-codemirror-html-hint', Atomic55_Plugin::$url . 'js/vendors/codemirror-html-hint.js');
        wp_register_script('a55-ui-codemirror', Atomic55_Plugin::$url . 'js/vendors/ui-codemirror.js', array(
            'a55-codemirror',
            'a55-codemirror-xml',
            'a55-codemirror-html-hint'
        ));
        wp_register_script('a55-ui-bootstrap-tpls', Atomic55_Plugin::$url . 'js/vendors/ui-bootstrap-tpls-0.5.0.min.js', array(
            'a55-angularjs'
        ), '0.5.0');
        wp_register_script('a55-wp-toolkit-theme-support', Atomic55_Plugin::$url . 'js/theme-settings.js', array(
            'a55-angularjs',
            'a55-ui-codemirror',
            'a55-ui-bootstrap-tpls'
        ));
        wp_localize_script('a55-wp-toolkit-theme-support', 'a55_theme_options', $data);
        wp_enqueue_script('a55-wp-toolkit-theme-support');
        wp_enqueue_style('a55-codemirror', Atomic55_Plugin::$url . 'css/vendors/codemirror.css');
        wp_enqueue_style('a55-codemirror-eclipse', Atomic55_Plugin::$url . 'css/vendors/eclipse.css');
        wp_enqueue_media();
    }
    
    // --------------------------------------
    /**
     * Enqueue JS & CSS for page Theme support
     *
     * @author Alfi Rizka
     */
    private static function _script_theme_support()
    {
        global $wp_registered_sidebars, $_wp_additional_image_sizes, $_wp_registered_nav_menus;
        
        $data = array(
            'default_args' => array(),
            'menus' => array(),
            'sidebars' => array(),
            'imageSizes' => array()
        );
        
        $options = get_option('a55_theme_support');
        if ($options === FALSE) {
            $options = $data;
        }
        /**
         * Menu yang dibuat dari code tidak perlu disave, bagaimana cara men-disable?
         */
        // MENUS
        if (is_array($_wp_registered_nav_menus)) {
            foreach ($_wp_registered_nav_menus as $k => $v) {
                if (isset($options['menus'][$k])) {
                    $data['menus'][$k] = $options['menus'][$k];
                } else {
                    $data['menus'][$k] = array(
                        'id' => $k,
                        'label' => $v,
                        'disabled' => FALSE,
                        'builtin' => TRUE
                    );
                }
            }
            foreach ($options['menus'] as $k => $v) {
                if ($v['disabled'] === TRUE) {
                    $data['menus'][$k] = $v;
                }
            }
        }
        // SIDEBARS
        if (is_array($wp_registered_sidebars)) {
            foreach ($wp_registered_sidebars as $k => $v) {
                $data['sidebars'][$k] = $v;
                if (isset($options['sidebars'][$k]) === FALSE) {
                    // sidebar NOT created with this plugin, maybe with code or other plugin.
                    $data['sidebars'][$k]['disabled'] = FALSE;
                    $data['sidebars'][$k]['builtin'] = TRUE;
                }
            }
            foreach ($options['sidebars'] as $k => $v) {
                if ($v['disabled'] === TRUE) {
                    $data['sidebars'][$k] = $v;
                }
            }
        }
        
        // don't change this, this must follow WP default args
        $data['default_args']['sidebars'] = array(
            'name' => 'Untitiled Sidebar',
            'id' => '',
            'description' => '',
            'class' => '',
            'before_widget' => '<li id="%1$s" class="widget %2$s">',
            'after_widget' => '</li>',
            'before_title' => '<h2 class="widgettitle">',
            'after_title' => '</h2>'
        );
        
        // IMAGE SIZES
        if (is_array($_wp_additional_image_sizes) === FALSE) {
            $_wp_additional_image_sizes = array();
        }
        
        $humanize_arr = apply_filters('image_size_names_choose', self::$wp_builtin_image_size);
        foreach (self::$wp_builtin_image_size as $k => $v) {
            $_wp_additional_image_sizes[$k] = array(
                'width' => get_option($k . '_size_w'),
                'height' => get_option($k . '_size_h'),
                'crop' => ! ! get_option($k . '_crop')
            );
        }
        // end of default builtin image size
        
        foreach ($_wp_additional_image_sizes as $k => $v) {
            $v['id'] = $k;
            $v['name'] = isset($humanize_arr[$k]) ? $humanize_arr[$k] : NULL;
            $v['disabled'] = $v['builtin'] = $v['protected'] = FALSE;
            $data['imageSizes'][$k] = $v;
            if (isset($options['imageSizes'][$k]) === FALSE) {
                // image size NOT created with this plugin, maybe with code or other plugin.
                $data['imageSizes'][$k]['disabled'] = FALSE;
                $data['imageSizes'][$k]['builtin'] = TRUE;
                $data['imageSizes'][$k]['protected'] = isset(self::$wp_builtin_image_size[$k]);
            }
        }
        foreach ($options['imageSizes'] as $k => $v) {
            if ($v['disabled'] === TRUE) {
                $data['imageSizes'][$k] = $v;
            }
        }
        
        // enqueue js & css
        wp_register_script('a55-angularjs', Atomic55_Plugin::$url . 'js/vendors/angular.min.js', NULL, '1.0.7');
        wp_register_script('a55-ui-codemirror', Atomic55_Plugin::$url . 'js/vendors/ui-codemirror.js', array(
            'a55-angularjs'
        ));
        wp_register_script('a55-ui-bootstrap-tpls', Atomic55_Plugin::$url . 'js/vendors/ui-bootstrap-tpls-0.5.0.min.js', array(
            'a55-angularjs'
        ), '0.5.0');
        wp_register_script('a55-wp-toolkit-theme-support', Atomic55_Plugin::$url . 'js/theme-support.js', array(
            'underscore',
            'a55-ui-bootstrap-tpls'
        ));
        wp_localize_script('a55-wp-toolkit-theme-support', 'a55_theme_options', $data);
        wp_enqueue_script('a55-wp-toolkit-theme-support');
    }
    
    // --------------------------------------
    /**
     *
     * @author Alfi Rizka
     */
    public static function admin_print_style()
    {
        wp_enqueue_style('atomic55-themeoptions-admin');
        
        if (isset($_GET['tab']) === FALSE) {
            self::_script_theme_customizer();
        } elseif ($_GET['tab'] === 'theme-support') {
            self::_script_theme_support();
        } elseif ($_GET['tab'] === 'settings') {
            self::_script_theme_setting();
        } else {
            self::_script_theme_customizer();
        }
    }
    
    // --------------------------------------
    /**
     * Reset plugin settings
     *
     * @author Alfi Rizka
     */
    public static function ajax_theme_reset()
    {
        $allow_reset = array(
            'customizer' => array(
                'a55_customizer_css',
                'atomic55_theme_customizer'
            ),
            'theme support' => array(
                'a55_theme_support'
            ),
            'theme branding' => array(
                'a55_theme_branding'
            ),
            'all' => array(
                'a55_remove_wp_meta',
                'a55_js_bottom',
                'a55_remove_adminbar',
                'a55_customizer_last_built',
                'a55_excerpt_readmore_text',
                'a55_excerpt_length'
            )
        );
        
        if (isset($allow_reset[$_POST['reset']]) === FALSE) {
            die('{"error": true,"err_msg":"ERROR: Missing required variable"}');
        }
        
        if (current_user_can('manage_options')) {
            if ($_POST['reset'] === 'all') {
                foreach ($allow_reset as $reset) {
                    for ($i = 0, $j = count($reset); $i < $j; $i ++) {
                        delete_option($reset[$i]);
                    }
                }
            } else {
                $reset = $allow_reset[$_POST['reset']];
                for ($i = 0, $j = count($reset); $i < $j; $i ++) {
                    delete_option($reset[$i]);
                }
            }
            
            die('{"error": false}');
        }
        
        die('{"error": true,"err_msg":"ERROR: You have no capability to delete plugin option"}');
    }
    // --------------------------------------
    /**
     * Save theme custom css
     *
     * @author Alfi Rizka
     */
    public static function ajax_theme_branding()
    {
        // fix magic quote
        Atomic55_Tools::fix_magic_quote($_POST);
        
        // remove action
        unset($_POST['action']);
        
        // TODO validate data.
        
        // save data to option
        update_option('a55_theme_branding', $_POST);
        
        // send response
        die('{"error": false}');
    }
    
    // --------------------------------------
    /**
     * Save theme custom css
     *
     * @author Alfi Rizka
     */
    public static function ajax_admin_theme_css()
    {
        $response = array(
            'error' => FALSE
        );
        
        // fix magic quote
        Atomic55_Tools::fix_magic_quote($_POST);
        
        if (isset($_POST['css']) === FALSE) {
            $response['error'] = TRUE;
            $response['err_msg'] = 'ERROR: Missing required variable.';
            die(json_encode($response));
        }
        
        update_option('a55_customizer_css', $_POST['css']);
        update_option('a55_customizer_last_built', gmdate('D, d M Y H:i:s') . ' GMT');
        
        die(json_encode($response));
    }
    
    // --------------------------------------
    /**
     * Save theme setting (menu, sidebar & image size)
     *
     * @author Alfi Rizka
     */
    public static function ajax_admin_theme_setting()
    {
        $response = array();
        
        // fix magic quote
        Atomic55_Tools::fix_magic_quote($_POST);
        
        // remove not use variable
        unset($_POST['action']);
        
        // FIXME do validation before save to option
        
        // Image size
        if (empty($_POST['imageSizes']) === FALSE) {
            foreach (self::$wp_builtin_image_size as $k => $v) {
                if (isset($_POST['imageSizes'][$k])) {
                    update_option($k . '_size_w', intval($_POST['imageSizes'][$k]['width']));
                    update_option($k . '_size_h', intval($_POST['imageSizes'][$k]['height']));
                    update_option($k . '_crop', intval($_POST['imageSizes'][$k]['crop']));
                    // don't save builtin size
                    unset($_POST['imageSizes'][$k]);
                }
            }
        }
        
        update_option('a55_theme_support', $_POST);
        
        die(json_encode($_POST));
    }
    
    // --------------------------------------
    /**
     * Save theme customizer setting.
     * Loop to all sections, check is section is editable, if yes then check what control is disabled/new, if no then add entire
     *
     * NOTE: please remember, when posting data type boolean, TRUE === '1' (string one), FALSE === '0' (string zero)
     *
     * @author Alfi Rizka
     */
    public static function ajax_admin_theme_customizer()
    {
        $_cache_control_args = array();
        $_control_class = Atomic55_Customizer::fields();
        
        // fix magic quote
        Atomic55_Tools::fix_magic_quote($_POST);
        
        if (is_array($_POST['sections']) === FALSE) {
            die('{"error":true,"err_msg":"ERROR: Missing required variables."}');
        }
        
        // initialize option data
        $options = Atomic55_Plugin::options_get();
        
        // reset some option data
        $options['theme_supports'] = array();
        $options['sections'] = array();
        $options['control2builtin_section'] = array();
        
        // disable section & control
        $options['disabled']['sections'] = array();
        $options['disabled']['controls'] = array();
        // priority
        $options['priority']['sections'] = array();
        $options['priority']['controls'] = array();
        
        // read post sections
        for ($i = 0, $j = count($_POST['sections']); $i < $j; $i ++) {
            $section = $_POST['sections'][$i];
            $section_id = $section['id'];
            $section['is_theme_option'] = (isset($section['is_theme_option']) && $section['is_theme_option'] == '1');
            $section['editable'] = (isset($section['editable']) && $section['editable'] == '1');
            $section['disabled'] = (isset($section['disabled']) && $section['disabled'] == '1');
            
            if ($section['disabled'] === TRUE) {
                $options['disabled']['sections'][] = $section_id;
                continue;
            }
            
            // theme support
            if (empty($section['theme_supports']) === FALSE) {
                $options['theme_supports'][] = trim($section['theme_supports']);
            }
            
            if ($section['editable'] === FALSE) {
                /* predefined section */
                // save current increment
                $options['priority']['sections'][$section_id] = intval($section['priority']);
            } else {
                /* user defined section */
                $options['sections'][$section_id] = shortcode_atts(Atomic55_Customizer::$default_section_args, $section);
                $options['sections'][$section_id]['controls'] = array();
            } // end user defined section
              
            // save controls
            if (is_array($section['controls']) === TRUE && empty($section['controls']) === FALSE) {
                for ($k = 0, $l = count($section['controls']); $k < $l; $k ++) {
                    $control_args = _a55_build_control_data($section_id, $section['controls'][$k]);
                    $control_id = $control_args['id'];
                    
                    // if not editable
                    if ($control_args['editable'] === FALSE) {
                        if ($control_args['disabled'] === TRUE) {
                            $options['disabled']['controls'][] = $control_id;
                        } else {
                            $options['priority']['controls'][$control_id] = array(
                                $section_id,
                                $control_args['priority']
                            );
                        }
                        // skip save process, uneditable control will not save to WP option
                        continue;
                    } // end if not editable control
                    
                    if ($section['editable'] === TRUE) {
                        $options['sections'][$section_id]['controls'][$control_id] = $control_args;
                    } else {
                        // add to control to builtin section
                        $options['control2builtin_section'][$control_id] = $control_args;
                    }
                }
            }
        } // end for sections
          
        // theme support
        if (empty($options['theme_supports']) === FALSE) {
            $options['theme_supports'] = array_values(array_unique($options['theme_supports']));
        }
        
        // save option to database
        Atomic55_Plugin::options_save($options);
        $options['error'] = headers_sent();
        die(json_encode($options));
    }
    
    
    // --------------------------------------
    /**
     *
     * @author Alfi Rizka
     */
    public static function contextual_help()
    {
        $screen = get_current_screen();
        
        if (isset($_GET['tab']) === FALSE) {
            $_GET['tab'] = '';
        }
        
        if ($_GET['tab'] === 'theme-support') {
            $screen->set_help_sidebar(Atomic55_Tools::help('setting-sidebar'));
            $screen->add_help_tab(array(
                'id' => 'A55-theme-customizer-qa',
                'title' => __('FAQ', ATOMIC_i18n),
                'content' => Atomic55_Tools::help('setting-faq')
            ));
        } elseif ($_GET['tab'] === 'settings') {
            $screen->set_help_sidebar(Atomic55_Tools::help('setting-sidebar'));
            $screen->add_help_tab(array(
                'id' => 'A55-theme-customizer-qa',
                'title' => __('FAQ', ATOMIC_i18n),
                'content' => Atomic55_Tools::help('setting-faq')
            ));
        } else {
            $screen->set_help_sidebar(Atomic55_Tools::help('setting-sidebar'));
            $screen->add_help_tab(array(
                'id' => 'A55-theme-customizer-qa',
                'title' => __('FAQ', ATOMIC_i18n),
                'content' => Atomic55_Tools::help('setting-faq')
            ));
        }
        
        $screen->add_help_tab(array(
            'id' => 'A55-recomended-plugin',
            'title' => __('Recomended Plugin', ATOMIC_i18n),
            'content' => Atomic55_Tools::help('recomended-plugin')
        ));
    }
    
    // --------------------------------------
    /**
     *
     * @author Alfi Rizka
     */
    public static function admin_notices()
    {
        // if have POST variable, then don't show admin notice
        if (empty($_POST) == FALSE) {
            return;
        }
        
        $screen = get_current_screen();
        $is_my_page = $screen->id == self::$page;
        if ($is_my_page == TRUE && isset($_GET['saved']) == TRUE) {
            echo '<div id="EV-warning" class="updated fade"><p>' . __('Option saved.', ATOMIC_i18n) . '</p></div>';
            return;
        }
    }
    
    // --------------------------------------
    /**
     *
     * @author Alfi Rizka
     */
    public static function page()
    {
        include_once Atomic55_Plugin::$path . 'page/admin.settings.php';
    }
}
