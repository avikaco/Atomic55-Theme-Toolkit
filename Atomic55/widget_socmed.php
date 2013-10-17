<?php
/**
 * Social Media Widget
 * 
 * @author Alfi Rizka
 * @copyright Atomic55
 */
add_action('admin_print_scripts-widgets.php', 'a55_admin_print_scripts_widget_socmed');

function a55_admin_print_scripts_widget_socmed()
{}

/**
 * Adds Atomic55_Widget_Socmed widget.
 *
 * @author Alfi Rizka T
 * @copyright Atomic55
 */
class Atomic55_Widget_Socmed extends WP_Widget
{

    /**
     * Register widget with WordPress.
     */
    function __construct()
    {
        parent::__construct('atomic55_widget_socmed', 'Social Media Links', array(
            'description' => 'Display your social media to widget'
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
        echo '<div class="atomic55-widget-socmed">';
        $title = apply_filters('widget_title', $instance['title']);
        if (empty($title) === FALSE) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        
        a55_display_socmed();
        
        
        echo '</div>';
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
            $instance['title'] = 'Our Social Media';
        }
        ?>
<p>
	<label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
	<input class="widefat"
		id="<?php echo $this->get_field_id( 'title' ); ?>"
		name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
		value="<?php echo esc_attr( $instance['title'] ); ?>" />
</p>

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
        $instance['title'] = strip_tags($new_instance['title']);
        
        return $instance;
    }
}