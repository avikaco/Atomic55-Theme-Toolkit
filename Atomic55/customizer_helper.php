<?php
/**
 * Theme customizer helper functions
 * 
 * @author Alfi Rizka
 * @copyright Atomic55
 */
add_action('a55_customizer_init', 'a55_register_predefined');

/**
 * register predefined section
 *
 * @author Alfi Rizka
 */
function a55_register_predefined()
{
    // Contact Informations
    $section_args = array(
        'title' => 'Contact Informations',
        'description' => 'Your contact information',
        'is_theme_option' => TRUE,
        'controls' => array(
            'site_contact_phone' => array(
                'label' => 'Phone',
                'type' => 'text',
                'setting_type' => 'option',
                'default' => ''
            ),
            'site_contact_email' => array(
                'label' => 'Email',
                'type' => 'text',
                'setting_type' => 'option',
                'default' => ''
            ),
            'site_contact_address' => array(
                'label' => 'Address',
                'type' => 'atomic55_textarea',
                'setting_type' => 'option',
                'default' => ''
            ),
            'site_contact_map' => array(
                'label' => 'Map',
                'type' => 'atomic55_map',
                'setting_type' => 'option',
                'default' => ''
            )
        )
    );
    a55_theme_section_register('a55_contact', $section_args);
    
    // SEO
    $section_args = array(
        'title' => 'SEO Optimizer',
        'description' => 'This setting will aplied to all page, leave it blank if you use other SEO plugin.',
        'is_theme_option' => TRUE,
        'controls' => array(
            'meta_keyword' => array(
                'label' => 'Keyword',
                'type' => 'atomic55_textarea',
                'setting_type' => 'option',
                'default' => ''
            ),
            'meta_description' => array(
                'label' => 'Description',
                'type' => 'atomic55_textarea',
                'setting_type' => 'option',
                'default' => ''
            ),
            'meta_fb_page_id' => array(
                'label' => 'Facebook App/Page ID',
                'type' => 'text',
                'setting_type' => 'option',
                'default' => ''
            ),
            'google_analytics_id' => array(
                'label' => 'Google Analytics',
                'type' => 'text',
                'setting_type' => 'option',
                'default' => ''
            ),
            'meta_google_webmaster' => array(
                'label' => 'Google Webmaster Verification',
                'type' => 'text',
                'setting_type' => 'option',
                'default' => ''
            ),
            'meta_yahoo_webmaster' => array(
                'label' => 'Yahoo Webmaster Verification',
                'type' => 'text',
                'setting_type' => 'option',
                'default' => ''
            ),
            'meta_bing_webmaster' => array(
                'label' => 'Bing Webmaster Verification',
                'type' => 'text',
                'setting_type' => 'option',
                'default' => ''
            )
        )
    );
    a55_theme_section_register('a55_seo', $section_args);
    
    // Social Media
    $section_args = array(
        'title' => 'Social Media',
        'is_theme_option' => TRUE,
        'controls' => array(
            'socmed_facebook' => array(
                'label' => 'Facebook',
                'type' => 'text',
                'setting_type' => 'option',
                'default' => ''
            ),
            'socmed_twitter' => array(
                'label' => 'Twitter',
                'type' => 'text',
                'setting_type' => 'option',
                'default' => ''
            ),
            'socmed_youtube' => array(
                'label' => 'Youtube',
                'type' => 'text',
                'setting_type' => 'option',
                'default' => ''
            ),
            'socmed_pinterest' => array(
                'label' => 'Pinterest',
                'type' => 'text',
                'setting_type' => 'option',
                'default' => ''
            ),
            'socmed_googleplus' => array(
                'label' => 'Google+',
                'type' => 'text',
                'setting_type' => 'option',
                'default' => ''
            ),
            'socmed_linkedin' => array(
                'label' => 'Linked Id',
                'type' => 'text',
                'setting_type' => 'option',
                'default' => ''
            )
        )
    );
    a55_theme_section_register('a55_social_media', $section_args);
    
    // General
    $section_args = array(
        'title' => 'Wordpress Options',
        'is_theme_option' => TRUE,
        'priority' => 21,
        'controls' => array(
            'a55_remove_adminbar' => array(
                'label' => 'Remove Wordpress admin bar',
                'type' => 'checkbox',
                'setting_type' => 'option',
                'default' => FALSE
            ),
            'blog_public' => array(
                'label' => 'Allow search engines indexing this site',
                'type' => 'checkbox',
                'setting_type' => 'option',
                'default' => 1
            ),
            'a55_remove_wp_meta' => array(
                'label' => 'Remove Wordpress meta tag',
                'type' => 'checkbox',
                'setting_type' => 'option',
                'default' => FALSE
            ),
            'a55_js_bottom' => array(
                'label' => 'Put all javascript at bottom of page',
                'type' => 'checkbox',
                'setting_type' => 'option',
                'default' => FALSE
            ),
            'a55_excerpt_length' => array(
                'label' => 'Excerpt word length',
                'type' => 'atomic55_range',
                'setting_type' => 'option',
                'default' => 25
            ),
            'a55_excerpt_readmore_text' => array(
                'label' => 'Excerpt suffix (default: [...])',
                'type' => 'text',
                'setting_type' => 'option',
                'default' => ''
            )
        )
    );
    a55_theme_section_register('a55_wp_general', $section_args);
}
// --- end of predefined section


