<?php

/**
 * Theme toolkit hooks
 * 
 * @auhtor Alfi Rizka
 * @copyright Atomic55
 */
class Customizer_Hooks
{

    private static $option_theme_branding;

    private static $option_theme_customizer;

    private static $css_filename = 'a55-theme-customizer.css';
    
    // --------------------------------------
    /**
     *
     * @author Alfi Rizka
     */
    public static function initialize()
    {
        self::$option_theme_branding = get_option('a55_theme_branding');
        self::$option_theme_customizer = Atomic55_Plugin::options_get();
        
        self::_hooks();
    }
    
    // --------------------------------------
    /**
     *
     * @author Alfi Rizka
     */
    private static function _hooks()
    {
        add_action('init', array(
            __CLASS__,
            'init'
        ));
        add_action('wp_head', array(
            __CLASS__,
            'wp_head'
        ), 1);
        add_action('wp_head', array(
            __CLASS__,
            'favicon'
        ), 99999);
        add_action('admin_head', array(
            __CLASS__,
            'admin_head'
        ), 99999);
        add_action('wp_footer', array(
            __CLASS__,
            'wp_footer'
        ), 99999);
        add_action('admin_enqueue_scripts', array(
            __CLASS__,
            'admin_enqueue_scripts'
        ), 99999);
        add_action('admin_notices', array(
            __CLASS__,
            'admin_notices'
        ), 1);
        add_action('login_enqueue_scripts', array(
            __CLASS__,
            'login_enqueue_scripts'
        ), 99999);
        add_action('wp_enqueue_scripts', array(
            __CLASS__,
            'wp_enqueue_scripts'
        ), 99999);
        add_filter('admin_footer_text', array(
            __CLASS__,
            'admin_footer_text'
        ), 999999, 1);
        
        add_filter('update_footer', array(
            __CLASS__,
            'update_footer'
        ), 999999, 1);
        add_filter('upload_mimes', array(
            __CLASS__,
            'upload_mimes'
        ));
        add_filter('excerpt_length', array(
            __CLASS__,
            'excerpt_length'
        ), 99999, 1);
        add_filter('excerpt_more', array(
            __CLASS__,
            'excerpt_more'
        ), 99999, 1);
        add_filter('a55_customizer_css_render', array(
            __CLASS__,
            'build_css'
        ), 1);
        
        // cleaning up wp_head()
        if (get_option('a55_remove_wp_meta', FALSE)) {
            remove_action('wp_head', 'rsd_link');
            remove_action('wp_head', 'wlwmanifest_link');
            remove_action('wp_head', 'index_rel_link');
            remove_action('wp_head', 'wp_generator');
        }
        
        // move JS to footer
        if (get_option('a55_js_bottom', FALSE)) {
            remove_action('wp_head', 'wp_print_scripts');
            remove_action('wp_head', 'wp_print_head_scripts', 9);
            add_action('wp_footer', 'wp_print_scripts', 5);
            add_action('wp_footer', 'wp_print_head_scripts', 5);
        }
        
        // remove admin bar at frontend
        if (get_option('a55_remove_adminbar', FALSE)) {
            add_filter('show_admin_bar', '__return_false');
            remove_action('wp_footer', 'wp_admin_bar_render', 1000); // for the front end
        }
    }
    
    // --------------------------------------
    /**
     * hook wp init
     * used by hook: init
     *
     * @author Alfi Rizka
     */
    public static function init()
    {
        if (strpos($_SERVER['REQUEST_URI'], self::$css_filename) !== FALSE) {
            self::render(FALSE);
            die();
        }
        
        // add theme support
        if (empty(self::$option_theme_customizer['theme_supports']) === FALSE) {
            for ($i = 0, $j = count(self::$option_theme_customizer['theme_supports']); $i < $j; $i ++) {
                add_theme_support(self::$option_theme_customizer['theme_supports'][$i]);
            }
        }
        
        wp_register_script('googlemap55', '//maps.googleapis.com/maps/api/js?v=3.exp&sensor=true&libraries=places');
        wp_register_style(self::$css_filename, site_url(self::$css_filename), '', strtotime(get_option('a55_customizer_last_built')));
    }
    
