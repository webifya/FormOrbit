<?php
/**
 * Plugin Name: Webform
 * Description: A visual drag-and-drop, multi-step form builder for WordPress.
 * Version: 1.2.0
 * Author: Webifya
 * Text Domain: webform
 * Requires at least: 6.2
 * Requires PHP: 7.4
 */

defined('ABSPATH') || exit;

define('WEBFORM_VERSION', '1.2.0');
define('WEBFORM_FILE', __FILE__);
define('WEBFORM_PATH', plugin_dir_path(__FILE__));
define('WEBFORM_URL', plugin_dir_url(__FILE__));

require_once WEBFORM_PATH . 'includes/class-webform.php';

register_activation_hook(__FILE__, array('Webform', 'activate'));
Webform::instance();