// ---------------------------------------
/**
 * Register new theme customizer section
 * Call this function from action hook "a55_customizer_init"
 *
 * @param unknown $section_id            
 * @param unknown $args            
 *
 * @author Alfi Rizka
 */
function a55_theme_section_register($section_id, $args = array())
{
    return Atomic55_Customizer::add_section($section_id, $args, 'theme');
}

// ---------------------------------------
/**
 * Register theme customizer control to section
 *
 * @param STRING $section_id            
 * @param STRING $control_id            
 * @param ARRAY $attr            
 *
 * @author Alfi Rizka
 */
function a55_theme_control_register($section_id, $control_id, $attr = array())
{
    Atomic55_Customizer::add_control($section_id, $control_id, $attr);
}

// ---------------------------------------
/**
 * Render map from control
 *
 * @param STRING $control_id            
 * @param BOOLEAN $use_static_map
 *            default: false
 * @param STRING $width
 *            default: 100%
 * @param STRING $height
 *            default: 400px
 * @return STRING
 *
 * @author Alfi Rizka
 */
function a55_display_map($control_id, $use_static_map = TRUE, $width = '100%', $height = '400px')
{
    $map = '';
    return $map;
}

// ---------------------------------------
/**
 * Render social media links
 *
 * $default_attr = array(
 * 'container' => 'ul',
 * 'container_class' => 'a55-site-socmed',
 * 'container_id' => '',
 * 'item_wrap' => '',
 * 'link_before' => '<li>',
 * 'link_after' => '</li>',
 * 'echo' => true
 * );
 *
 * @param ARRAY $attr            
 * @param STRING $section_name
 *            optional, default: social_media
 * @return STRING
 *
 * @author Alfi Rizka
 */