    // --------------------------------------
    /**
     * hook wp enqueue script
     * used by hook: wp_enqueue_scripts
     *
     * @author Alfi Rizka
     */
    public static function wp_enqueue_scripts()
    {
        if (wp_script_is('customize-preview') === TRUE) {
            self::render(TRUE);
        } else {
            wp_enqueue_style(self::$css_filename);
        }
    }
    
    // --------------------------------------
    /**
     * Render CSS
     *
     * @author Alfi Rizka
     */
    public static function render($is_embed = TRUE)
    {
        $css = '';
        
        if ($is_embed === TRUE) {
            update_option('a55_customizer_last_built', gmdate('D, d M Y H:i:s') . ' GMT');
            
            echo "\n\n<!-- BEGIN Atomic55 Theme Customizer -->";
            echo "\n<style>\n";
            
            // build CSS
            $css = apply_filters('a55_customizer_css_render', $css);
            echo $css;
            
            echo "\n</style>\n";
            echo "<!-- END Atomic55 Theme Customizer -->\n\n";
        } else {
            $last_built = get_option('a55_customizer_last_built');
            if (! $last_built) {
                $last_built = gmdate('D, d M Y H:i:s') . ' GMT';
                update_option('a55_customizer_last_built', $last_built);
            }
            
            $etag = md5($last_built);
            
            header('Last-Modified: ' . $last_built);
            header('ETag: "' . $etag . '"');
            header('content-type: text/css');
            
            if (function_exists('apache_request_headers') === TRUE) {
                $headers = call_user_func('apache_request_headers');
                if (isset($headers['If-Modified-Since'])) {
                    if ($headers['If-Modified-Since'] == $last_built) {
                        header('HTTP/1.1 304 Not Modified');
                        exit();
                    }
                }
            }
            
            // build CSS
            $css = apply_filters('a55_customizer_css_render', $css);
            
            // compress CSS
            require_once Atomic55_Plugin::$path . 'libs/Minify_CSS_Compressor.php';
            $css = Minify_CSS_Compressor::process($css);
            
            die($css);
        }
    }
    
    // --------------------------------------
    public static function build_css($css = '')
    {
        $raw_css = get_option('a55_customizer_css', '');
        if (empty($raw_css) === true) {
            return $css;
        }
        
        $css_vars = $cache = array();
        preg_match_all('/<%=([^%>]*)%>/', $raw_css, $css_vars);
        
        if (empty($css_vars[1])) {
            // no pattern found
            return $css . $raw_css;
        }
        
        for ($i = 0, $j = count($css_vars[0]); $i < $j; $i ++) {
            $key = preg_replace('/\s/', '', $css_vars[1][$i]); // remove all space
            
            if (isset($cache[$key]) === FALSE) {
                @list ($type, $id) = explode(':', $key, 2);
                
                if (empty($id) && empty($type) === FALSE) {
                    $id = $type;
                    $type = 'theme_mod';
                }
                
                // default value
                $default = '';
                if (strpos($id, '(')) {
                    list ($id, $default) = explode('(', $css_vars[1][$i], 2);
                    $id = trim($id);
                    $default = trim($default);
                    $default = trim(preg_replace('/\)$/', '', $default)); // remove ) at the end of str
                    if ($default[0] === '"') {
                        $default = trim($default, '"');
                    } elseif ($default[0] === "'") {
                        $default = trim($default, "'");
                    }
                    
                    $default = stripslashes($default);
                }
                if ($id === 'theme_url') {
                    $value = get_template_directory_uri();
                } elseif ($type === 'site_url') {
                    $value = site_url();
                } elseif ($type === 'option') {
                    $value = get_option($id, $default);
                } else {
                    $value = get_theme_mod($id, $default);
                }
                $cache[$key] = $value;
            }
            
            $css_vars[0][$i] = '#' . preg_quote($css_vars[0][$i], '#') . '#';
            $css_vars[1][$i] = $cache[$key];
        }
        
        $raw_css = preg_replace($css_vars[0], $css_vars[1], $raw_css);
        
        return $css . $raw_css;
    }
    // --------------------------------------
    /**
     * Action for admin head
     *
     * @author Alfi Rizka
     */
    public static function admin_head()
    {
        // add favicon
        self::favicon();
    }
    
