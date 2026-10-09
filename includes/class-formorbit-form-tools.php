<?php
defined('ABSPATH') || exit;

class FormOrbit_Form_Tools {
    public function __construct() {
        add_action('admin_menu', array($this, 'menu'), 20);
        add_action('admin_post_formorbit_export_form', array($this, 'export_form'), 10, 1);
        add_action('admin_post_formorbit_pro_export_form', array($this, 'export_form'), 10, 1);
        add_action('admin_post_webform_pro_export_form', array($this, 'export_form'), 10, 1);
        add_action('admin_post_formorbit_pro_import_url', array($this, 'import_public_url'), 10, 1);
        add_action('webform_import_export_tools', array($this, 'export_tools'), 10, 1);
        add_action('webform_builder_property_tabs', array($this, 'builder_settings_tab'), 10, 1);
        add_action('webform_builder_property_panels', array($this, 'builder_settings_panel'), 10, 1);
        add_action('wp_ajax_formorbit_pro_save_theme', array($this, 'save_theme'), 10, 1);
        add_action('wp_ajax_formorbit_pro_delete_theme', array($this, 'delete_theme'), 10, 1);
        add_action('wp_ajax_webform_pro_save_theme', array($this, 'save_theme'), 10, 1);
        add_action('wp_ajax_webform_pro_delete_theme', array($this, 'delete_theme'), 10, 1);
        add_action('webform_confirmation_message_editor', array($this, 'rich_confirmation_editor'), 10, 1);
        add_action('webform_builder_confirmation_controls', array($this, 'confirmation_controls'), 10, 2);
        add_filter('webform_sanitize_form_settings', array($this, 'sanitize_form_settings'), 15, 2);
        add_action('webform_after_submission', array($this, 'user_notification'), 15, 4);
        add_action('webform_after_submission', array($this, 'submission_messages'), 20, 4);
        add_action('formorbit_pro_followup', array($this, 'send_followup'), 10, 2);
        add_action('webform_builder_access_controls', array($this, 'access_controls'), 10, 2);
        add_filter('webform_availability_error', array($this, 'availability_error'), 10, 3);
        add_filter('webform_can_export_forms', '__return_true');
        add_action('admin_enqueue_scripts', array($this, 'assets'), 15);
        add_filter('webform_admin_script_data', array($this, 'script_data'));
    }
    public function menu() {
        add_submenu_page('formorbit', __('Import Public Form', 'formorbit'), __('Import Public Form', 'formorbit'), 'manage_options', 'formorbit-import-url', array($this, 'public_import_page'), 2);
    }
    public function assets() {
        if (!wp_script_is('webform-admin', 'enqueued')) return;
        wp_enqueue_style('formorbit-advanced-admin', WEBFORM_URL . 'assets/advanced/admin.css', array('webform-builder-layout'), WEBFORM_VERSION);
        wp_enqueue_style('formorbit-advanced-preview', WEBFORM_URL . 'assets/advanced/public.css', array('webform-public-preview'), WEBFORM_VERSION);
        wp_enqueue_script('webform-pro-admin', WEBFORM_URL . 'assets/advanced/admin.js', array('webform-admin'), WEBFORM_VERSION, true);
        wp_enqueue_editor();
    }
    public function script_data($data) {
        $data['proStyling'] = true;
        $data['savedThemes'] = (array) get_option('webform_pro_saved_themes', array());
        return $data;
    }
    public function public_import_page() {
        if (!current_user_can('manage_options')) return;
        ?>
        <div class="wrap webform-wrap webform-public-import-page"><div class="webform-page-head"><div><h1><?php esc_html_e('Import Public Form', 'formorbit'); ?></h1><p><?php esc_html_e('Convert a public website form into an editable FormOrbit form.', 'formorbit'); ?></p></div><a class="button" href="<?php echo esc_url(admin_url('admin.php?page=formorbit-builder')); ?>"><?php esc_html_e('Create blank form', 'formorbit'); ?></a></div>
            <form class="webform-settings-card webform-pro-export-card" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="formorbit_pro_import_url"><?php wp_nonce_field('formorbit_pro_import_url'); ?>
                <span class="webform-pro-badge"><?php esc_html_e('FORMORBIT', 'formorbit'); ?></span>
                <h2><?php esc_html_e('Import from a public form URL', 'formorbit'); ?></h2>
                <p><?php esc_html_e('Enter a publicly accessible page URL. FormOrbit reads the first HTML form and converts supported labels, inputs, choices, and required rules.', 'formorbit'); ?></p>
                <label><?php esc_html_e('Public form page URL', 'formorbit'); ?><input type="url" name="source_url" required placeholder="https://example.com/contact/"></label>
                <button class="button button-primary"><?php esc_html_e('Import and edit form', 'formorbit'); ?></button>
                <p class="description"><?php esc_html_e('Passwords, payment-card controls, scripts, hidden security tokens, and private pages are never imported.', 'formorbit'); ?></p>
            </form>
        </div>
        <?php
    }

