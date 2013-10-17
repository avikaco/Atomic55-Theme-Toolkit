<?php
/**
 * Create map control for theme customizer
 *
 * @author Alfi Rizka
 * @copyright Atomic55
 */


// require wp control, actually this autoload, just make sure load wp customize before this file
if(class_exists('WP_Customize_Control') === FALSE) {
    require_once ABSPATH . WPINC . '/class-wp-customize-control.php';
}


class Atomic55_Customizer_Map extends WP_Customize_Control
{

    public $type = 'map';

    private $_default_value = array(
        'lat' => '',
        'lng' => '',
        'zoom' => 16,
        'map_type' => 'dynamic',
        'width' => '100%',
        'height' => '300px'
    );
    
    public function __construct($manager, $id, $args = array()) {
        parent::__construct($manager, $id, $args);
        
        //$this->settings[ 'default' ] = $this->_default_value;
    }

    public function atomic55_editor()
    {
        echo $this->type;
    }

    public function enqueue()
    {
        wp_enqueue_script('a55-theme-customizer-map.js', Atomic55_Plugin::$url . 'js/theme-customizer-map.min.js', array(
            'jquery',
            'googlemap55'
        ), '', true);
    }
    
    public function render_content()
    {
        $value = $this->value();
        if (empty($value)) {
            $value = $this->_default_value;
        } else {
            $value = (array) json_decode($value, TRUE);
            $value = shortcode_atts($this->_default_value, $value);
        }
        print_r($value);
        ?>

<label> <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
</label>
<div class="a55-control-map">
	<input type="hidden"
		value="<?php echo esc_attr(json_encode($value)) ?>"
		<?php $this->link(); ?>>

	<div class="a55-control-map-wrp" data-lat="<?php echo $value['lat'] ?>"
		data-lng="<?php echo $value['lng'] ?>"
		data-zoom="<?php echo $value['zoom'] ?>"></div>
	<div class="a55-control-map-option">
		<strong>Map type</strong> <label><input type="radio"
			name="a55-map-type-<?php echo $this->id ?>" value="dynamic"
			<?php echo $value['map_type'] !== 'static' ? 'checked' : '' ?> /> Dynamic
			map (using javascript)</label> <label><input type="radio"
			name="a55-map-type-<?php echo $this->id ?>" value="static"
			<?php echo $value['map_type'] === 'static' ? 'checked' : '' ?> /> Static
			map (using image)</label> <strong>Map dimension</strong> <input
			type="text" name="a55-map-width-<?php echo $this->id ?>" value="<?php echo $value['width'] ?>"
			placeholder="width in px or %" /> &times; <input type="text" name="a55-map-height-<?php echo $this->id ?>"
			value="<?php echo $value['height'] ?>" placeholder="height in px" />
	</div>
</div>

<?php
    }
}