    // --------------------------------------
    /**
     * Change excerpt length
     *
     * @author Alfi Rizka
     */
    public static function excerpt_more($more)
    {
        $readmore_text = get_option('a55_excerpt_readmore_text');
        if ($readmore_text) { // FIXME read from option
            $more = ' <a class="read-more" href="' . get_permalink(get_the_ID()) . '">' . $readmore_text . '</a>';
        }
        return $more;
    }
    // --------------------------------------
    /**
     * Change excerpt length
     *
     * @author Alfi Rizka
     */
    public static function excerpt_length($length)
    {
        $words_length = get_option('a55_excerpt_length');
        if ($words_length) {
            $length = $words_length;
        }
        return $length;
    }
    
    // --------------------------------------
    /**
     * Action for login enqueue script, manipulate Login Logo (above the login form)
     *
     * @author Alfi Rizka
     */
    public static function login_enqueue_scripts()
    {
        // add favicon
        self::admin_head();
        
        // if login logo empty, return nothing
        if (empty(self::$option_theme_branding['login'])) {
            return;
        }
        
        // change get_option('blogname') to login logo
        add_filter('pre_option_blogname', array(
            __CLASS__,
            'pre_option_blogname'
        ), 99999);
        
        // write inline stylesheet only for login page. WHY not using background-image? because background image not responsive
        echo '
<style type="text/css">
body.login div#login h1 a {
    background-image: none;
    text-indent: 0;
    display: block;
    height: auto;
}
body.login div#login h1 a img {
    max-width:100% !important;
    height:auto;
    display:block;
    margin:0 auto;
}
</style>
';
    }
    
    // --------------------------------------
    /**
     * Filter for manipulating get_option('blogname')
     * this function only manipulate once, that why you will see static $blogname_access
     *
     * @author Alfi Rizka
     */
    public static function pre_option_blogname($default = FALSE)
    {
        static $blogname_access;
        
        if (empty(self::$option_theme_branding['login']) || $blogname_access !== 1) {
            $blogname_access = 1;
            echo '<img src="' . self::$option_theme_branding['login'] . '" alt="' . esc_attr(get_bloginfo('name')) . '" />';
            return '';
        }
        
        return $default;
    }
    
    // --------------------------------------
    /**
     * Action for WP Head,
     * Add favicon, keyword, description, Google/Bing/Yahoo webmaster validation
     *
     * @author Alfi Rizka
     */
    public static function wp_head()
    {
        if (in_array('a55_seo', self::$option_theme_customizer['disabled']['sections'])) {
            // don't print anything if section a55_seo is disabled by user
            return;
        }
        
        // html meta & webmaster verification (eg: google, bing, yahoo, facebook, etc).
        $metas = array(
            'meta_description' => 'description',
            'meta_keyword' => 'keyword',
            'meta_google_webmaster' => 'google-site-verification',
            'meta_fb_page_id' => 'fb:app_id',
            'meta_yahoo_webmaster' => 'y_key',
            'meta_bing_webmaster' => 'msvalidate.01'
        );
        
        foreach ($metas as $k => $v) {
            $value = get_option($k);
            if (empty($value) === FALSE) {
                echo "\n" . '<meta name="' . $v . '" content="' . esc_attr($value) . '">';
            }
        }
        
        echo "\n";
        
    }
    
    // --------------------------------------
    /**
     * Add favicon
     *
     * @author Alfi Rizka
     */
    public static function favicon() {
        // favicon
        if (empty(self::$option_theme_branding['favicon']) === FALSE) {
            echo "<!-- Atomic55 theme toolkit favicon -->\n";
            echo '<link rel="icon" href="' . self::$option_theme_branding['favicon'] . '" type="image/x-icon">' . "\n";
            echo '<link rel="shortcut icon" href="' . self::$option_theme_branding['favicon'] . '" type="image/x-icon">';
            echo "\n<!-- End of Atomic55 theme toolkit favicon -->\n";
        }
    }
    
    // --------------------------------------
    /**
     * Action for wp_footer
     * Add google analytics code
     *
     * @author Alfi Rizka
     */
    public static function wp_footer()
    {
        if (in_array('a55_seo', self::$option_theme_customizer['disabled']['sections'])) {
            // don't print anything if section a55_seo is disabled by user
            return;
        }
        
        $analytics_id = get_option('google_analytics_id');
        $host = preg_replace('/^www\./', '', $_SERVER['HTTP_HOST']); // without WWW.
        
        if (empty($analytics_id) === FALSE) {
            echo "\n<!-- Atomic55 theme toolkit, google analytics -->\n";
            echo "<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', '" . $analytics_id . "', '" . $host . "');
  ga('send', 'pageview');

</script>";
            
            echo "\n<!-- End of Atomic55 theme toolkit, google analytics -->\n";
        }
    }
    
    // --------------------------------------
    /**
     * Action for admin_enqueue_scripts
     * Enqueue admin.general.css to style powered by Atomic Link (see right bottom at admin page)
     *
     * @author Alfi Rizka
     */
    public static function admin_enqueue_scripts()
    {
        wp_enqueue_style('a55-admin-general', Atomic55_Plugin::$url . 'css/admin.general.css');
    }
    
    // --------------------------------------
    /**
     * Action for admin head for branding perpose.
     *
     * @author Alfi Rizka
     */
    public static function admin_notices()
    {
        if (isset(self::$option_theme_branding['admin_header']) === FALSE) {
            self::$option_theme_branding['admin_header'] = '';
        }
        echo '<div id="a55-theme-admin_header">' . self::$option_theme_branding['admin_header'] . '</div>';
    }
    
    // --------------------------------------
    /**
     * Action for admin footer for branding perpose.
     * Change "Thank you for creating with WordPress." to user defined text
     *
     * @author Alfi Rizka
     */
    public static function admin_footer_text($str = '')
    {
        if (empty(self::$option_theme_branding['admin_footer']) === FALSE) {
            $str = self::$option_theme_branding['admin_footer'];
        }
        return '</p><div id="a55-theme-admin_footer" class="alignleft">' . $str . '</div><p style="display:none">';
    }
    
    // --------------------------------------
    /**
     * Action for update footer
     * Add "Powered by Atomic"
     *
     * @author Alfi Rizka
     */
    public static function update_footer($str = '')
    {
        return '</p><div class="alignright">' . $str . '<div id="a55-theme-toolkit-powered">Powered by <a href="http://atomic55.net/?utm_source=wordpress-plugin&utm_medium=themecustomizer&utm_term=help&utm_campaign=wordpress+plugin" target="_atomic55">Atomic55</a></div></div><p style="display:none">';
    }
    
    // --------------------------------------
    /**
     * Add support to *.ico
     * By default wordpress not accept *.ico to wp media.
     *
     * @author Alfi Rizka
     */
    public static function upload_mimes($existing_mimes = array())
    {
        // add the file extension to the array
        $existing_mimes['ico'] = 'image/x-icon';
        
        // call the modified list of extensions
        return $existing_mimes;
    }
}