<?php

defined('ABSPATH') || exit; // FormOrbit Pro saved progress.

class FormOrbit_Drafts {
    public function __construct() {
        add_action('wp_ajax_webform_pro_save_progress', array($this, 'save'));
        add_action('wp_ajax_nopriv_webform_pro_save_progress', array($this, 'save'));
        add_action('wp_ajax_formorbit_pro_save_progress', array($this, 'save'));
        add_action('wp_ajax_nopriv_formorbit_pro_save_progress', array($this, 'save'));
        add_action('webform_form_hidden_fields', array($this, 'hidden_fields'), 10, 2);
        add_action('webform_stage_actions', array($this, 'stage_action'), 10, 4);
        add_filter('webform_existing_entry_id', array($this, 'existing_entry'), 10, 2);
        add_action('wp_scheduled_delete', array($this, 'cleanup'));
    }

    public function stage_action($form_id, $settings) {
        if (empty($settings['save_progress_enabled'])) return;
        echo '<button type="button" class="webform-save-progress">' . esc_html($settings['save_progress_label'] ?? __('Save and continue later', 'formorbit')) . '</button>';
    }

    public function hidden_fields($form_id, $settings) {
        if (empty($settings['save_progress_enabled'])) return;
        $token = isset($_GET['webform_resume']) ? sanitize_text_field(wp_unslash($_GET['webform_resume'])) : '';
        $draft = $token ? $this->find($form_id, $token) : null;
        echo '<input type="hidden" name="draft_token" value="' . esc_attr($draft ? $token : '') . '">';
        if (!$draft) return;
        $payload = array(
            'fields' => (array) get_post_meta($draft->ID, '_webform_draft_values', true),
            'stage' => absint(get_post_meta($draft->ID, '_webform_draft_stage', true)),
            'message' => __('Your saved progress has been restored.', 'formorbit'),
        );
        echo '<script type="application/json" class="webform-draft-data">' . wp_json_encode($payload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . '</script>';
    }

    public function save() {
        $form_id = absint($_POST['form_id'] ?? 0);
        if (!$form_id || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'] ?? '')), 'webform_submit_' . $form_id)) {
            wp_send_json_error(array('code' => 'expired_nonce', 'message' => __('Your session expired. Refresh the page and try again.', 'formorbit')), 403);
        }
        $settings = (array) get_post_meta($form_id, '_webform_settings', true);
        if (empty($settings['save_progress_enabled']) || get_post_type($form_id) !== 'webform_form' || get_post_status($form_id) !== 'publish') {
            wp_send_json_error(array('message' => __('Saving progress is unavailable for this form.', 'formorbit')), 403);
        }
        if (!empty($settings['require_login']) && !is_user_logged_in()) wp_send_json_error(array('message' => __('Please log in to save this form.', 'formorbit')), 403);
        $rate_key = 'webform_draft_rate_' . md5($form_id . '|' . sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? '')));
        $rate_count = absint(get_transient($rate_key));
        if ($rate_count >= 20) wp_send_json_error(array('message' => __('Too many save requests. Please wait and try again.', 'formorbit')), 429);
        set_transient($rate_key, $rate_count + 1, 10 * MINUTE_IN_SECONDS);
        $posted = isset($_POST['fields']) && is_array($_POST['fields']) ? wp_unslash($_POST['fields']) : array();
        $schema = (array) get_post_meta($form_id, '_webform_schema', true);
        $values = array();
        $admin_data = array();
        $email = '';
        foreach ($schema as $stage) {
            foreach ((array) ($stage['fields'] ?? array()) as $field) {
                $id = sanitize_key($field['id'] ?? '');
                if (!$id || in_array($field['type'] ?? '', array('file', 'captcha', 'signature', 'html', 'heading'), true)) continue;
                $value = $posted[$id] ?? '';
                $value = $this->sanitize_draft_value($value);
                if ($value === '' || $value === array()) continue;
                $values[$id] = $value;
                $admin_data[$id] = array('label' => sanitize_text_field($field['label'] ?? $id), 'value' => $value);
                if (!$email && ($field['type'] ?? '') === 'email' && is_email($value)) $email = sanitize_email($value);
            }
        }
        $token = sanitize_text_field(wp_unslash($_POST['draft_token'] ?? ''));
        $draft = $token ? $this->find($form_id, $token) : null;
        if (!$draft) {
            $token = bin2hex(random_bytes(24));
            $entry_id = wp_insert_post(array(
                'post_type' => 'webform_entry',
                'post_status' => 'private',
                'post_title' => sprintf(__('Draft for %s', 'formorbit'), get_the_title($form_id)),
            ));
            if (!$entry_id || is_wp_error($entry_id)) wp_send_json_error(array('message' => __('The draft could not be saved.', 'formorbit')), 500);
            update_post_meta($entry_id, '_webform_form_id', $form_id);
            update_post_meta($entry_id, '_webform_user_id', get_current_user_id());
            update_post_meta($entry_id, '_webform_draft_token_hash', hash('sha256', $token));
        } else {
            $entry_id = $draft->ID;
        }
        $days = min(90, max(1, absint($settings['save_progress_days'] ?? 30)));
        update_post_meta($entry_id, '_webform_entry_status', 'draft');
        update_post_meta($entry_id, '_webform_draft_values', $values);
        update_post_meta($entry_id, '_webform_data', $admin_data);
        update_post_meta($entry_id, '_webform_draft_stage', absint($_POST['draft_stage'] ?? 0));
        update_post_meta($entry_id, '_webform_draft_expires', time() + DAY_IN_SECONDS * $days);
        $current_url = esc_url_raw(wp_unslash($_POST['current_url'] ?? ''));
        $site_host = wp_parse_url(home_url('/'), PHP_URL_HOST);
        if (!$current_url || !wp_http_validate_url($current_url) || wp_parse_url($current_url, PHP_URL_HOST) !== $site_host) $current_url = home_url('/');
        $resume_url = add_query_arg('webform_resume', rawurlencode($token), remove_query_arg('webform_resume', $current_url));
        if ($email) {
            wp_mail($email, sprintf(__('Resume your %s form', 'formorbit'), get_the_title($form_id)), sprintf(__("Your progress has been saved.\n\nResume here: %s\n\nThis link expires in %d days.", 'formorbit'), $resume_url, $days));
        }
        wp_send_json_success(array(
            'message' => $email ? __('Progress saved. We emailed your secure resume link.', 'formorbit') : __('Progress saved. Copy the secure link below to continue later.', 'formorbit'),
            'resume_url' => esc_url_raw($resume_url),
            'token' => $token,
        ));
    }

    private function sanitize_draft_value($value, $depth = 0) {
        if ($depth > 4) return '';
        if (!is_array($value)) return substr(sanitize_textarea_field($value), 0, 10000);
        $clean = array();
        foreach (array_slice($value, 0, 100, true) as $key => $item) $clean[sanitize_key((string) $key)] = $this->sanitize_draft_value($item, $depth + 1);
        return $clean;
    }

    public function existing_entry($entry_id, $form_id) {
        $token = sanitize_text_field(wp_unslash($_POST['draft_token'] ?? ''));
        $draft = $token ? $this->find($form_id, $token) : null;
        return $draft ? $draft->ID : $entry_id;
    }

    private function find($form_id, $token) {
        if (!preg_match('/^[a-f0-9]{48}$/', $token)) return null;
        $query = new WP_Query(array(
            'post_type' => 'webform_entry',
            'post_status' => 'private',
            'posts_per_page' => 1,
            'meta_query' => array(
                'relation' => 'AND',
                array('key' => '_webform_form_id', 'value' => absint($form_id)),
                array('key' => '_webform_entry_status', 'value' => 'draft'),
                array('key' => '_webform_draft_token_hash', 'value' => hash('sha256', $token)),
            ),
        ));
        $draft = $query->posts ? $query->posts[0] : null;
        if (!$draft || absint(get_post_meta($draft->ID, '_webform_draft_expires', true)) < time()) return null;
        $owner = absint(get_post_meta($draft->ID, '_webform_user_id', true));
        if ($owner && $owner !== get_current_user_id()) return null;
        return $draft;
    }

    public function cleanup() {
        $expired = get_posts(array(
            'post_type' => 'webform_entry',
            'post_status' => 'private',
            'posts_per_page' => 100,
            'fields' => 'ids',
            'meta_query' => array(
                'relation' => 'AND',
                array('key' => '_webform_entry_status', 'value' => 'draft'),
                array('key' => '_webform_draft_expires', 'value' => time(), 'compare' => '<', 'type' => 'NUMERIC'),
            ),
        ));
        foreach ($expired as $entry_id) wp_delete_post($entry_id, true);
    }
}
