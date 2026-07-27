<?php

defined('ABSPATH') || exit;

class Webform_Public {
    public function __construct() {
        add_shortcode('webform', array($this, 'shortcode'));
        add_action('wp_ajax_webform_submit', array($this, 'submit'));
        add_action('wp_ajax_nopriv_webform_submit', array($this, 'submit'));
    }

    public function shortcode($atts) {
        $atts = shortcode_atts(array('id' => 0), $atts, 'webform');
        $form_id = absint($atts['id']);
        if (!$form_id || get_post_type($form_id) !== 'webform_form') {
            return current_user_can('manage_options') ? '<p>' . esc_html__('Webform not found.', 'webform') . '</p>' : '';
        }
        $schema = get_post_meta($form_id, '_webform_schema', true);
        $settings = get_post_meta($form_id, '_webform_settings', true);
        if (!$schema) {
            return '';
        }
        wp_enqueue_style('webform-public', WEBFORM_URL . 'assets/css/public.css', array(), WEBFORM_VERSION);
        wp_enqueue_script('webform-public', WEBFORM_URL . 'assets/js/public.js', array(), WEBFORM_VERSION, true);
        wp_localize_script('webform-public', 'WebformPublic', array('ajaxUrl' => admin_url('admin-ajax.php')));
        ob_start();
        ?>
        <div class="webform-public" data-form-id="<?php echo esc_attr($form_id); ?>">
            <?php if (count($schema) > 1) : ?><div class="webform-progress" role="progressbar" aria-valuemin="1" aria-valuemax="<?php echo esc_attr(count($schema)); ?>" aria-valuenow="1"><div class="webform-progress-bar"></div></div><ol class="webform-steps"><?php foreach ($schema as $index => $stage) : ?><li class="<?php echo $index === 0 ? 'is-active' : ''; ?>" <?php echo $index === 0 ? 'aria-current="step"' : ''; ?>><?php echo esc_html($stage['title']); ?></li><?php endforeach; ?></ol><?php endif; ?>
            <form novalidate enctype="multipart/form-data">
                <input type="hidden" name="action" value="webform_submit">
                <input type="hidden" name="form_id" value="<?php echo esc_attr($form_id); ?>">
                <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('webform_submit_' . $form_id)); ?>">
                <input type="text" name="website" class="webform-honeypot" tabindex="-1" autocomplete="off">
                <?php foreach ($schema as $stage_index => $stage) : ?>
                    <section class="webform-stage <?php echo $stage_index === 0 ? 'is-active' : ''; ?>" data-stage="<?php echo esc_attr($stage_index); ?>" <?php echo $stage_index === 0 ? '' : 'hidden'; ?>>
                        <?php if (count($schema) > 1) : ?><h2><?php echo esc_html($stage['title']); ?></h2><?php endif; ?>
                        <?php foreach ($stage['fields'] as $field) : $this->render_field($field); endforeach; ?>
                        <div class="webform-actions">
                            <?php if ($stage_index > 0) : ?><button type="button" class="webform-prev"><?php esc_html_e('Back', 'webform'); ?></button><?php endif; ?>
                            <?php if ($stage_index < count($schema) - 1) : ?><button type="button" class="webform-next"><?php esc_html_e('Continue', 'webform'); ?></button><?php else : ?><button type="submit" class="webform-submit"><?php echo esc_html($settings['submit_label'] ?? __('Submit', 'webform')); ?></button><?php endif; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
                <div class="webform-message" role="status" aria-live="polite"></div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    private function render_field($field) {
        $id = 'webform-' . $field['id'];
        $name = 'fields[' . $field['id'] . ']';
        if ($field['type'] === 'heading') {
            echo '<h3 class="webform-heading">' . esc_html($field['label']) . '</h3>';
            return;
        }
        $required = !empty($field['required']) ? ' required' : '';
        $describedby = $id . '-error';
        $condition = !empty($field['condition']['enabled']) ? $field['condition'] : array();
        $condition_attr = $condition ? ' data-condition="' . esc_attr(wp_json_encode($condition)) . '"' : '';
        if (in_array($field['type'], array('radio', 'checkbox'), true)) {
            ?>
            <fieldset class="webform-field webform-field-<?php echo esc_attr($field['type']); ?>"<?php echo $condition_attr; ?>>
                <legend><?php echo esc_html($field['label']); ?><?php if ($required) : ?> <span aria-hidden="true">*</span><?php endif; ?></legend>
                <div class="webform-choices" <?php echo $field['type'] === 'checkbox' && $required ? 'data-required="true"' : ''; ?>>
                    <?php foreach ($field['options'] as $index => $option) : $option_id = $id . '-' . $index; ?>
                        <label for="<?php echo esc_attr($option_id); ?>"><input id="<?php echo esc_attr($option_id); ?>" type="<?php echo esc_attr($field['type']); ?>" name="<?php echo esc_attr($name . ($field['type'] === 'checkbox' ? '[]' : '')); ?>" value="<?php echo esc_attr($option); ?>" aria-describedby="<?php echo esc_attr($describedby); ?>"<?php echo $field['type'] === 'radio' && $required && $index === 0 ? ' required' : ''; ?>> <?php echo esc_html($option); ?></label>
                    <?php endforeach; ?>
                </div>
                <span class="webform-error" id="<?php echo esc_attr($describedby); ?>"></span>
            </fieldset>
            <?php
            return;
        }
        if ($field['type'] === 'consent') {
            ?>
            <div class="webform-field webform-field-consent"<?php echo $condition_attr; ?>>
                <label for="<?php echo esc_attr($id); ?>"><input id="<?php echo esc_attr($id); ?>" type="checkbox" name="<?php echo esc_attr($name); ?>" value="Yes" aria-describedby="<?php echo esc_attr($describedby); ?>"<?php echo $required; ?>> <?php echo esc_html($field['label']); ?><?php if ($required) : ?> <span aria-hidden="true">*</span><?php endif; ?></label>
                <span class="webform-error" id="<?php echo esc_attr($describedby); ?>"></span>
            </div>
            <?php
            return;
        }
        ?>
        <div class="webform-field webform-field-<?php echo esc_attr($field['type']); ?>"<?php echo $condition_attr; ?>>
            <label for="<?php echo esc_attr($id); ?>"><?php echo esc_html($field['label']); ?><?php if ($required) : ?> <span aria-hidden="true">*</span><?php endif; ?></label>
            <?php if ($field['type'] === 'textarea') : ?>
                <textarea id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" maxlength="10000" aria-describedby="<?php echo esc_attr($describedby); ?>" placeholder="<?php echo esc_attr($field['placeholder']); ?>"<?php echo $required; ?>></textarea>
            <?php elseif ($field['type'] === 'select') : ?>
                <select id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" aria-describedby="<?php echo esc_attr($describedby); ?>"<?php echo $required; ?>><option value=""><?php esc_html_e('Select an option', 'webform'); ?></option><?php foreach ($field['options'] as $option) : ?><option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option><?php endforeach; ?></select>
            <?php elseif ($field['type'] === 'file') : ?>
                <input type="file" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" accept="<?php echo esc_attr(implode(',', array_map(function ($ext) { return '.' . trim($ext); }, explode(',', $field['allowed_extensions'])))); ?>" aria-describedby="<?php echo esc_attr($describedby); ?>"<?php echo $required; ?>>
                <small><?php echo esc_html(sprintf(__('Allowed: %1$s. Maximum: %2$d MB.', 'webform'), $field['allowed_extensions'], $field['max_size'])); ?></small>
            <?php else : ?>
                <?php $type = in_array($field['type'], array('email', 'number', 'date', 'url'), true) ? $field['type'] : ($field['type'] === 'phone' ? 'tel' : 'text'); ?>
                <input type="<?php echo esc_attr($type); ?>" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" <?php echo in_array($type, array('text', 'email', 'tel'), true) ? 'maxlength="1000"' : ''; ?> aria-describedby="<?php echo esc_attr($describedby); ?>" placeholder="<?php echo esc_attr($field['placeholder']); ?>"<?php echo $required; ?>>
            <?php endif; ?>
            <span class="webform-error" id="<?php echo esc_attr($describedby); ?>"></span>
        </div>
        <?php
    }

