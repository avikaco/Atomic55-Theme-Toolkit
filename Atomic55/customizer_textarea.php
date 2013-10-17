<?php
/**
 * Create custom textarea control for theme customizer
 *
 * @author Alfi Rizka
 * @copyright Atomic55
 */

if(class_exists('WP_Customize_Control') === FALSE) {
    require_once ABSPATH . WPINC . '/class-wp-customize-control.php';
}

class Atomic55_Customizer_Textarea extends WP_Customize_Control {
    public $type = 'textarea';

    public function atomic55_editor() {
        echo $this->type;
    }
    
    public function render_content() {
?>
        <label>
        <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
        <textarea rows="5" <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
        </label>
<?php
    }
}