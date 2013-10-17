<?php
/**
 * Widget Site Contact and Address 
 * @author Alfi Rizka T
 * @copyright Atomic55
 */

add_action('admin_print_scripts-widgets.php', 'a55_admin_print_scripts_widget_address');

function a55_admin_print_scripts_widget_address()
{
//     wp_enqueue_script('admin.widgets.js', Atomic55_Theme_Axxinet2::$theme_uri . '/js/admin.widgets.min.js', array(
//         'jquery',
//         'googlemap55'
//     ), '', true);
}

/**
 * Adds Atomic55_Contact_Widget widget.
 * 
 * @author Alfi Rizka T
 * @copyright Atomic55
 */
class Atomic55_Widget_Address extends WP_Widget
{

    /**
     * Register widget with WordPress.
     */
    function __construct()
    {
        parent::__construct('atomic55_widget_contact', 'Contact &amp; Map', array(
            'description' => 'Display your site contact information to widget'
        ));
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args
     *            Widget arguments.
     * @param array $instance
     *            Saved values from database.
     */
    public function widget($args, $instance)
    {
        echo $args['before_widget'];
        
        $latlng = get_theme_mod('latlng');
        $address = get_theme_mod('street_address');
        $rand_map_id = $args['widget_id'] . '-map';
        $map_zoom = 16;
        $width = intval(preg_replace('#\D#s', '', get_theme_mod('map_width', 400)));
        $height = intval(preg_replace('#\D#s', '', get_theme_mod('map_height', 400)));
        if ($height === 0) {
            $height = 400;
        }
        ?>
<div
	class="atomic55-contact-widget <?php echo (empty($latlng) ? '' : 'atomic55-contact-have-map') ?>">
<?php
        if (empty($latlng) == false) :
            if (get_theme_mod('use_static_map') == 1) :
                // validate width
                if ($width === 0) {
                    $width = 400;
                }
                
                $google_static_map_url = 'http://maps.googleapis.com/maps/api/staticmap?zoom=' . $map_zoom . '&size=' . $width . 'x' . $height . '&sensor=false&visual_refresh=true&';
                $google_static_map_url .= 'center=' . $latlng . '&markers=' . $latlng;
                // FIXME incorect link
                $google_map_link = 'https://maps.google.com/?ll=' . $latlng . '&t=m&z=' . $map_zoom;
                
                // echo '<a href="'. $google_map_link .'" target="_blank">';
                echo '<img id="' . $rand_map_id . '" src="' . $google_static_map_url . '" alt="' . esc_attr($address) . '" />';
                         // echo '</a>';
            
            else :
                
                if (wp_script_is('atomicsimplemap') === FALSE) {
                    wp_register_script('googlemap', '//maps.googleapis.com/maps/api/js?v=3.exp&sensor=false', '', '3.exp', true);
                    wp_register_script('atomicsimplemap', get_template_directory_uri() . '/js/map.min.js', array(
                        'jquery',
                        'googlemap'
                    ), '', true);
                    wp_enqueue_script('atomicsimplemap');
                }
                
                list ($lat, $lng) = explode(',', $latlng);
                
                $width = '100%'; // always 100% for dynamic map
                $height .= 'px';
                ?>
		<div id="<?php echo $rand_map_id ?>" class="map"
		                    data-zoom="<?php echo $map_zoom ?>"
							data-lat="<?php echo $lat ?>" data-lng="<?php echo $lng ?>"
							data-title="<?php echo esc_attr(get_theme_mod( 'street_address' )) ?>"
							style="height:<?php echo $height ?>;width:<?php echo $width ?>"></div>



            <?php endif;
         // use dynamic map
endif; // user ser latlng
        
        ?>
    <div class="atomic55-contact-detail">
<?php
        $title = apply_filters('widget_title', $instance['title']);
        if (! empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        ?>
		<div class="atomic55-contact-item">
			<span>Phone:</span>
    		<?php echo get_theme_mod( 'phone_number' )?>
		</div>
		<div class="atomic55-contact-item">
			<span>Email:</span> <a
				href="mailto:<?php echo get_theme_mod( 'email_address' )?>"><?php echo get_theme_mod( 'email_address' )?></a>
		</div>
		<div class="atomic55-contact-item">
			<span>Address:</span>
    		<?php echo $address?>
		</div>
	</div>
</div>
<?php
        echo $args['after_widget'];
        ?><?php
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance
     *            Previously saved values from database.
     */
    public function form($instance)
    {
        if (isset($instance['title']) === FALSE) {
            $instance['title'] = 'Contact Us';
            $instance['phone_number'] = get_theme_mod('phone_number');
            $instance['email_address'] = get_theme_mod('email_address');
            $instance['street_address'] = get_theme_mod('street_address');
            $instance['showmap'] = '1';
            $instance['latlng'] = get_theme_mod('latlng');
        }
        ?>
<p>
	<label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
	<input class="widefat"
		id="<?php echo $this->get_field_id( 'title' ); ?>"
		name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
		value="<?php echo esc_attr( $instance['title'] ); ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_name( 'phone_number' ); ?>"><?php _e( 'Phone:' ); ?></label>
	<input class="widefat"
		id="<?php echo $this->get_field_id( 'phone_number' ); ?>"
		name="<?php echo $this->get_field_name( 'phone_number' ); ?>"
		type="text"
		value="<?php echo esc_attr( $instance['phone_number'] ); ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_name( 'email_address' ); ?>"><?php _e( 'Email:' ); ?></label>
	<input class="widefat"
		id="<?php echo $this->get_field_id( 'email_address' ); ?>"
		name="<?php echo $this->get_field_name( 'email_address' ); ?>"
		type="email"
		value="<?php echo esc_attr( $instance['email_address'] ); ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_name( 'street_address' ); ?>"><?php _e( 'Address:' ); ?></label>
	<input class="widefat a55_widget_contact_address"
		id="<?php echo $this->get_field_id( 'street_address' ); ?>"
		name="<?php echo $this->get_field_name( 'street_address' ); ?>"
		type="text"
		value="<?php echo esc_attr( $instance['street_address'] ); ?>" />
</p>
<p>
	<label><input type="checkbox"
		id="<?php echo $this->get_field_id( 'showmap' ); ?>"
		name="<?php echo $this->get_field_name( 'showmap' ); ?>"
		class="a55_widget_contact_show_map" value="1"
		<?php echo $instance['showmap'] == '1' ? 'checked' : '' ?> /> <?php _e( 'Map Location:' ); ?></label>
	<a href="#" class="a55_widget_contact_search_address">Search from
		address</a> <input id="<?php echo $this->get_field_id( 'latlng' ); ?>"
		name="<?php echo $this->get_field_name( 'latlng' ); ?>" type="hidden"
		class="a55_widget_contact_latlng"
		value="<?php echo esc_attr( $instance['latlng'] ); ?>" />
</p>

<div id="<?php echo $this->get_field_id( 'map-wrp' ); ?>"
	class="a55_widget_contact_map" style="height: 300px">ini map</div>
<?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance
     *            Values just sent to be saved.
     * @param array $old_instance
     *            Previously saved values from database.
     *            
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $key_available = array(
            'title',
            'phone_number',
            'email_address',
            'street_address',
            'showmap',
            'latlng'
        );
        
        for ($i = 0, $j = count($key_available); $i < $j; $i ++) {
            $instance[$key_available[$i]] = (! empty($new_instance[$key_available[$i]])) ? strip_tags($new_instance[$key_available[$i]]) : '';
        }
        
        $theme_mods = $instance;
        unset($theme_mods['title'], $theme_mods['showmap']);
        foreach ($theme_mods as $k => $v) {
            set_theme_mod($k, $v);
        }
        
        return $instance;
    }
}