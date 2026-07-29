<?php

defined('ABSPATH') || exit;

final class Webform {
    private static $instance;

    public static function instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        require_once WEBFORM_PATH . 'includes/class-webform-admin.php';
        require_once WEBFORM_PATH . 'includes/class-webform-public.php';
        require_once WEBFORM_PATH . 'includes/class-webform-mailer.php';

        add_action('init', array($this, 'register_post_types'));
        new Webform_Admin();
        new Webform_Public();
        new Webform_Mailer();
        do_action('webform_loaded', $this);
    }

    public function register_post_types() {
        register_post_type('webform_form', array(
            'labels' => array(
                'name' => __('Mahfuzar Forms', 'mahfuzar-form-builder'),
                'singular_name' => __('Mahfuzar Form', 'mahfuzar-form-builder'),
            ),
            'public' => false,
            'show_ui' => false,
            'supports' => array('title'),
            'capability_type' => 'post',
            'map_meta_cap' => true,
        ));

        register_post_type('webform_entry', array(
            'labels' => array(
                'name' => __('Entries', 'mahfuzar-form-builder'),
                'singular_name' => __('Entry', 'mahfuzar-form-builder'),
            ),
            'public' => false,
            'show_ui' => false,
            'supports' => array('title'),
            'capability_type' => 'post',
            'map_meta_cap' => true,
        ));
    }

    public static function activate() {
        self::instance()->register_post_types();
        flush_rewrite_rules();
    }
}
