<?php

/**
 * Widget Base Class
 * 
 * @author Alfi Rizka
 * @copyright Atomic55.net
 */
class Atomic55_Widgets
{

    public static function initialize()
    {
        self::_hooks();
    }

    private static function _hooks()
    {
        add_action('widgets_init', array(
            __CLASS__,
            'widgets_init'
        ));
    }

    public static function widgets_init()
    {
        register_widget('Atomic55_Widget_Address');
        register_widget('Atomic55_Widget_Socmed');
    }
}