    public function builder_settings_tab() {
        echo '<button type="button" data-panel="integrations">' . esc_html__('Integrations', 'formorbit') . '</button>';
        echo '<button type="button" data-panel="settings">' . esc_html__('Settings', 'formorbit') . '</button>';
    }

    public function builder_settings_panel($form_id) {
        echo '<div class="webform-property-panel" data-panel="integrations"><h2>' . esc_html__('Integrations', 'formorbit') . '</h2>';
        if (has_action('webform_builder_integrations_panel')) {
            do_action('webform_builder_integrations_panel', $form_id);
        } else {
            echo '<p>' . esc_html__('All field, design, export, and form controls are included. Connect your forms to external services with FormOrbit Pro.', 'formorbit') . '</p>';
            foreach (array(__('Email & CRM', 'formorbit') => 'Mailchimp, AWeber, Brevo, ActiveCampaign, Kit, GetResponse, LeadConnector / GoHighLevel', __('Payments', 'formorbit') => 'Stripe, PayPal, Square, Bank transfer', __('Calendars', 'formorbit') => 'Google Calendar, Outlook, Calendly, Cal.com, Acuity, Apple', __('Messaging & automation', 'formorbit') => 'Twilio, Vonage, MessageBird, Telnyx, Zapier, Webhooks') as $category => $providers) echo '<details class="formorbit-integration-category"><summary>' . esc_html($category) . ' <span>Pro</span></summary><p>' . esc_html($providers) . '</p></details>';
            echo '<p><a class="button button-primary" href="https://www.webninjallc.com/plugins/formorbit/" target="_blank" rel="noopener noreferrer">' . esc_html__('Explore Pro integrations', 'formorbit') . '</a></p>';
        }
        echo '</div>';
        echo '<div class="webform-property-panel webform-pro-settings-panel" data-panel="settings"><h2>' . esc_html__('Form settings', 'formorbit') . '</h2><p class="description">' . esc_html__('Manage appearance, saved progress, and document delivery in one place.', 'formorbit') . '</p><div class="webform-pro-settings-sections"></div></div>';
    }

    public function export_tools($forms) {
        ?>
        <form class="webform-settings-card webform-pro-export-card" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
            <input type="hidden" name="action" value="formorbit_pro_export_form">
            <?php wp_nonce_field('webform_pro_export_form'); ?>
            
            <h2><?php esc_html_e('Export a form', 'formorbit'); ?></h2>
            <p><?php esc_html_e('Download a portable copy containing stages, fields, conditional rules, styling, confirmations, and form settings.', 'formorbit'); ?></p>
            <?php if ($forms) : ?>
                <label><?php esc_html_e('Form', 'formorbit'); ?><select name="form_id" required><?php foreach ($forms as $form) : ?><option value="<?php echo esc_attr($form->ID); ?>"><?php echo esc_html($form->post_title); ?></option><?php endforeach; ?></select></label>
                <label><?php esc_html_e('File format', 'formorbit'); ?><select name="format"><option value="json">JSON — <?php esc_html_e('recommended', 'formorbit'); ?></option><option value="csv">CSV</option><option value="xml">XML</option></select></label>
                <button class="button button-primary"><?php esc_html_e('Download export', 'formorbit'); ?></button>
            <?php else : ?>
                <p class="description"><?php esc_html_e('Create a form before exporting.', 'formorbit'); ?></p>
            <?php endif; ?>
        </form>
        <form class="webform-settings-card webform-pro-export-card" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
            <input type="hidden" name="action" value="formorbit_pro_import_url"><?php wp_nonce_field('formorbit_pro_import_url'); ?>
            
            <h2><?php esc_html_e('Import from a public form URL', 'formorbit'); ?></h2>
            <p><?php esc_html_e('Enter a publicly accessible page URL. FormOrbit reads the first HTML form and converts supported labels, inputs, choices, and required rules into an editable form.', 'formorbit'); ?></p>
            <label><?php esc_html_e('Public form page URL', 'formorbit'); ?><input type="url" name="source_url" required placeholder="https://example.com/contact/"></label>
            <button class="button button-primary"><?php esc_html_e('Import public form', 'formorbit'); ?></button>
            <p class="description"><?php esc_html_e('Passwords, payment-card controls, scripts, hidden security tokens, and private pages are never imported.', 'formorbit'); ?></p>
        </form>
        <?php
    }