    public function submit() {
        $form_id = isset($_POST['form_id']) ? absint($_POST['form_id']) : 0;
        if (!$form_id || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'] ?? '')), 'webform_submit_' . $form_id)) {
            wp_send_json_error(array('message' => __('Your session expired. Refresh and try again.', 'webform')), 403);
        }
        if (!empty($_POST['website'])) {
            wp_send_json_success(array('message' => __('Thanks! Your response has been submitted.', 'webform')));
        }
        $rate_key = 'webform_rate_' . md5($form_id . '|' . $this->client_ip());
        $rate_count = (int) get_transient($rate_key);
        $rate_limit = max(1, (int) apply_filters('webform_rate_limit', 20, $form_id));
        if ($rate_count >= $rate_limit) {
            wp_send_json_error(array('message' => __('Too many submissions. Please wait and try again.', 'webform')), 429);
        }
        set_transient($rate_key, $rate_count + 1, MINUTE_IN_SECONDS * 10);
        $schema = get_post_meta($form_id, '_webform_schema', true);
        if (!$schema || get_post_status($form_id) !== 'publish') {
            wp_send_json_error(array('message' => __('This form is unavailable.', 'webform')), 404);
        }
        $posted = isset($_POST['fields']) && is_array($_POST['fields']) ? wp_unslash($_POST['fields']) : array();
        $data = array();
        $errors = array();
        foreach ($schema as $stage) {
            foreach ($stage['fields'] as $field) {
                if ($field['type'] === 'heading') continue;
                if (!$this->condition_passes($field['condition'] ?? array(), $posted)) continue;
                $value = $posted[$field['id']] ?? '';
                if ($field['type'] === 'file') {
                    $upload = $this->handle_upload($field);
                    if (is_wp_error($upload)) {
                        $errors[$field['id']] = $upload->get_error_message();
                        $value = '';
                    } else {
                        $value = $upload;
                    }
                }
                $value = is_array($value) ? array_slice(array_map('sanitize_text_field', $value), 0, 100) : substr(sanitize_textarea_field($value), 0, 10000);
                if (!empty($field['required']) && (empty($value) && $value !== '0')) {
                    $errors[$field['id']] = sprintf(__('%s is required.', 'webform'), $field['label']);
                } elseif ($field['type'] === 'email' && $value && !is_email($value)) {
                    $errors[$field['id']] = __('Enter a valid email address.', 'webform');
                } elseif ($field['type'] === 'url' && $value && !wp_http_validate_url($value)) {
                    $errors[$field['id']] = __('Enter a valid URL.', 'webform');
                } elseif (in_array($field['type'], array('select', 'radio'), true) && $value && !in_array($value, $field['options'], true)) {
                    $errors[$field['id']] = __('Select a valid option.', 'webform');
                } elseif ($field['type'] === 'checkbox' && array_diff((array) $value, $field['options'])) {
                    $errors[$field['id']] = __('Select valid options.', 'webform');
                }
                $data[$field['id']] = array('label' => $field['label'], 'value' => $value);
            }
        }
        if ($errors) {
            wp_send_json_error(array('message' => __('Please correct the highlighted fields.', 'webform'), 'errors' => $errors), 422);
        }
        $entry_id = wp_insert_post(array('post_type' => 'webform_entry', 'post_status' => 'private', 'post_title' => sprintf(__('Submission for %s', 'webform'), get_the_title($form_id))));
        if (!$entry_id || is_wp_error($entry_id)) {
            wp_send_json_error(array('message' => __('We could not save your submission. Please try again.', 'webform')), 500);
        }
        update_post_meta($entry_id, '_webform_form_id', $form_id);
        update_post_meta($entry_id, '_webform_data', $data);
        $settings = get_post_meta($form_id, '_webform_settings', true);
        if (!empty($settings['notification_email']) && is_email($settings['notification_email'])) {
            $lines = array();
            foreach ($data as $item) $lines[] = $item['label'] . ': ' . (is_array($item['value']) ? implode(', ', $item['value']) : $item['value']);
            wp_mail($settings['notification_email'], sprintf(__('New submission: %s', 'webform'), get_the_title($form_id)), implode("\n", $lines));
        }
        if (!empty($settings['webhook_url']) && wp_http_validate_url($settings['webhook_url'])) {
            wp_safe_remote_post($settings['webhook_url'], array('timeout' => 5, 'blocking' => false, 'headers' => array('Content-Type' => 'application/json'), 'body' => wp_json_encode(array('form_id' => $form_id, 'entry_id' => $entry_id, 'form_title' => get_the_title($form_id), 'fields' => $data))));
        }
        /**
         * Fires after an entry is stored and core notifications are dispatched.
         *
         * Premium and third-party add-ons can use this hook for email marketing,
         * payment fulfillment, automation, and CRM integrations.
         */
        do_action('webform_after_submission', $entry_id, $form_id, $data, $settings);
        wp_send_json_success(array('message' => $settings['success_message'] ?? __('Thanks! Your response has been submitted.', 'webform'), 'redirect_url' => !empty($settings['redirect_url']) ? $settings['redirect_url'] : ''));
    }