function a55_social_media($attr = array(), $section_name = 'a55_social_media')
{
    $__customizer_settings = _a55_merge_section_control($section_name);
    $html = '';
    $default_attr = array(
        'container' => 'ul',
        'container_class' => '',
        'container_id' => '',
        'item_wrap' => '',
        'link_before' => '<li>',
        'link_after' => '</li>',
        'echo' => true
    );
    
    // if Section is disabled
    if ($__customizer_settings === FALSE) {
        // show notification if current user is admin.
        if (is_admin()) {
            echo '<font color="red">Social media is disabled.</font> ';
            // show link if theme setting is not disabled by theme developer
            if (defined('ATOMIC55_DISABLE_THEME_SETTING_PAGE') && ! ! ATOMIC55_DISABLE_THEME_SETTING_PAGE === TRUE) {
                return;
            }
            echo 'Please activate from <a href="' . admin_url('options-general.php?page=atomic55-theme-settings') . '">here</a>.';
        }
        return;
    }
    
    // merge attribute
    if (is_array($attr) === TRUE && empty($attr) === FALSE) {
        $attr = shortcode_atts($default_attr, $attr);
    } else {
        $attr = $default_attr;
    }
    
    if (empty($attr['item_wrap'])) {
        $attr['item_wrap'] = '%1$s';
    }
    
    foreach ($__customizer_settings as $control_id => $setting_type) {
        $value = ($setting_type === 'option' ? get_option($control_id) : get_theme_mod($control_id));
        if (empty($value) === TRUE) {
            continue;
        }
        
        // add http:// if missing
        if (preg_match('#^http://#i', $value) === 0) {
            $value = 'http://' . $value;
        }
        
        $id = str_replace('socmed_', '', $control_id);
        $item = sprintf($attr['item_wrap'], $id);
        $html .= "\n\t" . $attr['link_before'] . '<a href="' . esc_attr($value) . '" class="' . $id . '" target="_blank">' . $item . '</a>' . $attr['link_after'];
    }
    
    if (empty($html) === TRUE) {
        return '';
    }
    
    if (empty($attr['container']) === FALSE) {
        if (empty($attr['container_class']) === FALSE) {
            $attr['container_class'] = ' class="' . $attr['container_class'] . '"';
        }
        if (empty($attr['container_id']) === FALSE) {
            $attr['container_id'] = ' id="' . $attr['container_id'] . '"';
        }
        $html = sprintf("\n<%s%s%s>%s\n</%s>", $attr['container'], $attr['container_id'], $attr['container_class'], $html, $attr['container']);
    }
    
    if ($attr['echo']) {
        echo $html;
    }
    
    return $html;
}

/**
 * internal function to merge predefined control and user defined
 *
 * @author Alfi Rizka
 * @param STRING $section_id
 * @return ARRAY | NULL
 */
function _a55_merge_section_control($section_id)
{
    static $_a55_merge_section_control_cache;
    
    if (is_array($_a55_merge_section_control_cache) === FALSE) {
        $_a55_merge_section_control_cache = array();
        // get option "atomic55_theme_customizer"
        $_a55_merge_section_control_cache['_config'] = get_option('atomic55_theme_customizer');
        // trigger register predefined section & controls
        do_action('a55_customizer_init');
        $_a55_merge_section_control_cache['_predefined'] = Atomic55_Customizer::sections(); // get predifined sections
        $_a55_merge_section_control_cache['_control2section'] = Atomic55_Customizer::get('control_to_other_section'); // get predifined sections
    } elseif (isset($_a55_merge_section_control_cache[$section_id])) {
        // check cache if exist, return from cache
        return $_a55_merge_section_control_cache[$section_id];
    }
    
    // init data
    $_a55_merge_section_control_cache[$section_id] = $temp = array();
    
    // check is is disabled
    if (in_array($section_id, $_a55_merge_section_control_cache['_config']['disabled']['sections']) === TRUE) {
        $_a55_merge_section_control_cache[$section_id] = FALSE;
        return FALSE;
    }
    
    // is user defined section?
    if (isset($_a55_merge_section_control_cache['_config']['sections'][$section_id])) {
        // Yes, this is user defined section
        foreach ($_a55_merge_section_control_cache['_config']['sections'][$section_id]['controls'] as $control) {
            if (isset($temp[$control['priority']]) === FALSE) {
                $temp[$control['priority']] = array();
            }
            
            $temp[$control['priority']][$control['id']] = $control['setting']['type'];
        }
    } else {
        // not user defined section, check section registered
        if (isset($_a55_merge_section_control_cache['_predefined'][$section_id]) === FALSE) {
            return FALSE;
        }
        
        // add custom control to section
        $custom_control = array_merge($_a55_merge_section_control_cache['_control2section'], $_a55_merge_section_control_cache['_config']['control2builtin_section']);
        foreach ($custom_control as $control_id => $control) {
            if ($control['section'] === $section_id) {
                $_a55_merge_section_control_cache['_predefined'][$section_id]['controls'][$control_id] = $control;
            }
        }
        
        // group control by priority
        foreach ($_a55_merge_section_control_cache['_predefined'][$section_id]['controls'] as $control) {
            // check is this control disable
            if (in_array($control['id'], $_a55_merge_section_control_cache['_config']['disabled']['controls']) === TRUE) {
                continue;
            }
            // check is this control have different priority or section
            if (isset($_a55_merge_section_control_cache['_config']['priority']['controls'][$control['id']])) {
                list ($new_section, $priority) = $_a55_merge_section_control_cache['_config']['priority']['controls'][$control['id']];
                if ($new_section !== $section_id) {
                    continue;
                }
                $control['priority'] = $priority;
            }
            
            if (isset($temp[$control['priority']]) === FALSE) {
                $temp[$control['priority']] = array();
            }
            $temp[$control['priority']][$control['id']] = $control['setting']['type'];
        }
    }
    
    // sort array key as numeric
    ksort($temp, SORT_NUMERIC);
    
    // flat array temp
    foreach ($temp as $controls) {
        $_a55_merge_section_control_cache[$section_id] += $controls; // simple way to merge array
    }
    
    return $_a55_merge_section_control_cache[$section_id];
}

