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
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        new Webform_Admin();
        new Webform_Public();
        new Webform_Mailer();
        do_action('webform_loaded', $this);
    }

    public function load_textdomain() {
        load_plugin_textdomain('webform', false, dirname(plugin_basename(WEBFORM_FILE)) . '/languages');
    }

    public function register_post_types() {
        register_post_type('webform_form', array(
            'labels' => array(
                'name' => __('Webforms', 'webform'),
                'singular_name' => __('Webform', 'webform'),
            ),
            'public' => false,
            'show_ui' => false,
            'supports' => array('title'),
            'capability_type' => 'post',
            'map_meta_cap' => true,
        ));

        register_post_type('webform_entry', array(
            'labels' => array(
                'name' => __('Entries', 'webform'),
                'singular_name' => __('Entry', 'webform'),
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
