<?php

defined('ABSPATH') || exit;

final class FormOrbit_Site_Profile {
    const ENDPOINT = 'https://www.webninjallc.com/wp-json/wnlm/v1/site-profile';
    const CRON_HOOK = 'formorbit_daily_site_profile';
    const INSTANCE_OPTION = 'formorbit_site_instance_id';
    const STATUS_OPTION = 'formorbit_site_profile_status';

    public function __construct() {
        add_action(self::CRON_HOOK, array($this, 'send'));
        add_action('formorbit_site_profile_sync', array($this, 'send'));
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', self::CRON_HOOK);
        }
    }

    public static function enabled() {
        $pro_license = (array) get_option('webform_pro_license', array());
        $pro_status = (array) get_option('webform_pro_license_status', array());
        if (!empty($pro_license['license_key']) && !empty($pro_status['valid'])) return true;
        $settings = (array) get_option('webform_global_settings', array());
        return !empty($settings['usage_sharing_enabled']);
    }

    public function send() {
        if (!self::enabled()) return false;

        $products = array(array(
            'product' => 'formorbit',
            'version' => defined('WEBFORM_VERSION') ? WEBFORM_VERSION : '',
            'edition' => 'free',
            'license_key' => '',
        ));
        if (defined('WEBFORM_PRO_PLUGIN_VERSION')) {
            $license = (array) get_option('webform_pro_license', array());
            $products[] = array(
                'product' => 'formorbit-pro',
                'version' => WEBFORM_PRO_PLUGIN_VERSION,
                'edition' => 'pro',
                'license_key' => sanitize_text_field($license['license_key'] ?? ''),
            );
        }

        $site_data = $this->site_data();
        $results = array();
        foreach ($products as $product) {
            $payload = array_merge($site_data, $product, array(
                'instance_id' => $this->instance_id(),
                'site_url' => home_url('/'),
                'site_data' => wp_json_encode(array_merge($site_data, $product)),
                'profile' => wp_json_encode(array_merge($site_data, $product)),
                'site_profile' => wp_json_encode(array_merge($site_data, $product)),
                'plugin' => $product['product'],
                'plugin_slug' => $product['product'],
                'product_slug' => $product['product'],
                'plugin_version' => $product['version'],
                'contact_email' => $site_data['admin_email'],
                'license' => $product['license_key'],
                'telemetry_consent' => 'yes',
            ));
            $response = wp_safe_remote_post(self::ENDPOINT, array(
                'timeout' => 10,
                'redirection' => 2,
                'headers' => array(
                    'Accept' => 'application/json',
                    'User-Agent' => 'FormOrbit/' . (defined('WEBFORM_VERSION') ? WEBFORM_VERSION : 'unknown') . '; ' . home_url('/'),
                ),
                'body' => $payload,
            ));
            $results[$product['product']] = $this->response_status($response);
        }

        update_option(self::STATUS_OPTION, array(
            'last_sent' => time(),
            'results' => $results,
        ), false);
        return $results;
    }

    private function instance_id() {
        $pro = (array) get_option('webform_pro_license', array());
        if (!empty($pro['instance_id'])) return sanitize_text_field($pro['instance_id']);
        $instance_id = sanitize_text_field(get_option(self::INSTANCE_OPTION, ''));
        if (!$instance_id) {
            $instance_id = wp_generate_uuid4();
            update_option(self::INSTANCE_OPTION, $instance_id, false);
        }
        return $instance_id;
    }

    private function site_data() {
        global $wp_version;
        $theme = wp_get_theme();
        $message = '';
        if (is_array($body)) {
            $message = $body['message'] ?? ($body['data']['message'] ?? ($body['code'] ?? ''));
        }
        return array(
            'site_name' => sanitize_text_field(get_bloginfo('name')),
            'admin_email' => sanitize_email(get_option('admin_email')),
            'wordpress_url' => esc_url_raw(site_url('/')),
            'wordpress_version' => sanitize_text_field((string) $wp_version),
            'php_version' => sanitize_text_field(PHP_VERSION),
            'formorbit_version' => defined('WEBFORM_VERSION') ? sanitize_text_field(WEBFORM_VERSION) : '',
            'formorbit_pro_version' => defined('WEBFORM_PRO_PLUGIN_VERSION') ? sanitize_text_field(WEBFORM_PRO_PLUGIN_VERSION) : '',
            'locale' => sanitize_text_field(function_exists('determine_locale') ? determine_locale() : get_locale()),
            'timezone' => sanitize_text_field(wp_timezone_string()),
            'environment' => sanitize_key(function_exists('wp_get_environment_type') ? wp_get_environment_type() : 'production'),
            'multisite' => is_multisite() ? 'yes' : 'no',
            'theme' => sanitize_text_field($theme->get('Name')),
            'theme_version' => sanitize_text_field($theme->get('Version')),
        );
    }

    private function response_status($response) {
        if (is_wp_error($response)) {
            return array('success' => false, 'code' => 0, 'message' => sanitize_text_field($response->get_error_message()));
        }
        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);
        return array(
            'success' => $code >= 200 && $code < 300,
            'code' => absint($code),
            'message' => sanitize_text_field($message),
        );
    }
}
