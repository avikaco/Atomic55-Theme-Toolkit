<?php
/**
 * Create custom textarea control for theme customizer
 *
 * @author Alfi Rizka
 * @copyright Atomic55
 */

// require wp control, actually this autoload, just make sure load wp customize before this file
if(class_exists('WP_Customize_Control') === FALSE) {
    require_once ABSPATH . WPINC . '/class-wp-customize-control.php';
}

class Atomic55_Customizer_Range extends WP_Customize_Control {
    public $type = 'range';
    public $step = 1;
    public $min = 0;
    public $max = 100;
    
    public function atomic55_editor() {
        echo $this->type;
    }
    
    public function render_content() {
?>
    <label>
        <span class="customize-control-title"><?php echo esc_html( $this->label ); ?> <em class="a55-control-range-value" id="rangevalue" <?php $this->link(); ?>><?php echo esc_attr( $this->value() ); ?></em> </span>
        <input type="range" class="a55-control-range-input" min="<?php echo $this->min ?>" max="<?php echo $this->max ?>" step="<?php echo $this->step ?>" <?php $this->link(); ?> value="<?php echo esc_attr( $this->value() ); ?>" />
    </label>
<?php
    }
}