    public function import_public_url() {
        if (!current_user_can('manage_options')) wp_die(esc_html__('Permission denied.', 'formorbit'));
        check_admin_referer('formorbit_pro_import_url');
        $url = esc_url_raw(wp_unslash($_POST['source_url'] ?? ''), array('http', 'https'));
        if (!$url || !wp_http_validate_url($url)) wp_die(esc_html__('Enter a valid public HTTP or HTTPS URL.', 'formorbit'));
        $response = wp_safe_remote_get($url, array('timeout' => 15, 'redirection' => 3, 'limit_response_size' => 2 * MB_IN_BYTES, 'user-agent' => 'FormOrbit-Pro/' . WEBFORM_VERSION));
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) wp_die(esc_html__('The public page could not be downloaded.', 'formorbit'));
        $html = wp_remote_retrieve_body($response);
        if (!$html || !class_exists('DOMDocument')) wp_die(esc_html__('No readable HTML form was found.', 'formorbit'));
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        $form = $loaded ? $dom->getElementsByTagName('form')->item(0) : null;
        if (!$form) wp_die(esc_html__('No HTML form was found on that page.', 'formorbit'));
        $labels = array();
        foreach ($form->getElementsByTagName('label') as $label) if ($label->getAttribute('for')) $labels[$label->getAttribute('for')] = trim($label->textContent);
        $fields = array();
        foreach (array('input', 'textarea', 'select') as $tag) foreach ($form->getElementsByTagName($tag) as $node) {
            $input_type = strtolower($node->getAttribute('type') ?: $tag);
            if (in_array($input_type, array('submit', 'button', 'reset', 'image', 'hidden', 'password', 'file'), true)) continue;
            $name = $node->getAttribute('name'); $id = $node->getAttribute('id');
            if (!$name && !$id) continue;
            $type = $tag === 'textarea' ? 'textarea' : ($tag === 'select' ? 'select' : (in_array($input_type, array('email', 'number', 'date', 'time', 'url', 'tel', 'radio', 'checkbox'), true) ? ($input_type === 'tel' ? 'phone' : $input_type) : 'text'));
            $key = sanitize_key($name ?: $id); $label = sanitize_text_field($labels[$id] ?? $node->getAttribute('aria-label') ?: $node->getAttribute('placeholder') ?: ucwords(str_replace(array('_', '-'), ' ', $key)));
            $existing = null; foreach ($fields as $index => $candidate) if ($candidate['id'] === $key) { $existing = $index; break; }
            if ($existing !== null && in_array($type, array('radio', 'checkbox'), true)) { $value = sanitize_text_field($node->getAttribute('value')); if ($value !== '') $fields[$existing]['options'][] = $value; continue; }
            $options = array(); if ($tag === 'select') foreach ($node->getElementsByTagName('option') as $option) { $text = sanitize_text_field(trim($option->textContent)); if ($text !== '') $options[] = $text; }
            if (in_array($type, array('radio', 'checkbox'), true) && $node->getAttribute('value')) $options[] = sanitize_text_field($node->getAttribute('value'));
            $fields[] = array('id' => $key ?: 'field_' . wp_generate_password(6, false, false), 'type' => $type, 'label' => $label ?: __('Imported field', 'formorbit'), 'placeholder' => sanitize_text_field($node->getAttribute('placeholder')), 'required' => $node->hasAttribute('required'), 'options' => $options, 'style' => array('width' => '100'));
        }
        if (!$fields) wp_die(esc_html__('The page did not contain any supported public fields.', 'formorbit'));
        $title_node = $dom->getElementsByTagName('title')->item(0);
        $title = sanitize_text_field($title_node ? $title_node->textContent : __('Imported website form', 'formorbit'));
        $form_id = wp_insert_post(array('post_type' => 'webform_form', 'post_status' => 'publish', 'post_title' => $title ?: __('Imported website form', 'formorbit')));
        if (!$form_id || is_wp_error($form_id)) wp_die(esc_html__('The imported form could not be created.', 'formorbit'));
        update_post_meta($form_id, '_webform_schema', array(array('id' => 'stage_imported', 'title' => __('Imported form', 'formorbit'), 'fields' => $fields)));
        update_post_meta($form_id, '_webform_settings', array('success_message' => __('Thanks! Your response has been submitted.', 'formorbit'), 'notification_email' => get_option('admin_email'), 'confirmation_type' => 'message', 'style_preset' => 'modern'));
        wp_safe_redirect(admin_url('admin.php?page=formorbit-builder&form_id=' . absint($form_id) . '&imported=public-url'));
        exit;
    }

    public function export_form() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Permission denied.', 'formorbit'), '', array('response' => 403));
        }
        check_admin_referer('webform_pro_export_form');
        $form_id = absint($_POST['form_id'] ?? 0);
        $format = sanitize_key(wp_unslash($_POST['format'] ?? 'json'));
        if (!in_array($format, array('json', 'csv', 'xml'), true)) $format = 'json';
        $form = get_post($form_id);
        if (!$form || $form->post_type !== 'webform_form') wp_die(esc_html__('Form not found.', 'formorbit'), '', array('response' => 404));
        $schema = (array) get_post_meta($form_id, '_webform_schema', true);
        $settings = (array) get_post_meta($form_id, '_webform_settings', true);
        $slug = sanitize_title($form->post_title) ?: 'webform-' . $form_id;
        nocache_headers();
        header('X-Content-Type-Options: nosniff');
        header('Content-Disposition: attachment; filename="' . $slug . '-webform.' . $format . '"');
        if ($format === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            $output = fopen('php://output', 'w');
            fputcsv($output, array('form_name', 'stage_id', 'stage_title', 'field_id', 'field_type', 'label', 'placeholder', 'required', 'options', 'field_json', 'settings_json'));
            $first = true;
            foreach ($schema as $stage) {
                foreach ((array) ($stage['fields'] ?? array()) as $field) {
                    fputcsv($output, array($this->safe_csv($form->post_title), $stage['id'] ?? '', $this->safe_csv($stage['title'] ?? ''), $field['id'] ?? '', $field['type'] ?? 'text', $this->safe_csv($field['label'] ?? ''), $this->safe_csv($field['placeholder'] ?? ''), !empty($field['required']) ? '1' : '0', $this->safe_csv(implode('|', (array) ($field['options'] ?? array()))), wp_json_encode($field), $first ? wp_json_encode($settings) : ''));
                    $first = false;
                }
            }
            fclose($output);
            exit;
        }
        if ($format === 'xml') {
            header('Content-Type: application/xml; charset=utf-8');
            echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            echo '<webform-export version="1"><form><name>' . esc_xml($form->post_title) . '</name><settings>' . esc_xml(wp_json_encode($settings)) . '</settings><stages>';
            foreach ($schema as $stage) {
                echo '<stage id="' . esc_attr($stage['id'] ?? '') . '" title="' . esc_attr($stage['title'] ?? '') . '">';
                foreach ((array) ($stage['fields'] ?? array()) as $field) echo '<field>' . esc_xml(wp_json_encode($field)) . '</field>';
                echo '</stage>';
            }
            echo '</stages></form></webform-export>';
            exit;
        }
        header('Content-Type: application/json; charset=utf-8');
        echo wp_json_encode(array('webform_export_version' => 1, 'exported_at' => gmdate('c'), 'form' => array('name' => $form->post_title, 'status' => $form->post_status, 'schema' => $schema, 'settings' => $settings)), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function safe_csv($value) {
        $value = (string) $value;
        return preg_match('/^[=+\-@]/', $value) ? "'" . $value : $value;
    }

    public function rich_confirmation_editor($settings) {
        wp_editor(
            $settings['success_message'] ?? __('Thanks! Your response has been submitted.', 'formorbit'),
            'webform-success-message',
            array('textarea_rows' => 8, 'media_buttons' => true, 'teeny' => false, 'quicktags' => true)
        );
    }

    public function confirmation_controls($form_id, $settings) {
        $schema = (array) get_post_meta($form_id, '_webform_schema', true);
        $email_fields = array();
        $phone_fields = array();
        foreach ($schema as $stage) {
            foreach ((array) ($stage['fields'] ?? array()) as $field) {
                if (($field['type'] ?? '') === 'email' && !empty($field['id'])) $email_fields[sanitize_key($field['id'])] = sanitize_text_field($field['label'] ?? $field['id']);
                if (($field['type'] ?? '') === 'phone' && !empty($field['id'])) $phone_fields[sanitize_key($field['id'])] = sanitize_text_field($field['label'] ?? $field['id']);
            }
        }
        $email_enabled = !empty($settings['user_notification_enabled']);
        $progress_enabled = !empty($settings['save_progress_enabled']);
        ?>
        <div class="webform-confirmation-section webform-notification-workflows">
            <hr><h3><?php esc_html_e('Follow-up messages', 'formorbit'); ?></h3>
            <?php if (apply_filters('formorbit_integrations_enabled', false)) : ?>
            <label class="webform-check"><input type="checkbox" id="webform-admin-sms-enabled" <?php checked(!empty($settings['admin_sms_enabled'])); ?>> <?php esc_html_e('Send an immediate SMS notification to the configured admin number', 'formorbit'); ?></label>
            <label class="webform-check"><input type="checkbox" id="webform-user-sms-enabled" <?php checked(!empty($settings['user_sms_enabled'])); ?>> <?php esc_html_e('Send an immediate SMS confirmation to the visitor', 'formorbit'); ?></label>
            <label id="webform-user-sms-options"><?php esc_html_e('Visitor phone field', 'formorbit'); ?><select id="webform-user-sms-phone-field"><option value=""><?php esc_html_e('First phone field automatically', 'formorbit'); ?></option><?php foreach ($phone_fields as $field_id => $label) : ?><option value="<?php echo esc_attr($field_id); ?>" <?php selected($settings['user_sms_phone_field'] ?? '', $field_id); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></label>
            <label id="webform-immediate-sms-options"><?php esc_html_e('Immediate SMS message', 'formorbit'); ?><textarea id="webform-sms-message" rows="3"><?php echo esc_textarea($settings['sms_message'] ?? __('New {form_name} submission #{entry_id}.', 'formorbit')); ?></textarea></label>
            <?php endif; ?>
            <label class="webform-check"><input type="checkbox" id="webform-followup-enabled" <?php checked(!empty($settings['followup_enabled'])); ?>> <?php esc_html_e('Schedule a follow-up after submission', 'formorbit'); ?></label>
            <div id="webform-followup-options" class="webform-dependent-options <?php echo empty($settings['followup_enabled']) ? 'is-disabled' : ''; ?>"><div class="webform-followup-grid"><label><?php esc_html_e('Send after', 'formorbit'); ?><span><input type="number" id="webform-followup-delay" min="1" max="720" value="<?php echo esc_attr(absint($settings['followup_delay'] ?? 24)); ?>"> <?php esc_html_e('hours', 'formorbit'); ?></span></label><label><?php esc_html_e('Channel', 'formorbit'); ?><select id="webform-followup-channel"><option value="email" <?php selected($settings['followup_channel'] ?? '', 'email'); ?>><?php esc_html_e('Email', 'formorbit'); ?></option><?php if (apply_filters('formorbit_integrations_enabled', false)) : ?><option value="sms" <?php selected($settings['followup_channel'] ?? '', 'sms'); ?>>SMS</option><option value="both" <?php selected($settings['followup_channel'] ?? '', 'both'); ?>><?php esc_html_e('Email and SMS', 'formorbit'); ?></option><?php endif; ?></select></label></div>
            <label><?php esc_html_e('Follow-up subject', 'formorbit'); ?><input type="text" id="webform-followup-subject" value="<?php echo esc_attr($settings['followup_subject'] ?? __('Following up on your {form_name} submission', 'formorbit')); ?>"></label>
            <label><?php esc_html_e('Follow-up message', 'formorbit'); ?><textarea id="webform-followup-message" rows="5"><?php echo esc_textarea($settings['followup_message'] ?? __('Thank you for contacting us. We are following up regarding submission #{entry_id}.', 'formorbit')); ?></textarea></label>
            </div>

        </div>
        <div class="webform-confirmation-section webform-visitor-confirmation-section">
            <hr><h3><?php esc_html_e('Visitor confirmation email', 'formorbit'); ?> </h3>
            <label class="webform-check"><input type="checkbox" id="webform-user-notification-enabled" <?php checked($email_enabled); ?>> <?php esc_html_e('Email a personalized confirmation to the visitor', 'formorbit'); ?></label>
            <div id="webform-user-notification-options" class="webform-confirmation-options <?php echo $email_enabled ? '' : 'is-disabled'; ?>">
                <label><?php esc_html_e('Send to email field', 'formorbit'); ?><select id="webform-user-notification-email-field" data-saved="<?php echo esc_attr($settings['user_notification_email_field'] ?? ''); ?>"><option value=""><?php esc_html_e('First email field automatically', 'formorbit'); ?></option><?php foreach ($email_fields as $field_id => $label) : ?><option value="<?php echo esc_attr($field_id); ?>" <?php selected($settings['user_notification_email_field'] ?? '', $field_id); ?>><?php echo esc_html($label . ' (' . $field_id . ')'); ?></option><?php endforeach; ?></select><small><?php esc_html_e('Email fields added in the builder appear here automatically.', 'formorbit'); ?></small></label>
                <label><?php esc_html_e('Email subject', 'formorbit'); ?><input type="text" id="webform-user-notification-subject" value="<?php echo esc_attr($settings['user_notification_subject'] ?? __('We received your {form_name} submission', 'formorbit')); ?>"></label>
                <label><?php esc_html_e('Email template', 'formorbit'); ?></label>
                <?php wp_editor($settings['user_notification_body'] ?? __('<p>Thank you. We received your submission for <strong>{form_name}</strong>.</p>{all_fields}', 'formorbit'), 'webform-user-notification-body', array('textarea_rows' => 10, 'media_buttons' => true, 'teeny' => false, 'quicktags' => true)); ?>
                <p class="description"><?php esc_html_e('Available smart tags: {form_name}, {entry_id}, {all_fields}, or any field ID such as {email}.', 'formorbit'); ?></p>
                <label class="webform-check"><input type="checkbox" id="webform-user-notification-pdf" <?php checked(!empty($settings['user_notification_pdf'])); ?>> <?php esc_html_e('Attach a PDF copy of the submission', 'formorbit'); ?></label>
            </div>
        </div>
        <div class="webform-confirmation-section webform-save-continue-section">
            <hr><h3><?php esc_html_e('Save & Continue', 'formorbit'); ?> </h3>
            <label class="webform-check"><input type="checkbox" id="webform-save-progress-enabled" <?php checked($progress_enabled); ?>> <?php esc_html_e('Allow visitors to save an unfinished form and resume later', 'formorbit'); ?></label>
            <div id="webform-save-progress-options" class="webform-pro-access-grid <?php echo $progress_enabled ? '' : 'is-disabled'; ?>">
                <label><?php esc_html_e('Resume link expires after', 'formorbit'); ?><input type="number" min="1" max="90" id="webform-save-progress-days" value="<?php echo esc_attr(absint($settings['save_progress_days'] ?? 30)); ?>"><small><?php esc_html_e('Days', 'formorbit'); ?></small></label>
                <label><?php esc_html_e('Save button label', 'formorbit'); ?><input type="text" id="webform-save-progress-label" value="<?php echo esc_attr($settings['save_progress_label'] ?? __('Save and continue later', 'formorbit')); ?>"></label>
            </div>
            <p class="description"><?php esc_html_e('The button appears beside the form navigation after this form is saved. Visitors with an email field receive a secure resume link.', 'formorbit'); ?></p>
        </div>
        <div class="webform-confirmation-section webform-admin-pdf-section">
            <hr><h3><?php esc_html_e('Admin PDF notification', 'formorbit'); ?> </h3>
            <label class="webform-check"><input type="checkbox" id="webform-pdf-notifications" <?php checked(!empty($settings['pdf_notifications'])); ?>> <?php esc_html_e('Email a PDF submission copy to the admin notification address', 'formorbit'); ?></label>
            <p class="description"><?php esc_html_e('The PDF is generated privately after submission and is never exposed through a public URL.', 'formorbit'); ?></p>
        </div>
        <?php
    }

    public function sanitize_form_settings($clean, $raw) {
        $clean['pdf_notifications'] = !empty($raw['pdf_notifications']);
        $clean['open_at'] = sanitize_text_field($raw['open_at'] ?? '');
        $clean['close_at'] = sanitize_text_field($raw['close_at'] ?? '');
        $clean['per_user_limit'] = min(1000, absint($raw['per_user_limit'] ?? 0));
        $roles = array_keys(wp_roles()->roles);
        $clean['allowed_roles'] = array_values(array_intersect((array) ($raw['allowed_roles'] ?? array()), $roles));
        $clean['hide_after_submit'] = !empty($raw['hide_after_submit']);
        $clean['save_progress_enabled'] = !empty($raw['save_progress_enabled']);
        $clean['save_progress_days'] = min(90, max(1, absint($raw['save_progress_days'] ?? 30)));
        $clean['save_progress_label'] = substr(sanitize_text_field($raw['save_progress_label'] ?? __('Save and continue later', 'formorbit')), 0, 80);
        $clean['user_notification_enabled'] = !empty($raw['user_notification_enabled']);
        $clean['user_notification_email_field'] = sanitize_key($raw['user_notification_email_field'] ?? '');
        $clean['user_notification_subject'] = substr(sanitize_text_field($raw['user_notification_subject'] ?? __('We received your {form_name} submission', 'formorbit')), 0, 200);
        $clean['user_notification_body'] = substr(wp_kses_post($raw['user_notification_body'] ?? ''), 0, 50000);
        $clean['user_notification_pdf'] = !empty($raw['user_notification_pdf']);
        $clean['admin_sms_enabled'] = !empty($raw['admin_sms_enabled']);
        $clean['user_sms_enabled'] = !empty($raw['user_sms_enabled']);
        $clean['user_sms_phone_field'] = sanitize_key($raw['user_sms_phone_field'] ?? '');
        $clean['sms_message'] = substr(sanitize_textarea_field($raw['sms_message'] ?? ''), 0, 1000);
        $clean['followup_enabled'] = !empty($raw['followup_enabled']);
        $clean['followup_delay'] = min(720, max(1, absint($raw['followup_delay'] ?? 24)));
        $clean['followup_channel'] = in_array($raw['followup_channel'] ?? '', array('email', 'sms', 'both'), true) ? $raw['followup_channel'] : 'email';
        $clean['followup_subject'] = substr(sanitize_text_field($raw['followup_subject'] ?? ''), 0, 200);
        $clean['followup_message'] = substr(sanitize_textarea_field($raw['followup_message'] ?? ''), 0, 5000);
        return $clean;
    }

    public function user_notification($entry_id, $form_id, $data, $settings) {
        if (empty($settings['user_notification_enabled'])) return;
        $recipient = '';
        $selected_field = sanitize_key($settings['user_notification_email_field'] ?? '');
        if ($selected_field && isset($data[$selected_field]['value']) && is_email($data[$selected_field]['value'])) {
            $recipient = sanitize_email($data[$selected_field]['value']);
        }
        if (!$recipient) {
            $schema = (array) get_post_meta($form_id, '_webform_schema', true);
            foreach ($schema as $stage) {
                foreach ((array) ($stage['fields'] ?? array()) as $field) {
                    $field_id = sanitize_key($field['id'] ?? '');
                    $value = $data[$field_id]['value'] ?? '';
                    if (($field['type'] ?? '') === 'email' && is_string($value) && is_email($value)) {
                        $recipient = sanitize_email($value);
                        break 2;
                    }
                }
            }
        }
        if (!$recipient) return;
        $form_name = get_the_title($form_id);
        $subject_tokens = array('{form_name}' => $form_name, '{entry_id}' => (string) absint($entry_id));
        $body_tokens = array('{form_name}' => esc_html($form_name), '{entry_id}' => (string) absint($entry_id));
        $rows = '';
        foreach ((array) $data as $field_id => $item) {
            $value = is_array($item['value'] ?? '') ? implode(', ', $item['value']) : (string) ($item['value'] ?? '');
            $label = sanitize_text_field($item['label'] ?? $field_id);
            $subject_tokens['{' . sanitize_key($field_id) . '}'] = wp_strip_all_tags($value);
            $body_tokens['{' . sanitize_key($field_id) . '}'] = esc_html($value);
            $rows .= '<tr><th scope="row" style="padding:8px;text-align:left;vertical-align:top">' . esc_html($label) . '</th><td style="padding:8px">' . nl2br(esc_html($value)) . '</td></tr>';
        }
        $body_tokens['{all_fields}'] = '<table role="presentation" style="border-collapse:collapse;width:100%">' . $rows . '</table>';
        $subject = strtr($settings['user_notification_subject'] ?? __('We received your {form_name} submission', 'formorbit'), $subject_tokens);
        $body = strtr($settings['user_notification_body'] ?? __('<p>Thank you. We received your submission for <strong>{form_name}</strong>.</p>{all_fields}', 'formorbit'), $body_tokens);
        $attachments = array();
        if (!empty($settings['user_notification_pdf'])) {
            $pdf_file = apply_filters('webform_pro_pdf_attachment', '', $form_id, $entry_id, $data);
            if (is_string($pdf_file) && $pdf_file && file_exists($pdf_file)) $attachments[] = $pdf_file;
        }
        wp_mail($recipient, wp_strip_all_tags($subject), wp_kses_post($body), array('Content-Type: text/html; charset=' . get_bloginfo('charset')), $attachments);
        foreach ($attachments as $attachment) wp_delete_file($attachment);
    }

    public function submission_messages($entry_id, $form_id, $data, $settings) {
        $tokens = array('{form_name}' => get_the_title($form_id), '{entry_id}' => (string) absint($entry_id));
        foreach ((array) $data as $field_id => $item) $tokens['{' . sanitize_key($field_id) . '}'] = is_array($item['value'] ?? '') ? implode(', ', $item['value']) : (string) ($item['value'] ?? '');
        $message = strtr($settings['sms_message'] ?? __('New {form_name} submission #{entry_id}.', 'formorbit'), $tokens);
        $integration = (array) get_option('webform_pro_integrations', array());
        if (!empty($settings['admin_sms_enabled']) && !empty($integration['sms_admin_number'])) do_action('formorbit_send_sms', $integration['sms_admin_number'], $message, $integration);
        if (!empty($settings['user_sms_enabled'])) {
            $phone = $this->submission_recipient($data, $settings['user_sms_phone_field'] ?? '', 'phone');
            if ($phone) do_action('formorbit_send_sms', $phone, $message, $integration);
        }
        if (!empty($settings['followup_enabled']) && !wp_next_scheduled('formorbit_pro_followup', array(absint($entry_id), absint($form_id)))) {
            wp_schedule_single_event(time() + HOUR_IN_SECONDS * min(720, max(1, absint($settings['followup_delay'] ?? 24))), 'formorbit_pro_followup', array(absint($entry_id), absint($form_id)));
        }
    }

    public function send_followup($entry_id, $form_id) {
        if (get_post_status($entry_id) !== 'private') return;
        $settings = (array) get_post_meta($form_id, '_webform_settings', true);
        if (empty($settings['followup_enabled'])) return;
        $data = (array) get_post_meta($entry_id, '_webform_data', true);
        $tokens = array('{form_name}' => get_the_title($form_id), '{entry_id}' => (string) absint($entry_id));
        foreach ($data as $field_id => $item) $tokens['{' . sanitize_key($field_id) . '}'] = is_array($item['value'] ?? '') ? implode(', ', $item['value']) : (string) ($item['value'] ?? '');
        $channel = $settings['followup_channel'] ?? 'email';
        $message = strtr($settings['followup_message'] ?? '', $tokens);
        if (in_array($channel, array('email', 'both'), true)) {
            $email = $this->submission_recipient($data, $settings['user_notification_email_field'] ?? '', 'email');
            if ($email) wp_mail($email, strtr($settings['followup_subject'] ?? __('Following up on your submission', 'formorbit'), $tokens), $message);
        }
        if (in_array($channel, array('sms', 'both'), true)) {
            $phone = $this->submission_recipient($data, $settings['user_sms_phone_field'] ?? '', 'phone');
            if ($phone) do_action('formorbit_send_sms', $phone, $message, (array) get_option('webform_pro_integrations', array()));
        }
    }

    private function submission_recipient($data, $selected, $kind) {
        if ($selected && !empty($data[$selected]['value'])) return sanitize_text_field($data[$selected]['value']);
        foreach ((array) $data as $item) {
            $value = is_array($item['value'] ?? '') ? '' : trim((string) ($item['value'] ?? ''));
            if ($kind === 'email' && is_email($value)) return sanitize_email($value);
            if ($kind === 'phone' && preg_match('/^\+?[0-9][0-9\s().-]{6,24}$/', $value)) return preg_replace('/[^0-9+]/', '', $value);
        }
        return '';
    }

    public function save_theme() {
        $this->authorize_ajax();
        $name = substr(sanitize_text_field(wp_unslash($_POST['name'] ?? '')), 0, 60);
        $raw = json_decode(wp_unslash($_POST['settings'] ?? '{}'), true);
        if (!$name || !is_array($raw)) wp_send_json_error(array('message' => __('Enter a valid theme name.', 'formorbit')), 400);
        $themes = (array) get_option('webform_pro_saved_themes', array());
        if (count($themes) >= 20) wp_send_json_error(array('message' => __('You can save up to 20 themes.', 'formorbit')), 400);
        $id = sanitize_key($name) . '-' . substr(wp_generate_password(6, false, false), 0, 6);
        $themes[$id] = array('name' => $name, 'settings' => $this->sanitize_theme($raw), 'created_at' => current_time('mysql', true));
        update_option('webform_pro_saved_themes', $themes, false);
        wp_send_json_success(array('themes' => $themes, 'id' => $id));
    }

    public function delete_theme() {
        $this->authorize_ajax();
        $id = sanitize_key(wp_unslash($_POST['theme_id'] ?? ''));
        $themes = (array) get_option('webform_pro_saved_themes', array());
        if (isset($themes[$id])) unset($themes[$id]);
        update_option('webform_pro_saved_themes', $themes, false);
        wp_send_json_success(array('themes' => $themes));
    }

    private function authorize_ajax() {
        check_ajax_referer('webform_admin', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error(array('message' => __('Permission denied.', 'formorbit')), 403);
    }

    private function sanitize_theme($raw) {
        $keys = array('style_preset', 'font_family', 'base_font_size', 'label_font_size', 'text_color', 'form_background', 'field_background', 'border_color', 'form_max_width', 'field_spacing', 'field_radius', 'button_radius', 'button_padding', 'accent_color', 'button_text_color', 'custom_css');
        $clean = array();
        foreach ($keys as $key) $clean[$key] = sanitize_textarea_field($raw[$key] ?? '');
        return $clean;
    }

    public function access_controls($settings) {
        $roles = wp_roles()->roles;
        ?>
        <hr><h3><?php esc_html_e('Access controls', 'formorbit'); ?></h3>
        <div class="webform-pro-access-grid">
            <label><?php esc_html_e('Open form at', 'formorbit'); ?><input type="datetime-local" id="webform-open-at" value="<?php echo esc_attr($settings['open_at'] ?? ''); ?>"></label>
            <label><?php esc_html_e('Close form at', 'formorbit'); ?><input type="datetime-local" id="webform-close-at" value="<?php echo esc_attr($settings['close_at'] ?? ''); ?>"></label>
        </div>
        <label><?php esc_html_e('Entries per logged-in user', 'formorbit'); ?><input type="number" min="0" max="1000" id="webform-per-user-limit" value="<?php echo esc_attr(absint($settings['per_user_limit'] ?? 0)); ?>"><small><?php esc_html_e('Use 0 for unlimited. Guest limits continue to use rate protection.', 'formorbit'); ?></small></label>
        <div id="webform-role-controls" class="webform-role-controls <?php echo empty($settings['require_login']) ? 'is-disabled' : ''; ?>" aria-disabled="<?php echo empty($settings['require_login']) ? 'true' : 'false'; ?>"><strong><?php esc_html_e('Allowed user roles', 'formorbit'); ?></strong><div class="webform-role-list"><?php foreach ($roles as $role => $details) : ?><label><input type="checkbox" name="webform_allowed_roles" value="<?php echo esc_attr($role); ?>" <?php checked(in_array($role, (array) ($settings['allowed_roles'] ?? array()), true)); ?>> <?php echo esc_html(translate_user_role($details['name'])); ?></label><?php endforeach; ?></div><small><?php esc_html_e('Enable “Require visitors to log in” to restrict access by role. Leave every role unchecked to allow all logged-in users.', 'formorbit'); ?></small></div>
        <label class="webform-check"><input type="checkbox" id="webform-hide-after-submit" <?php checked(!empty($settings['hide_after_submit'])); ?>> <?php esc_html_e('Prevent another submission after a logged-in user submits', 'formorbit'); ?></label>
        <?php
    }

    public function availability_error($error, $form_id, $settings) {
        if ($error) return $error;
        $now = current_time('timestamp', true);
        $open = !empty($settings['open_at']) ? date_create_immutable($settings['open_at'], wp_timezone()) : false;
        $close = !empty($settings['close_at']) ? date_create_immutable($settings['close_at'], wp_timezone()) : false;
        if ($open && $open->getTimestamp() > $now) return __('This form is not open yet.', 'formorbit');
        if ($close && $close->getTimestamp() < $now) return $settings['closed_message'] ?? __('This form is closed.', 'formorbit');
        if (!empty($settings['require_login']) && !empty($settings['allowed_roles']) && is_user_logged_in()) {
            $user = wp_get_current_user();
            if (!array_intersect((array) $settings['allowed_roles'], (array) $user->roles)) return __('Your account role cannot access this form.', 'formorbit');
        }
        $limit = !empty($settings['hide_after_submit']) ? 1 : absint($settings['per_user_limit'] ?? 0);
        if ($limit && is_user_logged_in()) {
            $query = new WP_Query(array('post_type' => 'webform_entry', 'post_status' => 'private', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_query' => array('relation' => 'AND', array('key' => '_webform_form_id', 'value' => $form_id), array('key' => '_webform_user_id', 'value' => get_current_user_id()), array('relation' => 'OR', array('key' => '_webform_entry_status', 'compare' => 'NOT EXISTS'), array('key' => '_webform_entry_status', 'value' => 'submitted')))));
            if ($query->found_posts >= $limit) return __('You have reached the submission limit for this form.', 'formorbit');
        }
        return '';
    }
}