// --------------------------------------
/**
 * Build control data, call before register (@see Atomic55_Customizer::add_control()) & save register (@see Atomic55_Settings::ajax_admin_theme_customizer())
 *
 * @author Alfi Rizka
 * @param STRING $section_id            
 * @param ARRAY $control_args            
 * @return ARRAY formatted control args
 */
function _a55_build_control_data($section_id, $control_args = array())
{
    static $_control_class_property;
    
    if (is_array($_control_class_property)) {
        $_control_class_property = array();
    }
    
    $control_args['editable'] = (isset($control_args['editable']) && $control_args['editable'] == '1');
    $control_args['disabled'] = (isset($control_args['disabled']) && $control_args['disabled'] == '1');
    $control_args['section'] = $section_id;
    
    $_field = Atomic55_Customizer::fields($control_args['type']);
    if (isset($_field[1])) {
        list (, $control_class) = $_field;
    } else {
        $control_class = 'WP_Customize_Control';
    }
    
    if (isset($_control_class_property[$control_class]) === FALSE && class_exists($control_class) === TRUE) {
        $_control_args = get_class_vars($control_class);
        unset($_control_args['manager'], $_control_args['settings'], $_control_args['json']);
        $_control_class_property[$control_class] = array_merge(Atomic55_Customizer::$default_control_args, $_control_args);
    } else {
        $_control_class_property[$control_class] = Atomic55_Customizer::$default_control_args;
    }
    
    if (isset($control_args['setting']) && is_array($control_args['setting'])) {
        $setting_args = shortcode_atts(Atomic55_Customizer::$default_setting_args, $control_args['setting']);
    } else {
        $setting_args = shortcode_atts(Atomic55_Customizer::$default_setting_args, $control_args);
        if (isset($control_args['setting_type'])) {
            $setting_args['type'] = (strtolower($control_args['setting_type']) === 'option' ? 'option' : 'theme_mod');
        }
    }
    
    $control_args = shortcode_atts($_control_class_property[$control_class], $control_args);
    $control_args['setting'] = $setting_args;
    
    // fix choices
    if(empty($control_args['choices']) === FALSE) {
        $choices = array();
        foreach ($control_args['choices'] as $i => $c) {
            if(is_array($c)) {
                $choices[$c[0]] = $c[1];
            } else {
                $choices[$i] = $c;
            }
        }
        $control_args['choices'] = $choices;
    }
    
    // priority must integer, if not control list will wrong order
    $control_args['priority'] = intval($control_args['priority']);
    
    return $control_args;
}
