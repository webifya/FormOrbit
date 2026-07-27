<?php

defined('ABSPATH') || exit;

class Webform_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'menu'));
        add_action('admin_enqueue_scripts', array($this, 'assets'));
        add_action('wp_ajax_webform_save_form', array($this, 'save_form'));
        add_action('wp_ajax_webform_delete_form', array($this, 'delete_form'));
        add_action('admin_post_webform_duplicate_form', array($this, 'duplicate_form'));
        add_action('admin_post_webform_export_entries', array($this, 'export_entries'));
        add_action('admin_post_webform_delete_entry', array($this, 'delete_entry'));
    }

    public function menu() {
        add_menu_page(
            __('Webform', 'webform'),
            __('Webform', 'webform'),
            'manage_options',
            'webform',
            array($this, 'forms_page'),
            'dashicons-feedback',
            26
        );
        add_submenu_page('webform', __('All Forms', 'webform'), __('All Forms', 'webform'), 'manage_options', 'webform', array($this, 'forms_page'));
        add_submenu_page('webform', __('Add New', 'webform'), __('Add New', 'webform'), 'manage_options', 'webform-builder', array($this, 'builder_page'));
        add_submenu_page('webform', __('Form Templates', 'webform'), __('Templates', 'webform'), 'manage_options', 'webform-templates', array($this, 'templates_page'));
        add_submenu_page('webform', __('Entries', 'webform'), __('Entries', 'webform'), 'manage_options', 'webform-entries', array($this, 'entries_page'));
        if (!$this->is_pro_active()) {
            add_submenu_page('webform', __('Webform Pro', 'webform'), __('Upgrade to Pro', 'webform'), 'manage_options', 'webform-pro', array($this, 'pro_page'));
        }
    }

    public function assets($hook) {
        if (strpos($hook, 'webform') === false) {
            return;
        }
        wp_enqueue_style('webform-admin', WEBFORM_URL . 'assets/css/admin.css', array(), WEBFORM_VERSION);
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('webform-admin', WEBFORM_URL . 'assets/js/admin.js', array('jquery', 'jquery-ui-sortable'), WEBFORM_VERSION, true);
        wp_localize_script('webform-admin', 'WebformAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('webform_admin'),
            'formsUrl' => admin_url('admin.php?page=webform'),
        ));
    }

    public function forms_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $forms = get_posts(array('post_type' => 'webform_form', 'posts_per_page' => -1, 'post_status' => array('publish', 'draft'), 'orderby' => 'modified', 'order' => 'DESC'));
        ?>
        <div class="wrap webform-wrap">
            <div class="webform-page-head">
                <div><h1><?php esc_html_e('Webforms', 'webform'); ?></h1><p><?php esc_html_e('Build and manage forms without code.', 'webform'); ?></p></div>
                <a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=webform-builder')); ?>"><?php esc_html_e('Create form', 'webform'); ?></a>
            </div>
            <div class="webform-card">
                <?php if (!$forms) : ?>
                    <div class="webform-empty"><span class="dashicons dashicons-feedback"></span><h2><?php esc_html_e('Create your first form', 'webform'); ?></h2><p><?php esc_html_e('Add fields, arrange stages, and publish it with a shortcode.', 'webform'); ?></p></div>
                <?php else : ?>
                    <table class="widefat striped"><thead><tr><th><?php esc_html_e('Name', 'webform'); ?></th><th><?php esc_html_e('Shortcode', 'webform'); ?></th><th><?php esc_html_e('Entries', 'webform'); ?></th><th><?php esc_html_e('Updated', 'webform'); ?></th><th></th></tr></thead><tbody>
                    <?php foreach ($forms as $form) : ?>
                        <tr>
                            <td><strong><a href="<?php echo esc_url(admin_url('admin.php?page=webform-builder&form_id=' . $form->ID)); ?>"><?php echo esc_html($form->post_title); ?></a></strong></td>
                            <td><code>[webform id="<?php echo esc_attr($form->ID); ?>"]</code></td>
                            <td><?php echo esc_html($this->entry_count($form->ID)); ?></td>
                            <td><?php echo esc_html(get_the_modified_date('', $form)); ?></td>
                            <td class="webform-row-actions"><a href="<?php echo esc_url(admin_url('admin.php?page=webform-builder&form_id=' . $form->ID)); ?>"><?php esc_html_e('Edit', 'webform'); ?></a> <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=webform_duplicate_form&form_id=' . $form->ID), 'webform_duplicate_' . $form->ID)); ?>"><?php esc_html_e('Duplicate', 'webform'); ?></a> <button class="button-link-delete webform-delete" data-id="<?php echo esc_attr($form->ID); ?>"><?php esc_html_e('Delete', 'webform'); ?></button></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody></table>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    private function entry_count($form_id) {
        $query = new WP_Query(array('post_type' => 'webform_entry', 'post_status' => 'private', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_webform_form_id', 'meta_value' => $form_id));
        return $query->found_posts;
    }

    public function builder_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $form_id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;
        $form = $form_id ? get_post($form_id) : null;
        if ($form && $form->post_type !== 'webform_form') {
            $form = null;
            $form_id = 0;
        }
        $schema = $form ? get_post_meta($form_id, '_webform_schema', true) : '';
        $template_key = isset($_GET['template']) ? sanitize_key(wp_unslash($_GET['template'])) : '';
        if (!$form && $template_key) {
            $templates = $this->free_templates();
            $schema = isset($templates[$template_key]) ? $templates[$template_key]['schema'] : '';
        }
        $settings = $form ? get_post_meta($form_id, '_webform_settings', true) : array();
        ?>
        <div class="wrap webform-wrap webform-builder-wrap">
            <div class="webform-page-head">
                <div><h1><?php echo $form ? esc_html__('Edit form', 'webform') : esc_html__('Create form', 'webform'); ?></h1><p><?php esc_html_e('Drag fields into a stage, then select a field to configure it.', 'webform'); ?></p></div>
                <div><span id="webform-save-status"></span> <button class="button button-primary button-hero" id="webform-save"><?php esc_html_e('Save form', 'webform'); ?></button></div>
            </div>
            <input type="hidden" id="webform-id" value="<?php echo esc_attr($form_id); ?>">
            <input type="hidden" id="webform-schema" value="<?php echo esc_attr(wp_json_encode($schema ? $schema : array())); ?>">
            <input type="hidden" id="webform-settings" value="<?php echo esc_attr(wp_json_encode($settings ? $settings : array())); ?>">
            <div class="webform-name-row">
                <label for="webform-name"><?php esc_html_e('Form name', 'webform'); ?></label>
                <input id="webform-name" class="regular-text" value="<?php echo esc_attr($form ? $form->post_title : __('Untitled form', 'webform')); ?>">
            </div>
            <div class="webform-builder">
                <aside class="webform-sidebar">
                    <h2><?php esc_html_e('Fields', 'webform'); ?></h2>
                    <div id="webform-palette" class="webform-palette">
                        <?php
                        $fields = apply_filters('webform_field_palette', array(
                            'text' => __('Text', 'webform'),
                            'email' => __('Email', 'webform'),
                            'textarea' => __('Long text', 'webform'),
                            'select' => __('Dropdown', 'webform'),
                            'radio' => __('Radio', 'webform'),
                            'checkbox' => __('Checkbox', 'webform'),
                            'number' => __('Number', 'webform'),
                            'date' => __('Date', 'webform'),
                            'phone' => __('Phone', 'webform'),
                            'url' => __('Website', 'webform'),
                            'file' => __('File upload', 'webform'),
                            'consent' => __('Consent', 'webform'),
                            'poll' => __('Poll', 'webform'),
                            'quiz' => __('Quiz question', 'webform'),
                            'heading' => __('Heading', 'webform'),
                        ));
                        foreach ($fields as $type => $label) {
                            printf('<div class="webform-palette-item" data-type="%s"><span class="dashicons dashicons-plus-alt2"></span>%s</div>', esc_attr($type), esc_html($label));
                        }
                        ?>
                    </div>
                    <?php if (!$this->is_pro_active()) : ?><div class="webform-pro-mini">
                        <span class="webform-pro-badge"><?php esc_html_e('PRO', 'webform'); ?></span>
                        <h3><?php esc_html_e('Grow with integrations', 'webform'); ?></h3>
                        <p><?php esc_html_e('Email marketing, payments, Zapier, CRM connections, signatures, calculations, and priority support.', 'webform'); ?></p>
                        <a href="<?php echo esc_url($this->upgrade_url('builder')); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('View Pro — $19.99/year', 'webform'); ?></a>
                    </div><?php endif; ?>
                </aside>
                <main class="webform-canvas-panel">
                    <div class="webform-stage-tabs"><div id="webform-stage-tabs"></div><button class="button" id="webform-add-stage">+ <?php esc_html_e('Add stage', 'webform'); ?></button></div>
                    <div id="webform-canvas" class="webform-canvas"></div>
                </main>
                <aside class="webform-properties">
                    <h2><?php esc_html_e('Field settings', 'webform'); ?></h2>
                    <div id="webform-field-settings"><p class="description"><?php esc_html_e('Select a field to edit its options.', 'webform'); ?></p></div>
                    <hr>
                    <h2><?php esc_html_e('Confirmation', 'webform'); ?></h2>
                    <label><?php esc_html_e('Success message', 'webform'); ?><textarea id="webform-success-message" rows="3"><?php echo esc_textarea(isset($settings['success_message']) ? $settings['success_message'] : __('Thanks! Your response has been submitted.', 'webform')); ?></textarea></label>
                    <label><?php esc_html_e('Notification email', 'webform'); ?><input type="email" id="webform-notification-email" value="<?php echo esc_attr(isset($settings['notification_email']) ? $settings['notification_email'] : get_option('admin_email')); ?>"></label>
                    <label><?php esc_html_e('Submit button text', 'webform'); ?><input type="text" id="webform-submit-label" value="<?php echo esc_attr(isset($settings['submit_label']) ? $settings['submit_label'] : __('Submit', 'webform')); ?>"></label>
                    <label><?php esc_html_e('Redirect URL (optional)', 'webform'); ?><input type="url" id="webform-redirect-url" value="<?php echo esc_attr($settings['redirect_url'] ?? ''); ?>"></label>
                    <label><?php esc_html_e('Webhook URL (optional)', 'webform'); ?><input type="url" id="webform-webhook-url" value="<?php echo esc_attr($settings['webhook_url'] ?? ''); ?>"></label>
                    <hr>
                    <h2><?php esc_html_e('Access and limits', 'webform'); ?></h2>
                    <label class="webform-check"><input type="checkbox" id="webform-require-login" <?php checked(!empty($settings['require_login'])); ?>> <?php esc_html_e('Require visitors to log in', 'webform'); ?></label>
                    <label><?php esc_html_e('Maximum total entries', 'webform'); ?><input type="number" min="0" id="webform-submission-limit" value="<?php echo esc_attr(absint($settings['submission_limit'] ?? 0)); ?>"><small><?php esc_html_e('Use 0 for unlimited.', 'webform'); ?></small></label>
                    <label><?php esc_html_e('Closed form message', 'webform'); ?><textarea id="webform-closed-message" rows="3"><?php echo esc_textarea($settings['closed_message'] ?? __('This form is currently unavailable.', 'webform')); ?></textarea></label>
                </aside>
            </div>
        </div>
        <?php
    }

    public function entries_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $form_filter = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;
        $paged = max(1, isset($_GET['paged']) ? absint($_GET['paged']) : 1);
        $args = array('post_type' => 'webform_entry', 'post_status' => 'private', 'posts_per_page' => 25, 'paged' => $paged, 'orderby' => 'date', 'order' => 'DESC');
        if ($form_filter) {
            $args['meta_key'] = '_webform_form_id';
            $args['meta_value'] = $form_filter;
        }
        $query = new WP_Query($args);
        $entries = $query->posts;
        $forms = get_posts(array('post_type' => 'webform_form', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC'));
        ?>
        <div class="wrap webform-wrap"><div class="webform-page-head"><div><h1><?php esc_html_e('Entries', 'webform'); ?></h1><p><?php esc_html_e('Review, filter, export, or remove submissions.', 'webform'); ?></p></div>
        <a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=webform_export_entries&form_id=' . $form_filter), 'webform_export_entries')); ?>"><?php esc_html_e('Export CSV', 'webform'); ?></a></div>
        <form method="get" class="webform-entry-filter"><input type="hidden" name="page" value="webform-entries"><select name="form_id"><option value="0"><?php esc_html_e('All forms', 'webform'); ?></option><?php foreach ($forms as $form) : ?><option value="<?php echo esc_attr($form->ID); ?>" <?php selected($form_filter, $form->ID); ?>><?php echo esc_html($form->post_title); ?></option><?php endforeach; ?></select><button class="button"><?php esc_html_e('Filter', 'webform'); ?></button></form>
        <div class="webform-card"><table class="widefat striped"><thead><tr><th><?php esc_html_e('Form', 'webform'); ?></th><th><?php esc_html_e('Submitted data', 'webform'); ?></th><th><?php esc_html_e('Date', 'webform'); ?></th><th></th></tr></thead><tbody>
        <?php if (!$entries) : ?><tr><td colspan="4"><?php esc_html_e('No entries yet.', 'webform'); ?></td></tr><?php endif; ?>
        <?php foreach ($entries as $entry) : $data = get_post_meta($entry->ID, '_webform_data', true); $form_id = get_post_meta($entry->ID, '_webform_form_id', true); ?>
            <tr><td><?php echo esc_html(get_the_title($form_id)); ?></td><td><?php foreach ((array) $data as $key => $item) : $item = is_array($item) && isset($item['label']) ? $item : array('label' => $key, 'value' => $item); ?><div><strong><?php echo esc_html($item['label']); ?>:</strong> <?php echo esc_html(is_array($item['value']) ? implode(', ', $item['value']) : $item['value']); ?></div><?php endforeach; ?></td><td><?php echo esc_html(get_the_date('', $entry)); ?></td><td><a class="button-link-delete" onclick="return confirm('<?php echo esc_js(__('Permanently delete this entry?', 'webform')); ?>')" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=webform_delete_entry&entry_id=' . $entry->ID), 'webform_delete_entry_' . $entry->ID)); ?>"><?php esc_html_e('Delete', 'webform'); ?></a></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
        <?php echo wp_kses_post((string) paginate_links(array('base' => add_query_arg('paged', '%#%'), 'format' => '', 'current' => $paged, 'total' => $query->max_num_pages, 'type' => 'list'))); ?></div>
        <?php
    }

    public function save_form() {
        check_ajax_referer('webform_admin', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'webform')), 403);
        }
        $form_id = isset($_POST['form_id']) ? absint($_POST['form_id']) : 0;
        if ($form_id && (get_post_type($form_id) !== 'webform_form' || !current_user_can('edit_post', $form_id))) {
            wp_send_json_error(array('message' => __('Form not found or permission denied.', 'webform')), 403);
        }
        $schema_json = isset($_POST['schema']) ? wp_unslash($_POST['schema']) : '[]';
        $settings_json = isset($_POST['settings']) ? wp_unslash($_POST['settings']) : '{}';
        $schema = json_decode($schema_json, true);
        $settings = json_decode($settings_json, true);
        if (!is_array($schema) || !is_array($settings)) {
            wp_send_json_error(array('message' => __('Invalid form data.', 'webform')), 400);
        }
        if (count($schema) > 20) {
            wp_send_json_error(array('message' => __('A form may contain up to 20 stages.', 'webform')), 400);
        }
        $postarr = array(
            'post_title' => sanitize_text_field(isset($_POST['name']) ? wp_unslash($_POST['name']) : ''),
            'post_type' => 'webform_form',
            'post_status' => 'publish',
        );
        if ($form_id) {
            $postarr['ID'] = $form_id;
        }
        $form_id = wp_insert_post($postarr, true);
        if (is_wp_error($form_id)) {
            wp_send_json_error(array('message' => $form_id->get_error_message()), 500);
        }
        update_post_meta($form_id, '_webform_schema', $this->sanitize_schema($schema));
        update_post_meta($form_id, '_webform_settings', array(
            'success_message' => sanitize_textarea_field($settings['success_message'] ?? ''),
            'notification_email' => sanitize_email($settings['notification_email'] ?? ''),
            'submit_label' => sanitize_text_field($settings['submit_label'] ?? __('Submit', 'webform')),
            'redirect_url' => esc_url_raw($settings['redirect_url'] ?? ''),
            'webhook_url' => esc_url_raw($settings['webhook_url'] ?? ''),
            'require_login' => !empty($settings['require_login']),
            'submission_limit' => absint($settings['submission_limit'] ?? 0),
            'closed_message' => sanitize_textarea_field($settings['closed_message'] ?? __('This form is currently unavailable.', 'webform')),
        ));
        wp_send_json_success(array('id' => $form_id, 'message' => __('Saved', 'webform'), 'shortcode' => '[webform id="' . $form_id . '"]'));
    }

    private function sanitize_schema($schema) {
        $clean = array();
        $allowed_types = apply_filters('webform_allowed_field_types', array('text', 'email', 'textarea', 'select', 'radio', 'checkbox', 'number', 'date', 'phone', 'url', 'file', 'consent', 'poll', 'quiz', 'heading'));
        $allowed_operators = array('equals', 'not_equals', 'contains', 'not_empty', 'empty');
        $seen_ids = array();
        foreach ($schema as $stage_index => $stage) {
            $stage_id = sanitize_key($stage['id'] ?? '');
            if (!$stage_id || isset($seen_ids[$stage_id])) {
                $stage_id = 'stage_' . ($stage_index + 1) . '_' . wp_generate_password(6, false, false);
            }
            $seen_ids[$stage_id] = true;
            $clean_stage = array('id' => $stage_id, 'title' => sanitize_text_field($stage['title'] ?? ''), 'fields' => array());
            foreach (array_slice((array) ($stage['fields'] ?? array()), 0, 100) as $field_index => $field) {
                $type = in_array($field['type'] ?? '', $allowed_types, true) ? $field['type'] : 'text';
                $field_id = sanitize_key($field['id'] ?? '');
                if (!$field_id || isset($seen_ids[$field_id])) {
                    $field_id = 'field_' . ($stage_index + 1) . '_' . ($field_index + 1) . '_' . wp_generate_password(6, false, false);
                }
                $seen_ids[$field_id] = true;
                $clean_stage['fields'][] = array(
                    'id' => $field_id,
                    'type' => $type,
                    'label' => substr(sanitize_text_field($field['label'] ?? ''), 0, 200),
                    'placeholder' => substr(sanitize_text_field($field['placeholder'] ?? ''), 0, 300),
                    'required' => !empty($field['required']),
                    'options' => array_slice(array_values(array_filter(array_map('sanitize_text_field', (array) ($field['options'] ?? array())))), 0, 100),
                    'allowed_extensions' => preg_replace('/[^a-z0-9,\s]/', '', strtolower($field['allowed_extensions'] ?? 'jpg,jpeg,png,pdf,doc,docx')),
                    'max_size' => min(20, max(1, absint($field['max_size'] ?? 5))),
                    'correct_answer' => sanitize_text_field($field['correct_answer'] ?? ''),
                    'points' => min(100, max(1, absint($field['points'] ?? 1))),
                    'condition' => array(
                        'enabled' => !empty($field['condition']['enabled']),
                        'field_id' => sanitize_key($field['condition']['field_id'] ?? ''),
                        'operator' => in_array($field['condition']['operator'] ?? '', $allowed_operators, true) ? $field['condition']['operator'] : 'equals',
                        'value' => sanitize_text_field($field['condition']['value'] ?? ''),
                    ),
                );
            }
            $clean[] = $clean_stage;
        }
        return $clean;
    }

    public function delete_form() {
        check_ajax_referer('webform_admin', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'webform')), 403);
        }
        $form_id = isset($_POST['form_id']) ? absint($_POST['form_id']) : 0;
        if (!$form_id || get_post_type($form_id) !== 'webform_form') {
            wp_send_json_error(array('message' => __('Form not found.', 'webform')), 404);
        }
        wp_trash_post($form_id);
        wp_send_json_success();
    }

    public function duplicate_form() {
        if (!current_user_can('manage_options')) wp_die(esc_html__('Permission denied.', 'webform'));
        $form_id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;
        check_admin_referer('webform_duplicate_' . $form_id);
        if (get_post_type($form_id) !== 'webform_form') wp_die(esc_html__('Form not found.', 'webform'));
        $copy_id = wp_insert_post(array('post_type' => 'webform_form', 'post_status' => 'publish', 'post_title' => sprintf(__('%s (Copy)', 'webform'), get_the_title($form_id))));
        update_post_meta($copy_id, '_webform_schema', get_post_meta($form_id, '_webform_schema', true));
        update_post_meta($copy_id, '_webform_settings', get_post_meta($form_id, '_webform_settings', true));
        wp_safe_redirect(admin_url('admin.php?page=webform-builder&form_id=' . $copy_id));
        exit;
    }

    public function delete_entry() {
        if (!current_user_can('manage_options')) wp_die(esc_html__('Permission denied.', 'webform'));
        $entry_id = isset($_GET['entry_id']) ? absint($_GET['entry_id']) : 0;
        check_admin_referer('webform_delete_entry_' . $entry_id);
        if (get_post_type($entry_id) === 'webform_entry') wp_delete_post($entry_id, true);
        wp_safe_redirect(wp_get_referer() ?: admin_url('admin.php?page=webform-entries'));
        exit;
    }

    public function export_entries() {
        if (!current_user_can('manage_options')) wp_die(esc_html__('Permission denied.', 'webform'));
        check_admin_referer('webform_export_entries');
        $form_id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;
        $args = array('post_type' => 'webform_entry', 'post_status' => 'private', 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC');
        if ($form_id) {
            $args['meta_key'] = '_webform_form_id';
            $args['meta_value'] = $form_id;
        }
        $entries = get_posts($args);
        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=webform-entries-' . gmdate('Y-m-d') . '.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, array('Entry ID', 'Form', 'Date', 'Field', 'Value'));
        foreach ($entries as $entry) {
            $entry_form_id = get_post_meta($entry->ID, '_webform_form_id', true);
            foreach ((array) get_post_meta($entry->ID, '_webform_data', true) as $key => $item) {
                $item = is_array($item) && isset($item['label']) ? $item : array('label' => $key, 'value' => $item);
                fputcsv($output, array(
                    $entry->ID,
                    $this->csv_cell(get_the_title($entry_form_id)),
                    $entry->post_date,
                    $this->csv_cell($item['label']),
                    $this->csv_cell(is_array($item['value']) ? implode(', ', $item['value']) : $item['value']),
                ));
            }
        }
        fclose($output);
        exit;
    }

    private function csv_cell($value) {
        $value = (string) $value;
        return preg_match('/^[=+\-@]/', $value) ? "'" . $value : $value;
    }

    public function templates_page() {
        if (!current_user_can('manage_options')) return;
        ?>
        <div class="wrap webform-wrap">
            <div class="webform-page-head"><div><h1><?php esc_html_e('Free Form Templates', 'webform'); ?></h1><p><?php esc_html_e('Start with a complete form, then customize every field and stage.', 'webform'); ?></p></div></div>
            <div class="webform-template-grid">
                <div class="webform-template-card"><span class="dashicons dashicons-plus-alt2"></span><h2><?php esc_html_e('Blank Form', 'webform'); ?></h2><p><?php esc_html_e('Start with an empty stage.', 'webform'); ?></p><a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=webform-builder')); ?>"><?php esc_html_e('Create', 'webform'); ?></a></div>
                <?php foreach ($this->free_templates() as $key => $template) : ?>
                    <div class="webform-template-card"><span class="dashicons <?php echo esc_attr($template['icon']); ?>"></span><h2><?php echo esc_html($template['name']); ?></h2><p><?php echo esc_html($template['description']); ?></p><a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=webform-builder&template=' . $key)); ?>"><?php esc_html_e('Use template', 'webform'); ?></a></div>
                <?php endforeach; ?>
                <?php if (!$this->is_pro_active()) : ?><div class="webform-template-card webform-template-pro"><span class="webform-pro-badge"><?php esc_html_e('PRO', 'webform'); ?></span><h2><?php esc_html_e('20 Premium Templates', 'webform'); ?></h2><p><?php esc_html_e('Payments, lead generation, bookings, applications, orders, onboarding, and advanced business workflows.', 'webform'); ?></p><a class="button" href="<?php echo esc_url($this->upgrade_url('templates')); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Explore Pro', 'webform'); ?></a></div><?php endif; ?>
            </div>
        </div>
        <?php
    }

    private function free_templates() {
        return array(
            'contact' => array('name' => __('Contact Form', 'webform'), 'description' => __('Name, email, phone, and message.', 'webform'), 'icon' => 'dashicons-email-alt', 'schema' => array($this->template_stage(__('Contact Us', 'webform'), array($this->template_field('name', 'text', __('Name', 'webform'), true), $this->template_field('email', 'email', __('Email', 'webform'), true), $this->template_field('phone', 'phone', __('Phone', 'webform')), $this->template_field('message', 'textarea', __('Message', 'webform'), true))))),
            'feedback' => array('name' => __('Customer Feedback', 'webform'), 'description' => __('Satisfaction poll and written feedback.', 'webform'), 'icon' => 'dashicons-format-chat', 'schema' => array($this->template_stage(__('Your Feedback', 'webform'), array($this->template_field('satisfaction', 'poll', __('How satisfied are you?', 'webform'), true, array(__('Very satisfied', 'webform'), __('Satisfied', 'webform'), __('Neutral', 'webform'), __('Dissatisfied', 'webform'))), $this->template_field('feedback', 'textarea', __('What can we improve?', 'webform')))))),
            'job-application' => array('name' => __('Job Application', 'webform'), 'description' => __('Applicant details, role, résumé, and consent.', 'webform'), 'icon' => 'dashicons-businessperson', 'schema' => array($this->template_stage(__('Applicant Details', 'webform'), array($this->template_field('name', 'text', __('Full name', 'webform'), true), $this->template_field('email', 'email', __('Email', 'webform'), true), $this->template_field('role', 'select', __('Position', 'webform'), true, array(__('Developer', 'webform'), __('Designer', 'webform'), __('Marketing', 'webform'))))), $this->template_stage(__('Application', 'webform'), array($this->template_field('resume', 'file', __('Résumé', 'webform'), true), $this->template_field('cover', 'textarea', __('Cover letter', 'webform')), $this->template_field('consent', 'consent', __('I consent to the processing of my application.', 'webform'), true))))),
            'event-registration' => array('name' => __('Event Registration', 'webform'), 'description' => __('Attendee information and session choice.', 'webform'), 'icon' => 'dashicons-calendar-alt', 'schema' => array($this->template_stage(__('Registration', 'webform'), array($this->template_field('name', 'text', __('Attendee name', 'webform'), true), $this->template_field('email', 'email', __('Email', 'webform'), true), $this->template_field('session', 'radio', __('Preferred session', 'webform'), true, array(__('Morning', 'webform'), __('Afternoon', 'webform'))), $this->template_field('notes', 'textarea', __('Accessibility or dietary needs', 'webform')))))),
            'quote-request' => array('name' => __('Request a Quote', 'webform'), 'description' => __('Project type, budget, timing, and requirements.', 'webform'), 'icon' => 'dashicons-money-alt', 'schema' => array($this->template_stage(__('Project', 'webform'), array($this->template_field('service', 'select', __('Service needed', 'webform'), true, array(__('Website', 'webform'), __('Ecommerce', 'webform'), __('Marketing', 'webform'), __('Other', 'webform'))), $this->template_field('budget', 'select', __('Budget range', 'webform'), true, array(__('Under $1,000', 'webform'), __('$1,000–$5,000', 'webform'), __('$5,000+', 'webform'))), $this->template_field('details', 'textarea', __('Project details', 'webform'), true))), $this->template_stage(__('Contact', 'webform'), array($this->template_field('name', 'text', __('Name', 'webform'), true), $this->template_field('email', 'email', __('Email', 'webform'), true))))),
            'newsletter' => array('name' => __('Newsletter Signup', 'webform'), 'description' => __('Simple email subscription with consent.', 'webform'), 'icon' => 'dashicons-megaphone', 'schema' => array($this->template_stage(__('Stay Updated', 'webform'), array($this->template_field('name', 'text', __('Name', 'webform')), $this->template_field('email', 'email', __('Email', 'webform'), true), $this->template_field('consent', 'consent', __('I agree to receive email updates.', 'webform'), true))))),
            'support-request' => array('name' => __('Support Request', 'webform'), 'description' => __('Issue details, priority, and attachment.', 'webform'), 'icon' => 'dashicons-sos', 'schema' => array($this->template_stage(__('Support Ticket', 'webform'), array($this->template_field('email', 'email', __('Email', 'webform'), true), $this->template_field('priority', 'select', __('Priority', 'webform'), true, array(__('Low', 'webform'), __('Normal', 'webform'), __('Urgent', 'webform'))), $this->template_field('issue', 'textarea', __('Describe the issue', 'webform'), true), $this->template_field('attachment', 'file', __('Screenshot or document', 'webform')))))),
            'survey' => array('name' => __('Product Survey', 'webform'), 'description' => __('Three quick polls with an open comment.', 'webform'), 'icon' => 'dashicons-chart-bar', 'schema' => array($this->template_stage(__('Product Survey', 'webform'), array($this->template_field('ease', 'poll', __('How easy is the product to use?', 'webform'), true, array('1', '2', '3', '4', '5')), $this->template_field('recommend', 'poll', __('Would you recommend it?', 'webform'), true, array(__('Yes', 'webform'), __('Maybe', 'webform'), __('No', 'webform'))), $this->template_field('favorite', 'textarea', __('What is your favorite feature?', 'webform')))))),
            'quiz' => array('name' => __('Simple Knowledge Quiz', 'webform'), 'description' => __('A ready-to-edit scored three-question quiz.', 'webform'), 'icon' => 'dashicons-welcome-learn-more', 'schema' => array($this->template_stage(__('Quick Quiz', 'webform'), array($this->template_field('q1', 'quiz', __('What is the capital of France?', 'webform'), true, array(__('Paris', 'webform'), __('Rome', 'webform'), __('Madrid', 'webform')), __('Paris', 'webform')), $this->template_field('q2', 'quiz', __('How many days are in a leap year?', 'webform'), true, array('365', '366', '367'), '366'), $this->template_field('q3', 'quiz', __('Which planet is known as the Red Planet?', 'webform'), true, array(__('Mars', 'webform'), __('Venus', 'webform'), __('Jupiter', 'webform')), __('Mars', 'webform')))))),
            'volunteer' => array('name' => __('Volunteer Registration', 'webform'), 'description' => __('Availability, interests, and contact details.', 'webform'), 'icon' => 'dashicons-groups', 'schema' => array($this->template_stage(__('Volunteer With Us', 'webform'), array($this->template_field('name', 'text', __('Name', 'webform'), true), $this->template_field('email', 'email', __('Email', 'webform'), true), $this->template_field('interests', 'checkbox', __('Areas of interest', 'webform'), true, array(__('Events', 'webform'), __('Fundraising', 'webform'), __('Community outreach', 'webform'))), $this->template_field('availability', 'textarea', __('Availability', 'webform')))))),
        );
    }

    private function template_stage($title, $fields) {
        return array('id' => 'stage_' . wp_generate_password(8, false, false), 'title' => $title, 'fields' => $fields);
    }

    private function template_field($id, $type, $label, $required = false, $options = array(), $correct_answer = '') {
        return array('id' => $id . '_' . wp_generate_password(6, false, false), 'type' => $type, 'label' => $label, 'placeholder' => '', 'required' => $required, 'options' => $options, 'allowed_extensions' => 'jpg,jpeg,png,pdf,doc,docx', 'max_size' => 5, 'correct_answer' => $correct_answer, 'points' => 1, 'condition' => array('enabled' => false, 'field_id' => '', 'operator' => 'equals', 'value' => ''));
    }

    public function pro_page() {
        if (!current_user_can('manage_options')) return;
        $features = array(
            __('Stripe and PayPal payments', 'webform'),
            __('Mailchimp, Brevo, ActiveCampaign, and ConvertKit', 'webform'),
            __('Zapier and advanced webhook automation', 'webform'),
            __('CRM integrations and lead routing', 'webform'),
            __('20 additional premium form templates', 'webform'),
            __('Calculated fields and order forms', 'webform'),
            __('Electronic signatures and PDF documents', 'webform'),
            __('Advanced spam protection and priority support', 'webform'),
            __('Automatic updates with one-site license', 'webform'),
        );
        ?>
        <div class="wrap webform-wrap webform-pro-page">
            <div class="webform-pro-hero">
                <span class="webform-pro-badge"><?php esc_html_e('WEBFORM PRO', 'webform'); ?></span>
                <h1><?php esc_html_e('Turn every form into a connected workflow', 'webform'); ?></h1>
                <p><?php esc_html_e('Keep everything in Webform Free, then add payments, email marketing, automation, and advanced business tools.', 'webform'); ?></p>
                <div class="webform-pro-price"><strong>$19.99</strong> <span><?php esc_html_e('per year / one website', 'webform'); ?></span></div>
                <a class="button button-primary button-hero" href="<?php echo esc_url($this->upgrade_url('upgrade-page')); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Get Webform Pro', 'webform'); ?></a>
            </div>
            <div class="webform-pro-grid">
                <?php foreach ($features as $feature) : ?><div class="webform-pro-feature"><span class="dashicons dashicons-yes-alt"></span><strong><?php echo esc_html($feature); ?></strong></div><?php endforeach; ?>
            </div>
            <p class="description"><?php esc_html_e('Webform Pro will install as a separate licensed add-on. Your forms and entries remain compatible with the free plugin.', 'webform'); ?></p>
        </div>
        <?php
    }

    private function upgrade_url($source) {
        return apply_filters('webform_upgrade_url', add_query_arg(array('utm_source' => 'webform-plugin', 'utm_medium' => 'upgrade', 'utm_campaign' => sanitize_key($source)), 'https://www.webninjallc.com/'));
    }

    private function is_pro_active() {
        return defined('WEBFORM_PRO_VERSION') || (bool) apply_filters('webform_is_pro_active', false);
    }
}