    private function condition_passes($condition, $posted) {
        if (empty($condition['enabled']) || empty($condition['field_id'])) return true;
        $actual = $posted[$condition['field_id']] ?? '';
        $actual = is_array($actual) ? implode(', ', array_map('sanitize_text_field', $actual)) : sanitize_text_field($actual);
        $expected = (string) ($condition['value'] ?? '');
        switch ($condition['operator'] ?? 'equals') {
            case 'not_equals': return $actual !== $expected;
            case 'contains': return stripos($actual, $expected) !== false;
            case 'not_empty': return $actual !== '';
            case 'empty': return $actual === '';
            default: return $actual === $expected;
        }
    }

    private function handle_upload($field) {
        if (empty($_FILES['fields']['name'][$field['id']])) return '';
        $file = array(
            'name' => sanitize_file_name($_FILES['fields']['name'][$field['id']]),
            'type' => sanitize_mime_type($_FILES['fields']['type'][$field['id']]),
            'tmp_name' => $_FILES['fields']['tmp_name'][$field['id']],
            'error' => absint($_FILES['fields']['error'][$field['id']]),
            'size' => absint($_FILES['fields']['size'][$field['id']]),
        );
        if ($file['size'] > absint($field['max_size']) * MB_IN_BYTES) return new WP_Error('file_size', __('The uploaded file is too large.', 'webform'));
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = array_filter(array_map('trim', explode(',', $field['allowed_extensions'])));
        if (!$extension || !in_array($extension, $allowed, true)) return new WP_Error('file_type', __('This file type is not allowed.', 'webform'));
        require_once ABSPATH . 'wp-admin/includes/file.php';
        $uploaded = wp_handle_upload($file, array('test_form' => false));
        return !empty($uploaded['error']) ? new WP_Error('upload_error', $uploaded['error']) : esc_url_raw($uploaded['url']);
    }

    private function client_ip() {
        // REMOTE_ADDR is deliberately hashed before transient storage.
        return isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'unknown';
    }
}
