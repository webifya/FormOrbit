<?php
/**
 * Plugin Name: Webform
 * Plugin URI: https://www.webninjallc.com/
 * Description: A visual drag-and-drop, multi-step form builder for WordPress.
 * Version: 2.2.7
 * Author: Mahfuzar Rahman
 * Author URI: https://profiles.wordpress.org/mahfuzar/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: webform
 * Requires at least: 6.2
 * Requires PHP: 7.4
 */

defined('ABSPATH') || exit;

define('WEBFORM_VERSION', '2.2.7');
define('WEBFORM_EDITION', 'free');
define('WEBFORM_FILE', __FILE__);
define('WEBFORM_PATH', plugin_dir_path(__FILE__));
define('WEBFORM_URL', plugin_dir_url(__FILE__));

require_once WEBFORM_PATH . 'includes/class-webform.php';

register_activation_hook(__FILE__, array('Webform', 'activate'));
Webform::instance();
