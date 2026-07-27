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
        add_action('admin_post_webform_save_global_settings', array($this, 'save_global_settings'));
        add_action('admin_post_webform_import', array($this, 'import_form'));
        add_action('admin_head', array($this, 'suppress_editor_notices'), 1);
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
        add_submenu_page('webform', __('Import Forms', 'webform'), __('Import', 'webform'), 'manage_options', 'webform-import', array($this, 'import_page'));
        add_submenu_page('webform', __('Webform Settings', 'webform'), __('Settings', 'webform'), 'manage_options', 'webform-settings', array($this, 'settings_page'));
        if (!$this->is_pro_active()) {
            add_submenu_page('webform', __('Webform Pro', 'webform'), __('Upgrade to Pro', 'webform'), 'manage_options', 'webform-pro', array($this, 'pro_page'));
        }
    }

    public function assets($hook) {
        if (strpos($hook, 'webform') === false) {
            return;
        }
        wp_enqueue_style('webform-admin', WEBFORM_URL . 'assets/css/admin.css', array(), WEBFORM_VERSION);
        wp_enqueue_style('webform-builder-refresh', WEBFORM_URL . 'assets/css/builder-refresh.css', array('webform-admin'), WEBFORM_VERSION);
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
                <aside class="webform-field-picker" id="webform-field-picker" aria-hidden="true">
                    <div class="webform-field-picker-backdrop"></div><div class="webform-field-picker-dialog"><div class="webform-field-picker-head"><div><h2><?php esc_html_e('Add a field', 'webform'); ?></h2><p><?php esc_html_e('Choose a field to add to the current stage.', 'webform'); ?></p></div><button type="button" class="webform-field-picker-close" aria-label="<?php esc_attr_e('Close field picker', 'webform'); ?>">×</button></div>
                    <h3><?php esc_html_e('Standard fields', 'webform'); ?></h3>
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
                            'time' => __('Time', 'webform'),
                            'phone' => __('Phone', 'webform'),
                            'url' => __('Website', 'webform'),
                            'file' => __('File upload', 'webform'),
                            'consent' => __('Consent', 'webform'),
                            'poll' => __('Poll', 'webform'),
                            'quiz' => __('Quiz question', 'webform'),
                            'rating' => __('Rating', 'webform'),
                            'slider' => __('Slider', 'webform'),
                            'hidden' => __('Hidden field', 'webform'),
                            'html' => __('HTML content', 'webform'),
                            'captcha' => __('Google CAPTCHA', 'webform'),
                            'page_break' => __('Page break', 'webform'),
                            'heading' => __('Heading', 'webform'),
                        ));
                        $field_icons = array('text' => 'dashicons-editor-textcolor', 'email' => 'dashicons-email', 'textarea' => 'dashicons-editor-alignleft', 'select' => 'dashicons-arrow-down-alt2', 'radio' => 'dashicons-marker', 'checkbox' => 'dashicons-yes', 'number' => 'dashicons-editor-ol', 'date' => 'dashicons-calendar', 'time' => 'dashicons-clock', 'phone' => 'dashicons-phone', 'url' => 'dashicons-admin-links', 'file' => 'dashicons-upload', 'consent' => 'dashicons-privacy', 'poll' => 'dashicons-chart-bar', 'quiz' => 'dashicons-welcome-learn-more', 'rating' => 'dashicons-star-filled', 'slider' => 'dashicons-image-flip-horizontal', 'hidden' => 'dashicons-hidden', 'html' => 'dashicons-editor-code', 'captcha' => 'dashicons-shield', 'page_break' => 'dashicons-controls-forward', 'heading' => 'dashicons-heading', 'calculation' => 'dashicons-editor-table', 'field_group' => 'dashicons-grid-view', 'signature' => 'dashicons-edit');
                        foreach ($fields as $type => $label) {
                            printf('<button type="button" class="webform-palette-item" data-type="%s"><span class="dashicons %s"></span><span>%s</span></button>', esc_attr($type), esc_attr($field_icons[$type] ?? 'dashicons-plus-alt2'), esc_html($label));
                        }
                        ?>
                    </div>
                    <?php if (!$this->is_pro_active()) : ?><div class="webform-picker-pro"><div><span class="webform-pro-badge"><?php esc_html_e('RECOMMENDED PRO', 'webform'); ?></span><h3><?php esc_html_e('Build revenue and automated workflows', 'webform'); ?></h3><p><?php esc_html_e('Upgrade for calculated totals, grouped layouts, signatures, PDF notifications, hosted payments, Mailchimp, Brevo, Zapier, premium styles, and 20 business templates.', 'webform'); ?></p></div><div class="webform-pro-field-list"><div><span class="dashicons dashicons-editor-table"></span><?php esc_html_e('Calculations', 'webform'); ?></div><div><span class="dashicons dashicons-grid-view"></span><?php esc_html_e('Field groups', 'webform'); ?></div><div><span class="dashicons dashicons-edit"></span><?php esc_html_e('E-signatures', 'webform'); ?></div><div><span class="dashicons dashicons-media-document"></span><?php esc_html_e('PDF notifications', 'webform'); ?></div></div><a class="button button-primary" href="<?php echo esc_url($this->upgrade_url('field-picker')); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('See everything in Pro — $19.99/year', 'webform'); ?></a></div><?php endif; ?>
                    </div>
                </aside>
                <main class="webform-canvas-panel">
                    <div class="webform-stage-tabs"><div id="webform-stage-tabs"></div><div class="webform-canvas-tools"><button type="button" class="button button-primary webform-open-field-picker"><span class="dashicons dashicons-plus-alt2"></span><?php esc_html_e('Add field', 'webform'); ?></button><button class="button" id="webform-add-stage">+ <?php esc_html_e('Add stage', 'webform'); ?></button></div></div>
                    <div id="webform-canvas" class="webform-canvas"></div>
                </main>
                <aside class="webform-properties">
                    <div class="webform-property-tabs"><button type="button" class="is-active" data-panel="field"><?php esc_html_e('Field', 'webform'); ?></button><button type="button" data-panel="confirmation"><?php esc_html_e('Confirmation', 'webform'); ?></button><button type="button" data-panel="integrations"><?php esc_html_e('Integrations', 'webform'); ?></button><button type="button" data-panel="access"><?php esc_html_e('Access', 'webform'); ?></button><button type="button" data-panel="style"><?php esc_html_e('Style', 'webform'); ?></button></div>
                    <div class="webform-property-panel is-active" data-panel="field"><h2><?php esc_html_e('Field settings', 'webform'); ?></h2><div id="webform-field-settings"><p class="description"><?php esc_html_e('Select a field to edit its options.', 'webform'); ?></p></div></div>
                    <div class="webform-property-panel" data-panel="confirmation"><h2><?php esc_html_e('Confirmation', 'webform'); ?></h2>
                    <label><?php esc_html_e('Success message', 'webform'); ?><textarea id="webform-success-message" rows="3"><?php echo esc_textarea(isset($settings['success_message']) ? $settings['success_message'] : __('Thanks! Your response has been submitted.', 'webform')); ?></textarea></label>
                    <label><?php esc_html_e('Notification email', 'webform'); ?><input type="email" id="webform-notification-email" value="<?php echo esc_attr(isset($settings['notification_email']) ? $settings['notification_email'] : get_option('admin_email')); ?>"></label>
                    <label><?php esc_html_e('Submit button text', 'webform'); ?><input type="text" id="webform-submit-label" value="<?php echo esc_attr(isset($settings['submit_label']) ? $settings['submit_label'] : __('Submit', 'webform')); ?>"></label>
                    <label><?php esc_html_e('Redirect URL (optional)', 'webform'); ?><input type="url" id="webform-redirect-url" value="<?php echo esc_attr($settings['redirect_url'] ?? ''); ?>"></label>
                    <label><?php esc_html_e('Webhook URL (optional)', 'webform'); ?><input type="url" id="webform-webhook-url" value="<?php echo esc_attr($settings['webhook_url'] ?? ''); ?>"></label>
                    </div><div class="webform-property-panel" data-panel="access"><h2><?php esc_html_e('Access and limits', 'webform'); ?></h2>
                    <label class="webform-check"><input type="checkbox" id="webform-require-login" <?php checked(!empty($settings['require_login'])); ?>> <?php esc_html_e('Require visitors to log in', 'webform'); ?></label>
                    <label><?php esc_html_e('Maximum total entries', 'webform'); ?><input type="number" min="0" id="webform-submission-limit" value="<?php echo esc_attr(absint($settings['submission_limit'] ?? 0)); ?>"><small><?php esc_html_e('Use 0 for unlimited.', 'webform'); ?></small></label>
                    <label><?php esc_html_e('Closed form message', 'webform'); ?><textarea id="webform-closed-message" rows="3"><?php echo esc_textarea($settings['closed_message'] ?? __('This form is currently unavailable.', 'webform')); ?></textarea></label>
                    </div><div class="webform-property-panel" data-panel="style"><h2><?php esc_html_e('Appearance', 'webform'); ?></h2>
                    <label><?php esc_html_e('Style preset', 'webform'); ?><select id="webform-style-preset"><?php foreach (apply_filters('webform_style_presets', array('modern' => __('Modern', 'webform'), 'minimal' => __('Minimal', 'webform'), 'rounded' => __('Rounded', 'webform'))) as $preset_key => $preset_label) : ?><option value="<?php echo esc_attr($preset_key); ?>" <?php selected($settings['style_preset'] ?? 'modern', $preset_key); ?>><?php echo esc_html($preset_label); ?></option><?php endforeach; ?></select></label>
                    <label><?php esc_html_e('Accent color', 'webform'); ?><input type="color" id="webform-accent-color" value="<?php echo esc_attr($settings['accent_color'] ?? '#6c4bd4'); ?>"></label>
                    <label><?php esc_html_e('Button text color', 'webform'); ?><input type="color" id="webform-button-text-color" value="<?php echo esc_attr($settings['button_text_color'] ?? '#ffffff'); ?>"></label>
                    </div>
                    <div class="webform-property-panel" data-panel="integrations"><h2><?php esc_html_e('Integrations', 'webform'); ?></h2><?php if ($this->is_pro_active()) : do_action('webform_builder_integrations_panel', $form_id); else : ?><p class="description"><?php esc_html_e('Connect form submissions to your business tools with Webform Pro.', 'webform'); ?></p><div class="webform-integration-list"><div><span class="dashicons dashicons-email"></span><strong>Mailchimp</strong><small><?php esc_html_e('Email audiences', 'webform'); ?></small></div><div><span class="dashicons dashicons-megaphone"></span><strong>Brevo</strong><small><?php esc_html_e('Marketing automation', 'webform'); ?></small></div><div><span class="dashicons dashicons-randomize"></span><strong>Zapier</strong><small><?php esc_html_e('Thousands of apps', 'webform'); ?></small></div><div><span class="dashicons dashicons-money-alt"></span><strong>Stripe / PayPal</strong><small><?php esc_html_e('Hosted payments', 'webform'); ?></small></div></div><a class="button button-primary" href="<?php echo esc_url($this->upgrade_url('integrations-tab')); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Unlock integrations', 'webform'); ?></a><?php endif; ?></div>
                </aside>
            </div>
            <?php if (!$form_id && !$template_key) : ?><div class="webform-template-modal" id="webform-template-modal" role="dialog" aria-modal="true" aria-labelledby="webform-template-title"><div class="webform-template-dialog"><button type="button" class="webform-template-close" aria-label="<?php esc_attr_e('Close', 'webform'); ?>">×</button><h2 id="webform-template-title"><?php esc_html_e('Choose a starting template', 'webform'); ?></h2><p><?php esc_html_e('Select a template or start from a blank form.', 'webform'); ?></p><div class="webform-template-modal-grid"><a class="webform-template-choice" href="#"><strong><?php esc_html_e('Blank Form', 'webform'); ?></strong><span><?php esc_html_e('Build from scratch', 'webform'); ?></span></a><?php foreach ($this->free_templates() as $key => $template) : ?><a class="webform-template-choice" href="<?php echo esc_url(admin_url('admin.php?page=webform-builder&template=' . $key)); ?>"><strong><?php echo esc_html($template['name']); ?></strong><span><?php echo esc_html($template['description']); ?></span></a><?php endforeach; ?></div></div></div><?php endif; ?>
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
            'style_preset' => array_key_exists($settings['style_preset'] ?? '', apply_filters('webform_style_presets', array('modern' => 'Modern', 'minimal' => 'Minimal', 'rounded' => 'Rounded'))) ? $settings['style_preset'] : 'modern',
            'accent_color' => sanitize_hex_color($settings['accent_color'] ?? '') ?: '#6c4bd4',
            'button_text_color' => sanitize_hex_color($settings['button_text_color'] ?? '') ?: '#ffffff',
        ));
        wp_send_json_success(array('id' => $form_id, 'message' => __('Saved', 'webform'), 'shortcode' => '[webform id="' . $form_id . '"]'));
    }

    private function sanitize_schema($schema) {
        $clean = array();
        $allowed_types = apply_filters('webform_allowed_field_types', array('text', 'email', 'textarea', 'select', 'radio', 'checkbox', 'number', 'date', 'time', 'phone', 'url', 'file', 'consent', 'poll', 'quiz', 'rating', 'slider', 'hidden', 'html', 'captcha', 'heading'));
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
                $clean_field = array(
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
                    'default_value' => sanitize_text_field($field['default_value'] ?? ''),
                    'html' => wp_kses_post($field['html'] ?? ''),
                    'min' => floatval($field['min'] ?? 0),
                    'max' => floatval($field['max'] ?? 100),
                    'step' => max(0.01, floatval($field['step'] ?? 1)),
                    'condition' => array(
                        'enabled' => !empty($field['condition']['enabled']),
                        'field_id' => sanitize_key($field['condition']['field_id'] ?? ''),
                        'operator' => in_array($field['condition']['operator'] ?? '', $allowed_operators, true) ? $field['condition']['operator'] : 'equals',
                        'value' => sanitize_text_field($field['condition']['value'] ?? ''),
                    ),
                );
                $clean_stage['fields'][] = apply_filters('webform_sanitize_field', $clean_field, $field);
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

    public function suppress_editor_notices() {
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
        if ($page !== 'webform-builder') return;
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
    }

    public function settings_page() {
        if (!current_user_can('manage_options')) return;
        $settings = wp_parse_args((array) get_option('webform_global_settings', array()), array('recaptcha_enabled' => false, 'recaptcha_site_key' => '', 'recaptcha_secret_key' => ''));
        ?>
        <div class="wrap webform-wrap"><div class="webform-page-head"><div><h1><?php esc_html_e('Webform Settings', 'webform'); ?></h1><p><?php esc_html_e('Global security and service configuration.', 'webform'); ?></p></div></div>
        <form class="webform-settings-card" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post"><input type="hidden" name="action" value="webform_save_global_settings"><?php wp_nonce_field('webform_save_global_settings'); ?>
            <h2><?php esc_html_e('Google reCAPTCHA v2', 'webform'); ?></h2>
            <p><?php esc_html_e('Create Checkbox keys in the Google reCAPTCHA console, then add a CAPTCHA field to any form.', 'webform'); ?></p>
            <label><input type="checkbox" name="recaptcha_enabled" value="1" <?php checked(!empty($settings['recaptcha_enabled'])); ?>> <?php esc_html_e('Enable Google reCAPTCHA', 'webform'); ?></label>
            <label><?php esc_html_e('Site key', 'webform'); ?><input name="recaptcha_site_key" value="<?php echo esc_attr($settings['recaptcha_site_key']); ?>"></label>
            <label><?php esc_html_e('Secret key', 'webform'); ?><input type="password" name="recaptcha_secret_key" value="<?php echo esc_attr($settings['recaptcha_secret_key']); ?>" autocomplete="off"></label>
            <button class="button button-primary"><?php esc_html_e('Save settings', 'webform'); ?></button>
        </form></div>
        <?php
    }

    public function save_global_settings() {
        if (!current_user_can('manage_options')) wp_die(esc_html__('Permission denied.', 'webform'));
        check_admin_referer('webform_save_global_settings');
        update_option('webform_global_settings', array(
            'recaptcha_enabled' => !empty($_POST['recaptcha_enabled']),
            'recaptcha_site_key' => sanitize_text_field(wp_unslash($_POST['recaptcha_site_key'] ?? '')),
            'recaptcha_secret_key' => sanitize_text_field(wp_unslash($_POST['recaptcha_secret_key'] ?? '')),
        ), false);
        wp_safe_redirect(admin_url('admin.php?page=webform-settings'));
        exit;
    }

    public function import_page() {
        if (!current_user_can('manage_options')) return;
        ?>
        <div class="wrap webform-wrap"><div class="webform-page-head"><div><h1><?php esc_html_e('Import Forms', 'webform'); ?></h1><p><?php esc_html_e('Convert exported forms into editable Webform forms.', 'webform'); ?></p></div></div>
        <form class="webform-settings-card" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="webform_import"><?php wp_nonce_field('webform_import'); ?>
            <label><?php esc_html_e('Source plugin', 'webform'); ?><select name="source"><option value="wpforms">WPForms JSON</option><option value="gravity">Gravity Forms JSON</option><option value="fluent">Fluent Forms JSON</option><option value="cf7">Contact Form 7 markup</option></select></label>
            <label><?php esc_html_e('Export file', 'webform'); ?><input type="file" name="import_file" accept=".json,.txt"></label>
            <label><?php esc_html_e('Or paste exported content', 'webform'); ?><textarea name="import_content" rows="12"></textarea></label>
            <button class="button button-primary"><?php esc_html_e('Import and edit', 'webform'); ?></button>
        </form></div>
        <?php
    }

    public function import_form() {
        if (!current_user_can('manage_options')) wp_die(esc_html__('Permission denied.', 'webform'));
        check_admin_referer('webform_import');
        $source = sanitize_key(wp_unslash($_POST['source'] ?? ''));
        $content = trim((string) wp_unslash($_POST['import_content'] ?? ''));
        if (!$content && !empty($_FILES['import_file']['tmp_name']) && absint($_FILES['import_file']['size']) <= 2 * MB_IN_BYTES) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
            global $wp_filesystem;
            $content = $wp_filesystem->get_contents($_FILES['import_file']['tmp_name']);
        }
        if (!$content) wp_die(esc_html__('No import content was provided.', 'webform'));
        if ($source === 'cf7') {
            $converted = $this->convert_cf7($content);
        } else {
            $decoded = json_decode($content, true);
            if (!is_array($decoded)) wp_die(esc_html__('The import file is not valid JSON.', 'webform'));
            $converted = $this->convert_json_form($decoded);
        }
        if (empty($converted['schema'])) wp_die(esc_html__('No supported fields were found in the export.', 'webform'));
        $form_id = wp_insert_post(array('post_type' => 'webform_form', 'post_status' => 'publish', 'post_title' => sanitize_text_field($converted['name'] ?: __('Imported Form', 'webform'))));
        update_post_meta($form_id, '_webform_schema', $this->sanitize_schema($converted['schema']));
        update_post_meta($form_id, '_webform_settings', array('success_message' => __('Thanks! Your response has been submitted.', 'webform'), 'notification_email' => get_option('admin_email')));
        wp_safe_redirect(admin_url('admin.php?page=webform-builder&form_id=' . $form_id));
        exit;
    }

    private function convert_json_form($data) {
        $node = $this->find_form_node($data);
        $name = sanitize_text_field($node['title'] ?? ($node['name'] ?? ($node['settings']['form_title'] ?? __('Imported Form', 'webform'))));
        $source_fields = $node['fields'] ?? ($node['form_fields'] ?? array());
        if (is_string($source_fields)) $source_fields = json_decode($source_fields, true);
        $stages = array(array('id' => 'stage_imported', 'title' => __('Imported Form', 'webform'), 'fields' => array()));
        foreach ((array) $source_fields as $key => $source) {
            if (!is_array($source)) continue;
            $type = sanitize_key($source['type'] ?? ($source['element'] ?? 'text'));
            if (in_array($type, array('page', 'pagebreak', 'step'), true)) {
                $stages[] = array('id' => 'stage_' . count($stages), 'title' => sanitize_text_field($source['label'] ?? sprintf(__('Stage %d', 'webform'), count($stages) + 1)), 'fields' => array());
                continue;
            }
            $map = array('name' => 'text', 'phone' => 'phone', 'email' => 'email', 'textarea' => 'textarea', 'select' => 'select', 'radio' => 'radio', 'checkbox' => 'checkbox', 'number' => 'number', 'date' => 'date', 'time' => 'time', 'url' => 'url', 'file' => 'file', 'html' => 'html', 'rating' => 'rating');
            $type = $map[$type] ?? 'text';
            $choices = array();
            foreach ((array) ($source['choices'] ?? ($source['options'] ?? array())) as $choice) $choices[] = sanitize_text_field(is_array($choice) ? ($choice['label'] ?? ($choice['text'] ?? ($choice['value'] ?? ''))) : $choice);
            $stages[count($stages) - 1]['fields'][] = array('id' => sanitize_key($source['id'] ?? $key) ?: 'field_' . wp_generate_password(6, false, false), 'type' => $type, 'label' => sanitize_text_field($source['label'] ?? ($source['adminLabel'] ?? __('Imported Field', 'webform'))), 'placeholder' => sanitize_text_field($source['placeholder'] ?? ''), 'required' => !empty($source['required']) || !empty($source['isRequired']), 'options' => array_filter($choices));
        }
        return array('name' => $name, 'schema' => $stages);
    }

    private function find_form_node($data) {
        if (isset($data['fields']) || isset($data['form_fields'])) return $data;
        foreach ($data as $value) if (is_array($value)) {
            $found = $this->find_form_node($value);
            if (isset($found['fields']) || isset($found['form_fields'])) return $found;
        }
        return $data;
    }

    private function convert_cf7($content) {
        $fields = array();
        preg_match_all('/\[(text|email|tel|url|number|date|textarea|select|checkbox|radio|file)(\*)?\s+([a-zA-Z0-9_-]+)[^\]]*\]/', $content, $matches, PREG_SET_ORDER);
        $map = array('tel' => 'phone');
        foreach ($matches as $match) $fields[] = array('id' => sanitize_key($match[3]), 'type' => $map[$match[1]] ?? $match[1], 'label' => ucwords(str_replace(array('-', '_'), ' ', $match[3])), 'placeholder' => '', 'required' => $match[2] === '*', 'options' => array());
        return array('name' => __('Imported Contact Form', 'webform'), 'schema' => array(array('id' => 'stage_imported', 'title' => __('Contact Form', 'webform'), 'fields' => $fields)));
    }

    public function templates_page() {
        if (!current_user_can('manage_options')) return;
        ?>
        <div class="wrap webform-wrap">
            <div class="webform-page-head"><div><h1><?php esc_html_e('Form Templates', 'webform'); ?></h1><p><?php esc_html_e('Start with a complete form, then customize every field and stage.', 'webform'); ?></p></div></div>
            <div class="webform-template-grid">
                <div class="webform-template-card"><span class="dashicons dashicons-plus-alt2"></span><h2><?php esc_html_e('Blank Form', 'webform'); ?></h2><p><?php esc_html_e('Start with an empty stage.', 'webform'); ?></p><a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=webform-builder')); ?>"><?php esc_html_e('Create', 'webform'); ?></a></div>
                <?php foreach ($this->free_templates() as $key => $template) : ?>
                    <div class="webform-template-card"><?php if (!empty($template['pro'])) : ?><span class="webform-pro-badge"><?php esc_html_e('PRO', 'webform'); ?></span><?php else : ?><span class="dashicons <?php echo esc_attr($template['icon']); ?>"></span><?php endif; ?><h2><?php echo esc_html($template['name']); ?></h2><p><?php echo esc_html($template['description']); ?></p><a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=webform-builder&template=' . $key)); ?>"><?php esc_html_e('Use template', 'webform'); ?></a></div>
                <?php endforeach; ?>
                <?php if (!$this->is_pro_active()) : ?><div class="webform-template-card webform-template-pro"><span class="webform-pro-badge"><?php esc_html_e('PRO', 'webform'); ?></span><h2><?php esc_html_e('20 Premium Templates', 'webform'); ?></h2><p><?php esc_html_e('Payments, lead generation, bookings, applications, orders, onboarding, and advanced business workflows.', 'webform'); ?></p><a class="button" href="<?php echo esc_url($this->upgrade_url('templates')); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Explore Pro', 'webform'); ?></a></div><?php endif; ?>
            </div>
        </div>
        <?php
    }

    private function free_templates() {
        return apply_filters('webform_templates', array(
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
        ));
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
