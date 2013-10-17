<?php
require_once Atomic55_Plugin::$path . 'Atomic55/customizer_helper.php';

/**
 * Theme Customizer
 *
 * @author Alfi Rizka
 * @copyright Atomic55
 */
class Atomic55_Customizer
{

    public static $default_section_args = array(
        'id' => 'untitled_theme_section',
        'title' => 'Untitled Theme Section',
        'priority' => 300,
        'description' => '',
        'capability' => '',
        'theme_supports' => '',
        'is_theme_option' => FALSE,
        'editable' => TRUE,
        'disabled' => FALSE,
        'controls' => array()
    );

    public static $default_setting_args = array(
        'default' => '',
        'transport' => 'refresh', // options: refresh (default) | postMessage
        'type' => 'theme_mod', // options: theme_mod (default) | option
        'capability' => 'edit_theme_options'
    );

    public static $default_control_args = array(
        'id' => '',
        'label' => 'Untitled Control',
        'type' => 'text',
        'section' => '',
        'setting' => array(),
        'priority' => 10,
        'choices' => array(),
        'editable' => TRUE,
        'disabled' => FALSE
    );

    private static $customize = array();

    private static $sections = array();

    private static $control_to_other_section = array();

    private static $cached_var = array();

    /**
     * I think in next version we have to add:
     * - Category/taxonomy selector
     * - Advance post/page/post-type selector
     *
     * @var Array
     */
    private static $field_type = array(
        'text' => array(
            'Text',
            NULL
        ),
        'checkbox' => array(
            'Checkbox',
            NULL
        ),
        'radio' => array(
            'Radio',
            NULL
        ),
        'select' => array(
            'Select',
            NULL
        ),
        'dropdown-pages' => array(
            'Dropdown Pages',
            NULL
        ),
        'color' => array(
            'Color',
            'WP_Customize_Color_Control'
        ),
        'image' => array(
            'Image',
            'WP_Customize_Image_Control'
        ),
        // 'background-image' => array('Background Image', 'WP_Customize_Background_Image_Control'),
        // 'header-image' => array('Header Image', 'WP_Customize_Header_Image_Control'),
        'upload' => array(
            'Upload',
            'WP_Customize_Upload_Control'
        ),
        'atomic55_textarea' => array(
            'Textarea',
            'Atomic55_Customizer_Textarea'
        ),
        'atomic55_range' => array(
            'Range Slider',
            'Atomic55_Customizer_Range'
        ),
        'atomic55_map' => array(
            'Map',
            'Atomic55_Customizer_Map'
        )
    );
    
    // --------------------------------------
    public static function initialize()
    {
        self::hooks();
    }
    
    // --------------------------------------
    /**
     * private method for registering hooks
     *
     * @author Alfi Rizka
     */
    private static function hooks()
    {
        add_action('customize_register', array(
            __CLASS__,
            'customize_register'
        ), 1000);
        
        add_action('customize_controls_print_styles', array(
            __CLASS__,
            'customize_controls_print_styles'
        ));
    }
    
    // --------------------------------------
    /**
     * register theme customizer
     * used by hook: customize_register
     *
     * @author Alfi Rizka
     */
    public static function customize_register($wp_customize)
    {
        $section_args = $setting_args = $control_args = array();
        
        do_action('a55_customizer_init');
        
        $options = Atomic55_Plugin::options_get();
        if (empty($options['sections']) === FALSE) {
            $options['sections'] = array_merge(self::$sections, $options['sections']);
        } else {
            $options['sections'] = self::$sections;
        }
        
        foreach ($options['sections'] as $section_id => $settings) {
            if (defined('ATOMIC55_THEME_CUSTOMIZER_SETTING') === FALSE && in_array($section_id, $options['disabled']['sections']) === TRUE) {
                continue;
            }
            
            // register section
            $settings['id'] = $section_id;
            $section_args = shortcode_atts(self::$default_section_args, $settings);
            $wp_customize->add_section($section_id, $section_args);
            
            if (is_array($settings['controls']) === TRUE) {
                foreach ($settings['controls'] as $control_id => $control) {
                    self::_register_control($wp_customize, $section_id, $control_id, $control);
                }
            }
        }
        
        // Register control to builtin section
        $options['control2builtin_section'] = array_merge_recursive(self::$control_to_other_section, $options['control2builtin_section']);
        foreach ($options['control2builtin_section'] as $control_id => $control) {
            if (isset($control[1]['section'])) {}
            self::_register_control($wp_customize, $control['section'], $control_id, $control);
        }
        
        // disable section/control only if not in setting page
        if (defined('ATOMIC55_THEME_CUSTOMIZER_SETTING') === FALSE) {
            // disable builtin section, predefined section
            foreach ($options['disabled']['sections'] as $disabled) {
                $wp_customize->remove_section($disabled);
            }
            
            // disable builtin control
            foreach ($options['disabled']['controls'] as $disabled) {
                $wp_customize->remove_control($disabled);
                $wp_customize->remove_setting($disabled);
            }
        }
        
        /*
         * NOTE: $options['priority'] only save priority data for builtin sections & controls
         */
        // fix builtin section priority,
        if (empty($options['priority']['sections']) === FALSE) {
            foreach ($options['priority']['sections'] as $section_id => $priority) {
                $section = $wp_customize->get_section($section_id);
                if ($section) {
                    $section->priority = $priority;
                }
            }
        }
        
        // fix builtin control priority
        if (empty($options['priority']['controls']) === FALSE) {
            foreach ($options['priority']['controls'] as $control_id => $ss) {
                list ($section_id, $priority) = $ss;
                $section = $wp_customize->get_section($section_id);
                if ($section) {
                    $control = $wp_customize->get_control($control_id);
                    if ($control) {
                        $control->priority = $priority;
                    }
                }
            }
        }
    }
    
