<?php

defined('ABSPATH') || exit;

class Webform_Public {
    public function __construct() {
        add_shortcode('webform', array($this, 'shortcode'));
        add_action('template_redirect', array($this, 'preview'));
        add_action('wp_ajax_webform_submit', array($this, 'submit'));
        add_action('wp_ajax_nopriv_webform_submit', array($this, 'submit'));
    }

    public function preview() {
        $form_id = isset($_GET['webform_preview']) ? absint($_GET['webform_preview']) : 0;
        if (!$form_id) {
            return;
        }
        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
        if (!current_user_can('manage_options') || !wp_verify_nonce($nonce, 'webform_preview_' . $form_id)) {
            wp_die(esc_html__('You are not allowed to preview this form.', 'webform'), '', array('response' => 403));
        }
        $form = get_post($form_id);
        if (!$form || $form->post_type !== 'webform_form') {
            wp_die(esc_html__('Webform not found.', 'webform'), '', array('response' => 404));
        }
        wp_enqueue_style('webform-public', WEBFORM_URL . 'assets/css/public.css', array(), WEBFORM_VERSION);
        wp_enqueue_script('webform-public', WEBFORM_URL . 'assets/js/public.js', array(), WEBFORM_VERSION, true);
        status_header(200);
        nocache_headers();
        ?>
        <!doctype html><html <?php language_attributes(); ?>><head>
            <meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?php echo esc_html(sprintf(__('Preview: %s', 'webform'), $form->post_title)); ?></title>
            <?php wp_head(); ?>
            <style>.webform-preview-page{background:#f0f2f5;margin:0;padding:32px 18px}.webform-preview-toolbar{align-items:center;display:flex;justify-content:space-between;margin:0 auto 18px;max-width:900px}.webform-preview-toolbar strong{font:600 16px/1.4 -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}.webform-preview-toolbar a{background:#2271b1;border-radius:4px;color:#fff;font:600 13px/1 -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;padding:10px 14px;text-decoration:none}.webform-preview-frame{margin:auto;max-width:900px}</style>
        </head><body class="webform-preview-page">
            <div class="webform-preview-toolbar"><strong><?php echo esc_html(sprintf(__('Previewing “%s”', 'webform'), $form->post_title)); ?></strong><a href="<?php echo esc_url(admin_url('admin.php?page=webform-builder&form_id=' . $form_id)); ?>"><?php esc_html_e('Back to editor', 'webform'); ?></a></div>
            <main class="webform-preview-frame"><?php echo do_shortcode('[webform id="' . absint($form_id) . '"]'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></main>
            <?php wp_footer(); ?>
        </body></html>
        <?php
        exit;
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
        $availability_error = $this->availability_error($form_id, $settings);
        if ($availability_error) {
            return '<div class="webform-public webform-closed" role="status">' . esc_html($availability_error) . '</div>';
        }
        wp_enqueue_style('webform-public', WEBFORM_URL . 'assets/css/public.css', array(), WEBFORM_VERSION);
        wp_enqueue_script('webform-public', WEBFORM_URL . 'assets/js/public.js', array(), WEBFORM_VERSION, true);
        if ($this->google_recaptcha_enabled() && $this->schema_has_type($schema, 'captcha')) {
            $recaptcha = $this->recaptcha_settings();
            $script_url = $recaptcha['recaptcha_mode'] === 'enterprise' ? 'https://www.google.com/recaptcha/enterprise.js' : 'https://www.google.com/recaptcha/api.js';
            wp_enqueue_script('google-recaptcha', $script_url, array(), null, true);
        }
        wp_localize_script('webform-public', 'WebformPublic', array('ajaxUrl' => admin_url('admin-ajax.php')));
        ob_start();
        ?>
        <?php $available_presets = apply_filters('webform_style_presets', array('modern' => 'Modern', 'minimal' => 'Minimal', 'rounded' => 'Rounded')); $preset = array_key_exists($settings['style_preset'] ?? '', $available_presets) ? $settings['style_preset'] : 'modern'; $style_variables = apply_filters('webform_inline_style_variables', array('--wf-accent' => $settings['accent_color'] ?? '#6c4bd4', '--wf-button-text' => $settings['button_text_color'] ?? '#ffffff'), $settings, $form_id); $inline_style = ''; foreach ($style_variables as $property => $property_value) $inline_style .= sanitize_key($property) . ':' . sanitize_text_field($property_value) . ';'; do_action('webform_before_form_markup', $form_id, $settings); ?>
        <div id="webform-<?php echo esc_attr($form_id); ?>" class="webform-public webform-style-<?php echo esc_attr($preset); ?>" style="<?php echo esc_attr($inline_style); ?>" data-form-id="<?php echo esc_attr($form_id); ?>">
            <?php if (count($schema) > 1) : ?><div class="webform-progress" role="progressbar" aria-valuemin="1" aria-valuemax="<?php echo esc_attr(count($schema)); ?>" aria-valuenow="1"><div class="webform-progress-bar"></div></div><ol class="webform-steps"><?php foreach ($schema as $index => $stage) : ?><li class="<?php echo $index === 0 ? 'is-active' : ''; ?>" <?php echo $index === 0 ? 'aria-current="step"' : ''; ?>><?php echo esc_html($stage['title']); ?></li><?php endforeach; ?></ol><?php endif; ?>
            <form novalidate enctype="multipart/form-data">
                <input type="hidden" name="action" value="webform_submit">
                <input type="hidden" name="form_id" value="<?php echo esc_attr($form_id); ?>">
                <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('webform_submit_' . $form_id)); ?>">
                <input type="hidden" name="started_at" value="<?php echo esc_attr(time()); ?>">
                <?php do_action('webform_form_hidden_fields', $form_id, $settings); ?>
                <input type="text" name="website" class="webform-honeypot" tabindex="-1" autocomplete="off">
                <?php foreach ($schema as $stage_index => $stage) : ?>
                    <section class="webform-stage <?php echo $stage_index === 0 ? 'is-active' : ''; ?>" data-stage="<?php echo esc_attr($stage_index); ?>" <?php echo $stage_index === 0 ? '' : 'hidden'; ?>>
                        <?php if (count($schema) > 1) : ?><h2><?php echo esc_html($stage['title']); ?></h2><?php endif; ?>
                        <?php foreach ($stage['fields'] as $field) : ?>
                            <?php if (!empty($field['row_start'])) : ?><span class="webform-row-break" aria-hidden="true"></span><?php endif; ?>
                            <?php $this->render_field($field); ?>
                        <?php endforeach; ?>
                        <div class="webform-actions">
                            <?php do_action('webform_stage_actions', $form_id, $settings, $stage_index, count($schema)); ?>
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
        if (!$this->field_type_enabled($field)) return;
        $id = 'webform-' . $field['id'];
        $name = 'fields[' . $field['id'] . ']';
        $field_class = apply_filters('webform_field_custom_class', '', $field);
        $label_html = wp_kses(
            apply_filters('webform_field_label_html', esc_html($field['label']), $field),
            array('span' => array('class' => true, 'aria-hidden' => true))
        );
        $custom_html = apply_filters('webform_custom_field_html', '', $field, $id, $name);
        if ($custom_html !== '') {
            $allowed_custom_html = apply_filters('webform_custom_field_allowed_html', array(
                'div' => array('class' => true, 'data-*' => true),
                'fieldset' => array('class' => true, 'data-*' => true),
                'legend' => array(),
                'label' => array('for' => true),
                'input' => array('id' => true, 'class' => true, 'type' => true, 'name' => true, 'value' => true, 'readonly' => true, 'required' => true, 'data-*' => true),
                'canvas' => array('id' => true, 'class' => true, 'width' => true, 'height' => true, 'data-*' => true),
                'button' => array('type' => true, 'class' => true),
                'span' => array('class' => true, 'id' => true),
                'strong' => array('class' => true),
                'em' => array('class' => true),
                'small' => array(),
            ), $field);
            echo wp_kses($custom_html, $allowed_custom_html);
            return;
        }
        if ($field['type'] === 'hidden') {
            echo '<input type="hidden" name="' . esc_attr($name) . '" value="' . esc_attr($field['default_value']) . '">';
            return;
        }
        if ($field['type'] === 'html') {
            echo '<div class="webform-html ' . esc_attr($field_class) . '" data-field-id="' . esc_attr($field['id']) . '">' . wp_kses_post($field['html']) . '</div>';
            return;
        }
        if ($field['type'] === 'heading') {
            echo '<h3 class="webform-heading ' . esc_attr($field_class) . '" data-field-id="' . esc_attr($field['id']) . '">' . $label_html . '</h3>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            return;
        }
        $required = !empty($field['required']) ? ' required' : '';
        $describedby = $id . '-error';
        $condition = !empty($field['condition']['enabled']) ? $field['condition'] : array();
        $condition_attr = ' data-field-id="' . esc_attr($field['id']) . '"' . ($condition ? ' data-condition="' . esc_attr(wp_json_encode($condition)) . '"' : '');
        if ($field['type'] === 'name') {
            ?>
            <fieldset class="webform-field webform-field-name <?php echo esc_attr($field_class); ?>"<?php echo $condition_attr; ?>>
                <legend><?php echo $label_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php if ($required) : ?> <span aria-hidden="true">*</span><?php endif; ?></legend>
                <div class="webform-name-fields">
                    <label for="<?php echo esc_attr($id . '-first'); ?>"><span><?php esc_html_e('First name', 'webform'); ?></span><input id="<?php echo esc_attr($id . '-first'); ?>" type="text" name="<?php echo esc_attr($name . '[first]'); ?>" maxlength="100" autocomplete="given-name" aria-describedby="<?php echo esc_attr($describedby); ?>"<?php echo $required; ?>></label>
                    <label for="<?php echo esc_attr($id . '-last'); ?>"><span><?php esc_html_e('Last name', 'webform'); ?></span><input id="<?php echo esc_attr($id . '-last'); ?>" type="text" name="<?php echo esc_attr($name . '[last]'); ?>" maxlength="100" autocomplete="family-name" aria-describedby="<?php echo esc_attr($describedby); ?>"<?php echo $required; ?>></label>
                </div>
                <span class="webform-error" id="<?php echo esc_attr($describedby); ?>"></span>
            </fieldset>
            <?php
            return;
        }
        if (in_array($field['type'], array('radio', 'checkbox', 'poll', 'quiz'), true)) {
            $input_type = $field['type'] === 'checkbox' ? 'checkbox' : 'radio';
            ?>
            <fieldset class="webform-field webform-field-<?php echo esc_attr($field['type'] . ' ' . $field_class); ?>"<?php echo $condition_attr; ?>>
                <legend><?php echo $label_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php if ($required) : ?> <span aria-hidden="true">*</span><?php endif; ?></legend>
                <div class="webform-choices" <?php echo $field['type'] === 'checkbox' && $required ? 'data-required="true"' : ''; ?>>
                    <?php foreach ($field['options'] as $index => $option) : $option_id = $id . '-' . $index; ?>
                        <label for="<?php echo esc_attr($option_id); ?>"><input id="<?php echo esc_attr($option_id); ?>" type="<?php echo esc_attr($input_type); ?>" name="<?php echo esc_attr($name . ($input_type === 'checkbox' ? '[]' : '')); ?>" value="<?php echo esc_attr($option); ?>" aria-describedby="<?php echo esc_attr($describedby); ?>"<?php echo $input_type === 'radio' && $required && $index === 0 ? ' required' : ''; ?>> <?php echo esc_html($option); ?></label>
                    <?php endforeach; ?>
                </div>
                <span class="webform-error" id="<?php echo esc_attr($describedby); ?>"></span>
            </fieldset>
            <?php
            return;
        }
        if ($field['type'] === 'consent') {
            ?>
            <div class="webform-field webform-field-consent <?php echo esc_attr($field_class); ?>"<?php echo $condition_attr; ?>>
                <label for="<?php echo esc_attr($id); ?>"><input id="<?php echo esc_attr($id); ?>" type="checkbox" name="<?php echo esc_attr($name); ?>" value="Yes" aria-describedby="<?php echo esc_attr($describedby); ?>"<?php echo $required; ?>> <?php echo $label_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php if ($required) : ?> <span aria-hidden="true">*</span><?php endif; ?></label>
                <span class="webform-error" id="<?php echo esc_attr($describedby); ?>"></span>
            </div>
            <?php
            return;
        }
        if ($field['type'] === 'captcha') {
            $global_settings = (array) get_option('webform_global_settings', array());
            if ($this->google_recaptcha_enabled()) {
                $action_attr = ($global_settings['recaptcha_mode'] ?? 'enterprise') === 'enterprise' ? ' data-action="' . esc_attr($global_settings['recaptcha_action'] ?? 'WEBFORM_SUBMIT') . '"' : '';
                echo '<div class="webform-field webform-field-captcha ' . esc_attr($field_class) . '"' . $condition_attr . '><div class="g-recaptcha" data-sitekey="' . esc_attr($global_settings['recaptcha_site_key']) . '"' . $action_attr . '></div><span class="webform-error" id="' . esc_attr($describedby) . '"></span></div>';
                return;
            }
            $first = wp_rand(2, 9);
            $second = wp_rand(1, 9);
            $answer = $first + $second;
            $token = base64_encode($first . ':' . $second . ':' . wp_create_nonce('webform_captcha_' . $field['id'] . '_' . $answer));
            ?>
            <div class="webform-field webform-field-captcha <?php echo esc_attr($field_class); ?>"<?php echo $condition_attr; ?>>
                <label for="<?php echo esc_attr($id); ?>"><?php echo esc_html(sprintf(__('%1$d + %2$d = ?', 'webform'), $first, $second)); ?> <span aria-hidden="true">*</span></label>
                <input type="number" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" required>
                <input type="hidden" name="captcha_tokens[<?php echo esc_attr($field['id']); ?>]" value="<?php echo esc_attr($token); ?>">
                <span class="webform-error" id="<?php echo esc_attr($describedby); ?>"></span>
            </div>
            <?php
            return;
        }
        if ($field['type'] === 'rating') {
            ?>
            <fieldset class="webform-field webform-field-rating <?php echo esc_attr($field_class); ?>"<?php echo $condition_attr; ?>><legend><?php echo $label_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php if ($required) : ?> <span aria-hidden="true">*</span><?php endif; ?></legend><div class="webform-rating"><?php for ($rating = 5; $rating >= 1; $rating--) : ?><input id="<?php echo esc_attr($id . '-' . $rating); ?>" type="radio" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($rating); ?>" <?php echo $required && $rating === 1 ? 'required' : ''; ?>><label for="<?php echo esc_attr($id . '-' . $rating); ?>" aria-label="<?php echo esc_attr(sprintf(__('%d stars', 'webform'), $rating)); ?>">★</label><?php endfor; ?></div><span class="webform-error" id="<?php echo esc_attr($describedby); ?>"></span></fieldset>
            <?php
            return;
        }
        ?>
        <div class="webform-field webform-field-<?php echo esc_attr($field['type'] . ' ' . $field_class); ?>"<?php echo $condition_attr; ?>>
            <label for="<?php echo esc_attr($id); ?>"><?php echo $label_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php if ($required) : ?> <span aria-hidden="true">*</span><?php endif; ?></label>
            <?php if ($field['type'] === 'textarea') : ?>
                <textarea id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" rows="<?php echo esc_attr(min(30, max(2, absint($field['rows'] ?? 5)))); ?>" maxlength="10000" aria-describedby="<?php echo esc_attr($describedby); ?>" placeholder="<?php echo esc_attr($field['placeholder']); ?>"<?php echo $required; ?>></textarea>
            <?php elseif ($field['type'] === 'select') : ?>
                <select id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" aria-describedby="<?php echo esc_attr($describedby); ?>"<?php echo $required; ?>><option value=""><?php esc_html_e('Select an option', 'webform'); ?></option><?php foreach ($field['options'] as $option) : ?><option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option><?php endforeach; ?></select>
            <?php elseif ($field['type'] === 'file') : ?>
                <input type="file" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" accept="<?php echo esc_attr(implode(',', array_map(function ($ext) { return '.' . trim($ext); }, explode(',', $field['allowed_extensions'])))); ?>" aria-describedby="<?php echo esc_attr($describedby); ?>"<?php echo $required; ?>>
                <small><?php echo esc_html(sprintf(__('Allowed: %1$s. Maximum: %2$d MB.', 'webform'), $field['allowed_extensions'], $field['max_size'])); ?></small>
            <?php elseif ($field['type'] === 'slider') : ?>
                <input type="range" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" min="<?php echo esc_attr($field['min']); ?>" max="<?php echo esc_attr($field['max']); ?>" step="<?php echo esc_attr($field['step']); ?>" value="<?php echo esc_attr($field['min']); ?>"<?php echo $required; ?>><output class="webform-slider-value"><?php echo esc_html($field['min']); ?></output>
            <?php else : ?>
                <?php $type = in_array($field['type'], array('email', 'number', 'date', 'time', 'url'), true) ? $field['type'] : ($field['type'] === 'phone' ? 'tel' : 'text'); ?>
                <?php $date_bounds = $type === 'date' ? $this->date_bounds($field) : array('', ''); ?>
                <input type="<?php echo esc_attr($type); ?>" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" <?php echo in_array($type, array('text', 'email', 'tel'), true) ? 'maxlength="1000"' : ''; ?><?php if ($date_bounds[0]) : ?> min="<?php echo esc_attr($date_bounds[0]); ?>"<?php endif; ?><?php if ($date_bounds[1]) : ?> max="<?php echo esc_attr($date_bounds[1]); ?>"<?php endif; ?> aria-describedby="<?php echo esc_attr($describedby); ?>" placeholder="<?php echo esc_attr($field['placeholder']); ?>"<?php echo $required; ?>>
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
        $started_at = isset($_POST['started_at']) ? absint($_POST['started_at']) : 0;
        if (!$started_at || time() - $started_at < 2) {
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
        $settings = get_post_meta($form_id, '_webform_settings', true);
        $availability_error = $this->availability_error($form_id, $settings);
        if ($availability_error) {
            wp_send_json_error(array('message' => $availability_error), 403);
        }
        $posted = isset($_POST['fields']) && is_array($_POST['fields']) ? wp_unslash($_POST['fields']) : array();
        $data = array();
        $errors = array();
        $uploaded_urls = array();
        $quiz_score = 0;
        $quiz_total = 0;
        $poll_answers = array();
        foreach ($schema as $stage) {
            foreach ($stage['fields'] as $field) {
                if (!$this->field_type_enabled($field)) continue;
                if (in_array($field['type'], array('heading', 'html'), true) || !apply_filters('webform_process_field', true, $field, $form_id)) continue;
                if (!$this->condition_passes($field['condition'] ?? array(), $posted)) continue;
                $value = $posted[$field['id']] ?? '';
                if ($field['type'] === 'captcha' && $this->google_recaptcha_enabled()) $value = '__recaptcha__';
                if ($field['type'] === 'file') $value = !empty($_FILES['fields']['name'][$field['id']]) ? '__pending_upload__' : '';
                $value = apply_filters('webform_raw_submission_value', $value, $field, $form_id, $posted);
                $name_incomplete = false;
                if ($field['type'] === 'name') {
                    $first_name = is_array($value) ? sanitize_text_field($value['first'] ?? '') : '';
                    $last_name = is_array($value) ? sanitize_text_field($value['last'] ?? '') : '';
                    $name_incomplete = !empty($field['required']) && (!$first_name || !$last_name);
                    $value = trim($first_name . ' ' . $last_name);
                }
                $value = is_array($value) ? array_slice(array_map('sanitize_text_field', $value), 0, 100) : substr(sanitize_textarea_field($value), 0, 10000);
                $date_error = $field['type'] === 'date' ? $this->date_validation_error($field, $value) : '';
                if ($name_incomplete) {
                    $errors[$field['id']] = sprintf(__('Enter a complete %s.', 'webform'), $field['label']);
                } elseif (!empty($field['required']) && (empty($value) && $value !== '0')) {
                    $errors[$field['id']] = sprintf(__('%s is required.', 'webform'), $field['label']);
                } elseif ($field['type'] === 'email' && $value && !is_email($value)) {
                    $errors[$field['id']] = __('Enter a valid email address.', 'webform');
                } elseif ($field['type'] === 'url' && $value && !wp_http_validate_url($value)) {
                    $errors[$field['id']] = __('Enter a valid URL.', 'webform');
                } elseif ($date_error) {
                    $errors[$field['id']] = $date_error;
                } elseif (in_array($field['type'], array('select', 'radio', 'poll', 'quiz'), true) && $value && !in_array($value, $field['options'], true)) {
                    $errors[$field['id']] = __('Select a valid option.', 'webform');
                } elseif ($field['type'] === 'checkbox' && array_diff((array) $value, $field['options'])) {
                    $errors[$field['id']] = __('Select valid options.', 'webform');
                } elseif ($field['type'] === 'captcha' && !$this->valid_captcha($field['id'], $value)) {
                    $errors[$field['id']] = __('The security answer is incorrect.', 'webform');
                } elseif ($field['type'] === 'rating' && $value && (!is_numeric($value) || $value < 1 || $value > 5)) {
                    $errors[$field['id']] = __('Select a valid rating.', 'webform');
                }
                $custom_error = apply_filters('webform_validate_field', '', $value, $field, $form_id, $posted);
                if ($custom_error) $errors[$field['id']] = sanitize_text_field($custom_error);
                $data[$field['id']] = array('label' => $field['label'], 'value' => $value);
                if ($field['type'] === 'quiz') {
                    $points = max(1, absint($field['points'] ?? 1));
                    $quiz_total += $points;
                    if ($value !== '' && hash_equals((string) ($field['correct_answer'] ?? ''), (string) $value)) $quiz_score += $points;
                } elseif ($field['type'] === 'poll' && $value !== '') {
                    $poll_answers[$field['id']] = array('label' => $field['label'], 'value' => $value, 'options' => $field['options']);
                }
            }
        }
        if ($errors) {
            wp_send_json_error(array('message' => __('Please correct the highlighted fields.', 'webform'), 'errors' => $errors), 422);
        }
        // Upload only after every non-file field passes, preventing orphaned
        // files when another field causes validation to fail.
        foreach ($schema as $stage) {
            foreach ($stage['fields'] as $field) {
                if ($field['type'] !== 'file' || !$this->condition_passes($field['condition'] ?? array(), $posted)) continue;
                $upload = $this->handle_upload($field);
                if (is_wp_error($upload)) {
                    $errors[$field['id']] = $upload->get_error_message();
                    continue;
                }
                if ($upload) $uploaded_urls[] = $upload;
                $data[$field['id']] = array('label' => $field['label'], 'value' => $upload);
            }
        }
        if ($errors) {
            foreach ($uploaded_urls as $uploaded_url) {
                $uploads = wp_get_upload_dir();
                if (strpos($uploaded_url, $uploads['baseurl']) === 0) {
                    wp_delete_file($uploads['basedir'] . substr($uploaded_url, strlen($uploads['baseurl'])));
                }
            }
            wp_send_json_error(array('message' => __('Please correct the highlighted fields.', 'webform'), 'errors' => $errors), 422);
        }
        $entry_id = absint(apply_filters('webform_existing_entry_id', 0, $form_id, $posted));
        if (!$entry_id || get_post_type($entry_id) !== 'webform_entry') {
            $entry_id = wp_insert_post(array('post_type' => 'webform_entry', 'post_status' => 'private', 'post_title' => sprintf(__('Submission for %s', 'webform'), get_the_title($form_id))));
        } else {
            wp_update_post(array('ID' => $entry_id, 'post_title' => sprintf(__('Submission for %s', 'webform'), get_the_title($form_id))));
        }
        if (!$entry_id || is_wp_error($entry_id)) {
            wp_send_json_error(array('message' => __('We could not save your submission. Please try again.', 'webform')), 500);
        }
        update_post_meta($entry_id, '_webform_form_id', $form_id);
        update_post_meta($entry_id, '_webform_user_id', get_current_user_id());
        update_post_meta($entry_id, '_webform_data', $data);
        update_post_meta($entry_id, '_webform_entry_status', 'submitted');
        delete_post_meta($entry_id, '_webform_draft_token_hash');
        delete_post_meta($entry_id, '_webform_draft_expires');
        delete_post_meta($entry_id, '_webform_draft_values');
        delete_post_meta($entry_id, '_webform_draft_stage');
        if ($quiz_total) update_post_meta($entry_id, '_webform_quiz_score', array('score' => $quiz_score, 'total' => $quiz_total));
        $poll_results = array();
        foreach ($poll_answers as $field_id => $answer) {
            $meta_key = '_webform_poll_' . sanitize_key($field_id);
            $counts = (array) get_post_meta($form_id, $meta_key, true);
            foreach ($answer['options'] as $option) {
                if (!isset($counts[$option])) $counts[$option] = 0;
            }
            $counts[$answer['value']] = isset($counts[$answer['value']]) ? absint($counts[$answer['value']]) + 1 : 1;
            update_post_meta($form_id, $meta_key, $counts);
            $poll_results[] = array('label' => $answer['label'], 'counts' => $counts);
        }
        if (!empty($settings['notification_email']) && is_email($settings['notification_email'])) {
            $lines = array();
            foreach ($data as $item) $lines[] = $item['label'] . ': ' . (is_array($item['value']) ? implode(', ', $item['value']) : $item['value']);
            wp_mail($settings['notification_email'], sprintf(__('New submission: %s', 'webform'), get_the_title($form_id)), implode("\n", $lines));
        }
        if (($settings['confirmation_type'] ?? 'message') === 'webhook' && apply_filters('webform_allow_webhook_confirmation', false) && !empty($settings['webhook_url']) && wp_http_validate_url($settings['webhook_url'])) {
            wp_safe_remote_post($settings['webhook_url'], array('timeout' => 5, 'blocking' => false, 'headers' => array('Content-Type' => 'application/json'), 'body' => wp_json_encode(array('form_id' => $form_id, 'entry_id' => $entry_id, 'form_title' => get_the_title($form_id), 'fields' => $data))));
        }
        /**
         * Fires after an entry is stored and core notifications are dispatched.
         *
         * Premium and third-party add-ons can use this hook for email marketing,
         * payment fulfillment, automation, and CRM integrations.
         */
        do_action('webform_after_submission', $entry_id, $form_id, $data, $settings);
        $response = array(
            'message' => $settings['success_message'] ?? __('Thanks! Your response has been submitted.', 'webform'),
            'message_html' => wp_kses_post($settings['success_message'] ?? ''),
            'redirect_url' => ($settings['confirmation_type'] ?? 'message') === 'redirect' && !empty($settings['redirect_url']) ? $settings['redirect_url'] : '',
            'quiz' => $quiz_total ? array('score' => $quiz_score, 'total' => $quiz_total) : null,
            'polls' => $poll_results,
        );
        $response = apply_filters('webform_submission_response', $response, $entry_id, $form_id, $data, $settings);
        wp_send_json_success($response);
    }

    private function field_type_enabled($field) {
        $pro_types = array('calculation', 'field_group', 'signature', 'rich_text', 'address', 'repeater', 'appointment', 'nps', 'currency', 'product', 'price');
        return !in_array($field['type'] ?? '', $pro_types, true) || (bool) apply_filters('webform_pro_field_enabled', false, $field);
    }

    private function date_bounds($field) {
        $rule = in_array($field['date_rule'] ?? 'any', array('any', 'future', 'past', 'custom'), true) ? $field['date_rule'] : 'any';
        $today = current_time('Y-m-d');
        if ($rule === 'future') return array($today, '');
        if ($rule === 'past') return array('', $today);
        if ($rule !== 'custom') return array('', '');
        $minimum = preg_match('/^\d{4}-\d{2}-\d{2}$/', $field['date_min'] ?? '') ? $field['date_min'] : '';
        $maximum = preg_match('/^\d{4}-\d{2}-\d{2}$/', $field['date_max'] ?? '') ? $field['date_max'] : '';
        return array($minimum, $maximum);
    }

    private function date_validation_error($field, $value) {
        if ($value === '') return '';
        $date = DateTime::createFromFormat('!Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value) return __('Enter a valid date.', 'webform');
        list($minimum, $maximum) = $this->date_bounds($field);
        if ($minimum && $value < $minimum) return sprintf(__('Choose a date on or after %s.', 'webform'), $minimum);
        if ($maximum && $value > $maximum) return sprintf(__('Choose a date on or before %s.', 'webform'), $maximum);
        return '';
    }

    private function condition_passes($condition, $posted) {
        if (empty($condition['enabled']) || empty($condition['field_id'])) return true;
        $actual = $posted[$condition['field_id']] ?? '';
        $actual = is_array($actual) ? implode(', ', array_map('sanitize_text_field', $actual)) : sanitize_text_field($actual);
        $expected = (string) ($condition['value'] ?? '');
        switch ($condition['operator'] ?? 'equals') {
            case 'not_equals': return $actual !== $expected;
            case 'contains': return stripos($actual, $expected) !== false;
            case 'starts_with': return strpos(strtolower($actual), strtolower($expected)) === 0;
            case 'ends_with':
                $actual_text = strtolower($actual);
                $expected_text = strtolower($expected);
                return $expected_text === '' || substr($actual_text, -strlen($expected_text)) === $expected_text;
            case 'greater_than': return is_numeric($actual) && is_numeric($expected) && floatval($actual) > floatval($expected);
            case 'less_than': return is_numeric($actual) && is_numeric($expected) && floatval($actual) < floatval($expected);
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
        if ($file['error'] !== UPLOAD_ERR_OK) return new WP_Error('upload_error', __('The file could not be uploaded.', 'webform'));
        if ($file['size'] > absint($field['max_size']) * MB_IN_BYTES) return new WP_Error('file_size', __('The uploaded file is too large.', 'webform'));
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = array_filter(array_map('trim', explode(',', $field['allowed_extensions'])));
        if (!$extension || !in_array($extension, $allowed, true)) return new WP_Error('file_type', __('This file type is not allowed.', 'webform'));
        require_once ABSPATH . 'wp-admin/includes/file.php';
        $uploaded = wp_handle_upload($file, array('test_form' => false));
        return !empty($uploaded['error']) ? new WP_Error('upload_error', $uploaded['error']) : esc_url_raw($uploaded['url']);
    }

    private function availability_error($form_id, $settings) {
        if (!empty($settings['require_login']) && !is_user_logged_in()) {
            return __('You must be logged in to submit this form.', 'webform');
        }
        $limit = absint($settings['submission_limit'] ?? 0);
        if ($limit) {
            $query = new WP_Query(array(
                'post_type' => 'webform_entry',
                'post_status' => 'private',
                'posts_per_page' => 1,
                'fields' => 'ids',
                'no_found_rows' => false,
                'meta_query' => array('relation' => 'AND', array('key' => '_webform_form_id', 'value' => $form_id), array('relation' => 'OR', array('key' => '_webform_entry_status', 'compare' => 'NOT EXISTS'), array('key' => '_webform_entry_status', 'value' => 'submitted'))),
            ));
            if ($query->found_posts >= $limit) {
                return !empty($settings['closed_message']) ? $settings['closed_message'] : __('This form is currently unavailable.', 'webform');
            }
        }
        return (string) apply_filters('webform_availability_error', '', $form_id, $settings);
    }

    private function valid_captcha($field_id, $value) {
        $settings = $this->recaptcha_settings();
        if ($this->google_recaptcha_enabled()) {
            $response_token = sanitize_text_field(wp_unslash($_POST['g-recaptcha-response'] ?? ''));
            if (!$response_token) return false;
            if ($settings['recaptcha_mode'] === 'enterprise') {
                $endpoint = add_query_arg('key', $settings['recaptcha_api_key'], 'https://recaptchaenterprise.googleapis.com/v1/projects/' . rawurlencode($settings['recaptcha_project_id']) . '/assessments');
                $response = wp_safe_remote_post($endpoint, array('timeout' => 10, 'headers' => array('Content-Type' => 'application/json; charset=utf-8'), 'body' => wp_json_encode(array('event' => array('token' => $response_token, 'siteKey' => $settings['recaptcha_site_key'], 'userAgent' => sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'] ?? '')), 'userIpAddress' => $this->client_ip(), 'expectedAction' => $settings['recaptcha_action'])))));
            } else {
                $response = wp_safe_remote_post('https://www.google.com/recaptcha/api/siteverify', array('timeout' => 10, 'body' => array('secret' => $settings['recaptcha_secret_key'], 'response' => $response_token, 'remoteip' => $this->client_ip())));
            }
            if (is_wp_error($response)) return false;
            $body = json_decode(wp_remote_retrieve_body($response), true);
            if ($settings['recaptcha_mode'] === 'enterprise') {
                $properties = (array) ($body['tokenProperties'] ?? array());
                $valid_action = !empty($properties['action']) && hash_equals($settings['recaptcha_action'], (string) $properties['action']);
                return !empty($properties['valid']) && $valid_action && $this->valid_recaptcha_hostname($properties['hostname'] ?? '');
            }
            return !empty($body['success']) && $this->valid_recaptcha_hostname($body['hostname'] ?? '');
        }
        $tokens = isset($_POST['captcha_tokens']) && is_array($_POST['captcha_tokens']) ? wp_unslash($_POST['captcha_tokens']) : array();
        $token = isset($tokens[$field_id]) ? sanitize_text_field($tokens[$field_id]) : '';
        $decoded = base64_decode($token, true);
        if (!$decoded) return false;
        $parts = explode(':', $decoded);
        if (count($parts) !== 3) return false;
        $answer = absint($parts[0]) + absint($parts[1]);
        return absint($value) === $answer && wp_verify_nonce($parts[2], 'webform_captcha_' . $field_id . '_' . $answer);
    }

    private function client_ip() {
        // REMOTE_ADDR is deliberately hashed before transient storage.
        return isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'unknown';
    }

    private function schema_has_type($schema, $type) {
        foreach ((array) $schema as $stage) foreach ((array) ($stage['fields'] ?? array()) as $field) if (($field['type'] ?? '') === $type) return true;
        return false;
    }

    private function google_recaptcha_enabled() {
        $settings = $this->recaptcha_settings();
        if (empty($settings['recaptcha_enabled']) || empty($settings['recaptcha_site_key'])) return false;
        if ($settings['recaptcha_mode'] === 'enterprise') return !empty($settings['recaptcha_project_id']) && !empty($settings['recaptcha_api_key']) && !empty($settings['recaptcha_action']);
        return !empty($settings['recaptcha_secret_key']);
    }

    private function recaptcha_settings() {
        return wp_parse_args((array) get_option('webform_global_settings', array()), array('recaptcha_enabled' => false, 'recaptcha_mode' => 'classic', 'recaptcha_site_key' => '', 'recaptcha_secret_key' => '', 'recaptcha_project_id' => '', 'recaptcha_api_key' => '', 'recaptcha_action' => 'WEBFORM_SUBMIT'));
    }

    private function valid_recaptcha_hostname($hostname) {
        $expected = strtolower((string) wp_parse_url(home_url(), PHP_URL_HOST));
        $actual = strtolower((string) $hostname);
        return $actual !== '' && ($actual === $expected || preg_replace('/^www\\./', '', $actual) === preg_replace('/^www\\./', '', $expected));
    }
}
