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
        if (!$schema) {
            return '';
        }
        wp_enqueue_style('webform-public', WEBFORM_URL . 'assets/css/public.css', array(), WEBFORM_VERSION);
        wp_enqueue_script('webform-public', WEBFORM_URL . 'assets/js/public.js', array(), WEBFORM_VERSION, true);
        wp_localize_script('webform-public', 'WebformPublic', array('ajaxUrl' => admin_url('admin-ajax.php')));
        ob_start();
        ?>
        <div class="webform-public" data-form-id="<?php echo esc_attr($form_id); ?>">
            <?php if (count($schema) > 1) : ?><div class="webform-progress"><div class="webform-progress-bar"></div></div><ol class="webform-steps"><?php foreach ($schema as $index => $stage) : ?><li class="<?php echo $index === 0 ? 'is-active' : ''; ?>"><?php echo esc_html($stage['title']); ?></li><?php endforeach; ?></ol><?php endif; ?>
            <form novalidate>
                <input type="hidden" name="action" value="webform_submit">
                <input type="hidden" name="form_id" value="<?php echo esc_attr($form_id); ?>">
                <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('webform_submit_' . $form_id)); ?>">
                <input type="text" name="website" class="webform-honeypot" tabindex="-1" autocomplete="off">
                <?php foreach ($schema as $stage_index => $stage) : ?>
                    <section class="webform-stage <?php echo $stage_index === 0 ? 'is-active' : ''; ?>" data-stage="<?php echo esc_attr($stage_index); ?>">
                        <?php if (count($schema) > 1) : ?><h2><?php echo esc_html($stage['title']); ?></h2><?php endif; ?>
                        <?php foreach ($stage['fields'] as $field) : $this->render_field($field); endforeach; ?>
                        <div class="webform-actions">
                            <?php if ($stage_index > 0) : ?><button type="button" class="webform-prev"><?php esc_html_e('Back', 'webform'); ?></button><?php endif; ?>
                            <?php if ($stage_index < count($schema) - 1) : ?><button type="button" class="webform-next"><?php esc_html_e('Continue', 'webform'); ?></button><?php else : ?><button type="submit" class="webform-submit"><?php esc_html_e('Submit', 'webform'); ?></button><?php endif; ?>
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
        ?>
        <div class="webform-field webform-field-<?php echo esc_attr($field['type']); ?>">
            <label for="<?php echo esc_attr($id); ?>"><?php echo esc_html($field['label']); ?><?php if ($required) : ?> <span aria-hidden="true">*</span><?php endif; ?></label>
            <?php if ($field['type'] === 'textarea') : ?>
                <textarea id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" placeholder="<?php echo esc_attr($field['placeholder']); ?>"<?php echo $required; ?>></textarea>
            <?php elseif ($field['type'] === 'select') : ?>
                <select id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>"<?php echo $required; ?>><option value=""><?php esc_html_e('Select an option', 'webform'); ?></option><?php foreach ($field['options'] as $option) : ?><option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option><?php endforeach; ?></select>
            <?php elseif ($field['type'] === 'radio') : ?>
                <div class="webform-choices"><?php foreach ($field['options'] as $index => $option) : ?><label><input type="radio" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($option); ?>"<?php echo $required && $index === 0 ? ' required' : ''; ?>> <?php echo esc_html($option); ?></label><?php endforeach; ?></div>
            <?php elseif ($field['type'] === 'checkbox') : ?>
                <div class="webform-choices"><?php foreach ($field['options'] as $index => $option) : ?><label><input type="checkbox" name="<?php echo esc_attr($name); ?>[]" value="<?php echo esc_attr($option); ?>"<?php echo $required && $index === 0 ? ' required' : ''; ?>> <?php echo esc_html($option); ?></label><?php endforeach; ?></div>
            <?php else : ?>
                <?php $type = in_array($field['type'], array('email', 'number', 'date'), true) ? $field['type'] : ($field['type'] === 'phone' ? 'tel' : 'text'); ?>
                <input type="<?php echo esc_attr($type); ?>" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" placeholder="<?php echo esc_attr($field['placeholder']); ?>"<?php echo $required; ?>>
            <?php endif; ?>
            <span class="webform-error"></span>
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
                $value = $posted[$field['id']] ?? '';
                $value = is_array($value) ? array_map('sanitize_text_field', $value) : sanitize_textarea_field($value);
                if (!empty($field['required']) && (empty($value) && $value !== '0')) {
                    $errors[$field['id']] = sprintf(__('%s is required.', 'webform'), $field['label']);
                } elseif ($field['type'] === 'email' && $value && !is_email($value)) {
                    $errors[$field['id']] = __('Enter a valid email address.', 'webform');
                }
                $data[$field['label']] = $value;
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
            foreach ($data as $label => $value) $lines[] = $label . ': ' . (is_array($value) ? implode(', ', $value) : $value);
            wp_mail($settings['notification_email'], sprintf(__('New submission: %s', 'webform'), get_the_title($form_id)), implode("\n", $lines));
        }
        wp_send_json_success(array('message' => $settings['success_message'] ?? __('Thanks! Your response has been submitted.', 'webform')));
    }
}