    // --------------------------------------
    /**
     * Private method to register control,
     *
     * @see self::customize_register()
     *
     * @author Alfi Rizka
     */
    private static function _register_control($wp_customize, $section_id, $control_id, $control)
    {
        $_type = str_replace('atomic55_', '', $control['type']);
        $control_args = $control;
        $control_args['section'] = $section_id;
        $control_args['id'] = $control_args['settings'] = $control_id;
        if (isset($control['setting'])) {
            $setting_args = $control['setting'];
        } else {
            $setting_args = shortcode_atts(self::$default_setting_args, $control);
        }
        
        // fix setting type, var 'type' used by control argument, so we must use different name
        if (isset($setting_args['setting_type'])) {
            $setting_args['type'] = $setting_args['setting_type'];
            unset($setting_args['setting_type']);
        }
        
        // fix setting type
        if ($setting_args['type'] !== 'option') {
            $setting_args['type'] = 'theme_mod';
        }
        
        if (isset(self::$field_type[$control_args['type']][1]) === TRUE) {
            $customize_control_class = self::$field_type[$control_args['type']][1];
        } else {
            $customize_control_class = 'WP_Customize_Control';
        }
        
        // register setting
        $wp_customize->add_setting($control_id, $setting_args);
        
        if (class_exists($customize_control_class)) {
            // register custom control
            $control_args = shortcode_atts(get_class_vars($customize_control_class), $control_args);
            $wp_customize->add_control(new $customize_control_class($wp_customize, $control_id, $control_args));
        } else {
            // register control
            // WP_Customize_Control
            $control_args = shortcode_atts(self::$default_control_args, $control_args);
            $wp_customize->add_control($control_id, $control_args);
        }
        
        $wp_customize->get_control($control_id)->editable = $control['editable'];
    }
    
    // --------------------------------------
    /**
     * Register custom field control
     *
     * @param STRING $type
     *            Control type, eg: "phone"
     * @param STRING $label
     *            Label for settings page, eg: "Phone Number"
     * @param STRING $class
     *            Class name, must extends WP_Customize_Control
     *            
     * @author Alfi Rizka
     */
    public static function field_register($type, $label, $class)
    {
        self::$field_type[$type] = array(
            $label,
            $class
        );
    }
    
    // --------------------------------------
    /**
     * Get registered fields
     *
     * @author Alfi Rizka
     * @param STRING|NULL $id
     *            field/control id
     * @return ARRAY NULL
     */
    public static function fields($id = NULL)
    {
        if (is_null($id) === TRUE) {
            return self::$field_type;
        } elseif (isset(self::$field_type[$id]) === TRUE) {
            return self::$field_type[$id];
        }
        
        return NULL;
    }
    
    // --------------------------------------
    /**
     * Get registered sections
     *
     * @author Alfi Rizka
     * @return ARRAY self::$section
     */
    public static function sections()
    {
        return self::$sections;
    }
    
    // --------------------------------------
    /**
     * Add theme customizer section
     *
     * @author Alfi Rizka
     * @param STRING $section_id            
     * @param ARRAY $args            
     * @return STRING section id
     */
    public static function add_section($section_id, $args = array())
    {
        $controls = array();
        
        if (isset($args['controls']) && is_array($args['controls'])) {
            $controls = $args['controls'];
            unset($args['controls']);
        }
        
        if (isset(self::$sections[$section_id]) === FALSE) {
            $args['editable'] = FALSE;
            self::$sections[$section_id] = $args;
        }
        
        foreach ($controls as $control_id => $control_args) {
            self::add_control($section_id, $control_id, $control_args);
        }
        
        return $section_id;
    }
    
    // --------------------------------------
    /**
     * Add theme customizer control
     *
     * @author Alfi Rizka
     * @param STRING $section_id            
     * @param STRING $control_id            
     * @param ARRAY $args            
     * @return STRING control id
     */
    public static function add_control($section_id, $control_id, $args = array())
    {
        $args['id'] = $control_id;
        $args['editable'] = FALSE; // force to not editable
        
        // format args
        $args = _a55_build_control_data($section_id, $args);
        
        if (isset(self::$sections[$section_id]) === TRUE) {
            self::$sections[$section_id]['controls'][$control_id] = $args;
        } else {
            $args['section'] = $section_id;
            self::$control_to_other_section[$control_id] = $args;
        }
        
        return $control_id;
    }
    
    // -------------------------------------
    /**
     * Getter private property
     *
     * @author Alfi Rizka
     * @param STRING $property            
     * @return MIXED NULL
     */
    public static function get($property)
    {
        if (isset(self::${$property})) {
            return self::${$property};
        }
        return NULL;
    }
    
    // -------------------------------------
    /**
     * Enqueue css on wp-admin/customize.php
     *
     * @author Alfi Rizka
     */
    public static function customize_controls_print_styles()
    {
        wp_enqueue_style('atomic55-customizer-page', Atomic55_Plugin::$url . 'css/customizer.css');
    }
}
