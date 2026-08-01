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
        require_once WEBFORM_PATH . 'includes/class-formorbit-admin.php';
        require_once WEBFORM_PATH . 'includes/class-formorbit-public.php';
        require_once WEBFORM_PATH . 'includes/class-formorbit-mailer.php';
        require_once WEBFORM_PATH . 'includes/class-formorbit-site-profile.php';

        add_action('init', array($this, 'register_post_types'));
        new Webform_Admin();
        new Webform_Public();
        new Webform_Mailer();
        new FormOrbit_Site_Profile();
        do_action('webform_loaded', $this);
    }

    public function register_post_types() {
        register_post_type('webform_form', array(
            'labels' => array(
                'name' => __('FormOrbit', 'formorbit'),
                'singular_name' => __('FormOrbit Form', 'formorbit'),
            ),
            'public' => false,
            'show_ui' => false,
            'supports' => array('title'),
            'capability_type' => 'post',
            'map_meta_cap' => true,
        ));

        register_post_type('webform_entry', array(
            'labels' => array(
                'name' => __('Entries', 'formorbit'),
                'singular_name' => __('Entry', 'formorbit'),
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
        if (!get_option('formorbit_activated_at')) update_option('formorbit_activated_at', time(), false);
        if (!wp_next_scheduled('formorbit_daily_site_profile')) wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'formorbit_daily_site_profile');
        flush_rewrite_rules();
    }

    public static function deactivate() {
        wp_clear_scheduled_hook('formorbit_daily_site_profile');
    }
}
