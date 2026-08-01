<?php
/**
 * Plugin Name: FormOrbit
 * Plugin URI: https://www.webninjallc.com/plugins/formorbit/
 * Description: Build responsive contact, multi-step, survey, quiz and registration forms with visual editing, templates, SMTP, analytics and secure entries.
 * Version: 4.9.2
 * Author: Mahfuzar Rahman
 * Author URI: https://profiles.wordpress.org/mahfuzar/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: formorbit
 * Requires at least: 6.2
 * Requires PHP: 7.4
 */

defined('ABSPATH') || exit;

if (defined('WEBFORM_FILE')) {
    return;
}

define('WEBFORM_VERSION', '4.9.2');
define('WEBFORM_EDITION', 'free');
define('WEBFORM_FILE', __FILE__);
define('WEBFORM_PATH', plugin_dir_path(__FILE__));
define('WEBFORM_URL', plugin_dir_url(__FILE__));

require_once WEBFORM_PATH . 'includes/class-formorbit.php';

register_activation_hook(__FILE__, array('Webform', 'activate'));
register_deactivation_hook(__FILE__, array('Webform', 'deactivate'));
Webform::instance();
