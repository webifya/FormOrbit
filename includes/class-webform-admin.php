<?php

defined('ABSPATH') || exit;

class Webform_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'menu'));
        add_action('admin_enqueue_scripts', array($this, 'assets'));
        add_action('wp_ajax_webform_save_form', array($this, 'save_form'));
        add_action('wp_ajax_webform_delete_form', array($this, 'delete_form'));
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
        add_submenu_page('webform', __('Entries', 'webform'), __('Entries', 'webform'), 'manage_options', 'webform-entries', array($this, 'entries_page'));
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
                            <td class="webform-row-actions"><a href="<?php echo esc_url(admin_url('admin.php?page=webform-builder&form_id=' . $form->ID)); ?>"><?php esc_html_e('Edit', 'webform'); ?></a> <button class="button-link-delete webform-delete" data-id="<?php echo esc_attr($form->ID); ?>"><?php esc_html_e('Delete', 'webform'); ?></button></td>
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
                        $fields = array('text' => 'Text', 'email' => 'Email', 'textarea' => 'Long text', 'select' => 'Dropdown', 'radio' => 'Radio', 'checkbox' => 'Checkbox', 'number' => 'Number', 'date' => 'Date', 'phone' => 'Phone', 'heading' => 'Heading');
                        foreach ($fields as $type => $label) {
                            printf('<div class="webform-palette-item" data-type="%s"><span class="dashicons dashicons-plus-alt2"></span>%s</div>', esc_attr($type), esc_html__($label, 'webform'));
                        }
                        ?>
                    </div>
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
                </aside>
            </div>
        </div>
        <?php
    }

    public function entries_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $entries = get_posts(array('post_type' => 'webform_entry', 'post_status' => 'private', 'posts_per_page' => 100, 'orderby' => 'date', 'order' => 'DESC'));
        ?>
        <div class="wrap webform-wrap"><div class="webform-page-head"><div><h1><?php esc_html_e('Entries', 'webform'); ?></h1><p><?php esc_html_e('The 100 most recent form submissions.', 'webform'); ?></p></div></div>
        <div class="webform-card"><table class="widefat striped"><thead><tr><th><?php esc_html_e('Form', 'webform'); ?></th><th><?php esc_html_e('Submitted data', 'webform'); ?></th><th><?php esc_html_e('Date', 'webform'); ?></th></tr></thead><tbody>
        <?php if (!$entries) : ?><tr><td colspan="3"><?php esc_html_e('No entries yet.', 'webform'); ?></td></tr><?php endif; ?>
        <?php foreach ($entries as $entry) : $data = get_post_meta($entry->ID, '_webform_data', true); $form_id = get_post_meta($entry->ID, '_webform_form_id', true); ?>
            <tr><td><?php echo esc_html(get_the_title($form_id)); ?></td><td><?php foreach ((array) $data as $key => $value) : ?><div><strong><?php echo esc_html($key); ?>:</strong> <?php echo esc_html(is_array($value) ? implode(', ', $value) : $value); ?></div><?php endforeach; ?></td><td><?php echo esc_html(get_the_date('', $entry)); ?></td></tr>
        <?php endforeach; ?>
        </tbody></table></div></div>
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
        ));
        wp_send_json_success(array('id' => $form_id, 'message' => __('Saved', 'webform'), 'shortcode' => '[webform id="' . $form_id . '"]'));
    }

    private function sanitize_schema($schema) {
        $clean = array();
        $allowed_types = array('text', 'email', 'textarea', 'select', 'radio', 'checkbox', 'number', 'date', 'phone', 'heading');
        foreach ($schema as $stage) {
            $clean_stage = array('id' => sanitize_key($stage['id'] ?? ''), 'title' => sanitize_text_field($stage['title'] ?? ''), 'fields' => array());
            foreach ((array) ($stage['fields'] ?? array()) as $field) {
                $type = in_array($field['type'] ?? '', $allowed_types, true) ? $field['type'] : 'text';
                $clean_stage['fields'][] = array(
                    'id' => sanitize_key($field['id'] ?? ''),
                    'type' => $type,
                    'label' => sanitize_text_field($field['label'] ?? ''),
                    'placeholder' => sanitize_text_field($field['placeholder'] ?? ''),
                    'required' => !empty($field['required']),
                    'options' => array_values(array_filter(array_map('sanitize_text_field', (array) ($field['options'] ?? array())))),
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
}
