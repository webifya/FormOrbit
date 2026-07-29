<?php

defined('ABSPATH') || exit;

class Webform_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'menu'));
        add_action('admin_enqueue_scripts', array($this, 'assets'));
        add_action('wp_ajax_webform_save_form', array($this, 'save_form'));
        add_action('wp_ajax_webform_delete_form', array($this, 'delete_form'));
        add_action('admin_post_webform_restore_form', array($this, 'restore_form'));
        add_action('admin_post_webform_permanently_delete_form', array($this, 'permanently_delete_form'));
        add_action('admin_post_webform_duplicate_form', array($this, 'duplicate_form'));
        add_action('admin_post_webform_export_entries', array($this, 'export_entries'));
        add_action('admin_post_webform_delete_entry', array($this, 'delete_entry'));
        add_action('admin_post_webform_save_global_settings', array($this, 'save_global_settings'));
        add_action('admin_post_webform_import', array($this, 'import_form'));
        add_action('admin_head', array($this, 'suppress_editor_notices'), 1);
    }

    public function menu() {
        add_menu_page(
            __('Mahfuzar Form Builder', 'mahfuzar-form-builder'),
            __('Mahfuzar Forms', 'mahfuzar-form-builder'),
            'manage_options',
            'webform',
            array($this, 'forms_page'),
            'dashicons-feedback',
            26
        );
        add_submenu_page('webform', __('All Forms', 'mahfuzar-form-builder'), __('All Forms', 'mahfuzar-form-builder'), 'manage_options', 'webform', array($this, 'forms_page'), 0);
        add_submenu_page('webform', __('Add New', 'mahfuzar-form-builder'), __('Add New', 'mahfuzar-form-builder'), 'manage_options', 'webform-builder', array($this, 'builder_page'), 1);
        add_submenu_page('webform', __('Form Templates', 'mahfuzar-form-builder'), __('Templates', 'mahfuzar-form-builder'), 'manage_options', 'webform-templates', array($this, 'templates_page'), 3);
        add_submenu_page('webform', __('Entries', 'mahfuzar-form-builder'), __('Entries', 'mahfuzar-form-builder'), 'manage_options', 'webform-entries', array($this, 'entries_page'), 4);
        add_submenu_page('webform', __('Import and Export Forms', 'mahfuzar-form-builder'), __('Import / Export', 'mahfuzar-form-builder'), 'manage_options', 'webform-import', array($this, 'import_page'), 5);
        add_submenu_page('webform', __('Webform Settings', 'mahfuzar-form-builder'), __('Settings', 'mahfuzar-form-builder'), 'manage_options', 'webform-settings', array($this, 'settings_page'), 6);
        if (!$this->is_pro_active()) {
            add_submenu_page('webform', __('Webform Pro', 'mahfuzar-form-builder'), __('Upgrade to Pro', 'mahfuzar-form-builder'), 'manage_options', 'webform-pro', array($this, 'pro_page'), 7);
        }
    }

    public function assets($hook) {
        if (strpos($hook, 'mahfuzar-form-builder') === false) {
            return;
        }
        wp_enqueue_style('webform-admin', WEBFORM_URL . 'assets/css/admin.css', array(), WEBFORM_VERSION);
        wp_enqueue_style('webform-builder-refresh', WEBFORM_URL . 'assets/css/builder-refresh.css', array('webform-admin'), WEBFORM_VERSION);
        wp_enqueue_style('webform-field-previews', WEBFORM_URL . 'assets/css/field-previews.css', array('webform-builder-refresh'), WEBFORM_VERSION);
        wp_enqueue_style('webform-responsive-upgrades', WEBFORM_URL . 'assets/css/responsive-upgrades.css', array('webform-field-previews'), WEBFORM_VERSION);
        wp_enqueue_style('webform-field-style-upgrades', WEBFORM_URL . 'assets/css/field-style-upgrades.css', array('webform-responsive-upgrades'), WEBFORM_VERSION);
        wp_enqueue_style('webform-public-preview', WEBFORM_URL . 'assets/css/public.css', array('webform-field-style-upgrades'), WEBFORM_VERSION);
        wp_enqueue_style('webform-preset-preview', WEBFORM_URL . 'assets/css/preset-preview.css', array('webform-public-preview'), WEBFORM_VERSION);
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('webform-admin', WEBFORM_URL . 'assets/js/admin.js', array('jquery', 'jquery-ui-sortable'), WEBFORM_VERSION, true);
        wp_localize_script('webform-admin', 'WebformAdmin', apply_filters('webform_admin_script_data', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('webform_admin'),
            'formsUrl' => admin_url('admin.php?page=webform'),
            'proInstalled' => false,
            'proActive' => false,
            'proFieldTypes' => array('calculation', 'field_group', 'signature', 'rich_text', 'address', 'repeater', 'appointment', 'nps', 'currency', 'product', 'price'),
            'proStyling' => false,
            'savedThemes' => array(),
        )));
    }

    public function forms_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $current_view = isset($_GET['form_status']) && sanitize_key(wp_unslash($_GET['form_status'])) === 'trash' ? 'trash' : 'all';
        $post_counts = wp_count_posts('webform_form');
        $active_count = absint($post_counts->publish ?? 0) + absint($post_counts->draft ?? 0);
        $trash_count = absint($post_counts->trash ?? 0);
        $forms = get_posts(array('post_type' => 'webform_form', 'posts_per_page' => -1, 'post_status' => $current_view === 'trash' ? 'trash' : array('publish', 'draft'), 'orderby' => 'modified', 'order' => 'DESC'));
        $embed_counts = $this->embed_counts();
        ?>
        <div class="wrap webform-wrap">
            <div class="webform-page-head">
                <div><h1><?php esc_html_e('Mahfuzar Forms', 'mahfuzar-form-builder'); ?></h1><p><?php esc_html_e('Build and manage forms without code.', 'mahfuzar-form-builder'); ?></p></div>
                <a class="button button-primary webform-create-button" href="<?php echo esc_url(admin_url('admin.php?page=webform-builder')); ?>"><span class="dashicons dashicons-plus-alt2"></span><?php esc_html_e('Create form', 'mahfuzar-form-builder'); ?></a>
            </div>
            <div class="webform-card webform-list-card">
                <div class="webform-list-toolbar">
                    <nav class="webform-list-tabs" aria-label="<?php esc_attr_e('Form views', 'mahfuzar-form-builder'); ?>">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=webform')); ?>" class="<?php echo $current_view === 'all' ? 'is-active' : ''; ?>"><span class="dashicons dashicons-feedback"></span><?php esc_html_e('All forms', 'mahfuzar-form-builder'); ?><strong><?php echo esc_html($active_count); ?></strong></a>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=webform&form_status=trash')); ?>" class="<?php echo $current_view === 'trash' ? 'is-active' : ''; ?>"><span class="dashicons dashicons-trash"></span><?php esc_html_e('Trash', 'mahfuzar-form-builder'); ?><strong><?php echo esc_html($trash_count); ?></strong></a>
                    </nav>
                    <?php if ($forms) : ?><label class="webform-list-search"><span class="dashicons dashicons-search"></span><span class="screen-reader-text"><?php esc_html_e('Search forms', 'mahfuzar-form-builder'); ?></span><input type="search" id="webform-form-search" placeholder="<?php esc_attr_e('Search forms…', 'mahfuzar-form-builder'); ?>" autocomplete="off"></label><?php endif; ?>
                </div>
                <?php if (!$forms) : ?>
                    <div class="webform-empty"><span class="dashicons <?php echo $current_view === 'trash' ? 'dashicons-trash' : 'dashicons-feedback'; ?>"></span><h2><?php echo $current_view === 'trash' ? esc_html__('Trash is empty', 'mahfuzar-form-builder') : esc_html__('Create your first form', 'mahfuzar-form-builder'); ?></h2><p><?php echo $current_view === 'trash' ? esc_html__('Deleted forms will appear here until they are restored or permanently removed.', 'mahfuzar-form-builder') : esc_html__('Add fields, arrange stages, and publish it with a shortcode.', 'mahfuzar-form-builder'); ?></p></div>
                <?php else : ?>
                    <div class="webform-list-table-wrap"><table class="wp-list-table widefat fixed table-view-list webform-forms-table"><thead><tr>
                        <th class="column-primary"><?php esc_html_e('Form', 'mahfuzar-form-builder'); ?></th><th class="column-shortcode"><?php esc_html_e('Shortcode', 'mahfuzar-form-builder'); ?></th><th class="column-number"><?php esc_html_e('Entries', 'mahfuzar-form-builder'); ?></th><th class="column-number"><?php esc_html_e('Embeds', 'mahfuzar-form-builder'); ?></th><th class="column-status"><?php esc_html_e('Status', 'mahfuzar-form-builder'); ?></th><th class="column-date"><?php esc_html_e('Created', 'mahfuzar-form-builder'); ?></th><th class="column-date"><?php esc_html_e('Updated', 'mahfuzar-form-builder'); ?></th>
                    </tr></thead><tbody>
                    <?php foreach ($forms as $form) : ?>
                        <?php $edit_url = admin_url('admin.php?page=webform-builder&form_id=' . $form->ID); $preview_url = wp_nonce_url(add_query_arg('webform_preview', $form->ID, home_url('/')), 'webform_preview_' . $form->ID); $schema = (array) get_post_meta($form->ID, '_webform_schema', true); $stage_count = max(1, count($schema)); $shortcode = '[webform id="' . $form->ID . '"]'; ?>
                        <tr data-form-title="<?php echo esc_attr(strtolower($form->post_title . ' ' . $form->ID)); ?>">
                            <td class="column-primary" data-colname="<?php esc_attr_e('Form', 'mahfuzar-form-builder'); ?>"><div class="webform-form-cell"><span class="webform-form-icon"><span class="dashicons dashicons-feedback"></span></span><div class="webform-form-summary"><strong class="webform-form-title"><?php if ($current_view === 'trash') : ?><?php echo esc_html($form->post_title); ?><?php else : ?><a class="row-title" href="<?php echo esc_url($edit_url); ?>"><?php echo esc_html($form->post_title); ?></a><a class="webform-title-preview" href="<?php echo esc_url($preview_url); ?>" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e('Preview form', 'mahfuzar-form-builder'); ?>" aria-label="<?php esc_attr_e('Preview form', 'mahfuzar-form-builder'); ?>"><span class="dashicons dashicons-visibility" aria-hidden="true"></span></a><?php endif; ?></strong><small><?php echo esc_html(sprintf(_n('ID %1$d · %2$d stage', 'ID %1$d · %2$d stages', $stage_count, 'mahfuzar-form-builder'), $form->ID, $stage_count)); ?></small>
                                <?php if ($current_view === 'trash') : ?>
                                    <div class="row-actions"><span><a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=webform_restore_form&form_id=' . $form->ID), 'webform_restore_' . $form->ID)); ?>"><span class="dashicons dashicons-undo"></span><?php esc_html_e('Restore', 'mahfuzar-form-builder'); ?></a></span><span class="delete"><a onclick="return confirm('<?php echo esc_js(__('Permanently delete this form and all of its entries?', 'mahfuzar-form-builder')); ?>')" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=webform_permanently_delete_form&form_id=' . $form->ID), 'webform_permanently_delete_' . $form->ID)); ?>"><span class="dashicons dashicons-trash"></span><?php esc_html_e('Delete permanently', 'mahfuzar-form-builder'); ?></a></span></div>
                                <?php else : ?>
                                    <div class="row-actions"><span><a href="<?php echo esc_url($edit_url); ?>"><span class="dashicons dashicons-edit"></span><?php esc_html_e('Edit', 'mahfuzar-form-builder'); ?></a></span><span><a href="<?php echo esc_url(add_query_arg('panel', 'confirmation', $edit_url)); ?>"><span class="dashicons dashicons-admin-generic"></span><?php esc_html_e('Settings', 'mahfuzar-form-builder'); ?></a></span><span><a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=webform_duplicate_form&form_id=' . $form->ID), 'webform_duplicate_' . $form->ID)); ?>"><span class="dashicons dashicons-admin-page"></span><?php esc_html_e('Duplicate', 'mahfuzar-form-builder'); ?></a></span><span class="trash"><button type="button" class="button-link-delete webform-delete" data-id="<?php echo esc_attr($form->ID); ?>"><span class="dashicons dashicons-trash"></span><?php esc_html_e('Trash', 'mahfuzar-form-builder'); ?></button></span></div>
                                <?php endif; ?>
                                </div></div>
                                <button type="button" class="toggle-row"><span class="screen-reader-text"><?php esc_html_e('Show more details', 'mahfuzar-form-builder'); ?></span></button>
                            </td>
                            <td data-colname="<?php esc_attr_e('Shortcode', 'mahfuzar-form-builder'); ?>"><span class="webform-shortcode"><code><?php echo esc_html($shortcode); ?></code><button type="button" class="webform-copy-shortcode" data-shortcode="<?php echo esc_attr($shortcode); ?>" title="<?php esc_attr_e('Copy shortcode', 'mahfuzar-form-builder'); ?>" aria-label="<?php esc_attr_e('Copy shortcode', 'mahfuzar-form-builder'); ?>"><span class="dashicons dashicons-admin-page"></span></button></span></td>
                            <td data-colname="<?php esc_attr_e('Entries', 'mahfuzar-form-builder'); ?>"><a class="webform-count-link" href="<?php echo esc_url(admin_url('admin.php?page=webform-entries&form_id=' . $form->ID)); ?>"><?php echo esc_html($this->entry_count($form->ID)); ?></a></td>
                            <td data-colname="<?php esc_attr_e('Embeds', 'mahfuzar-form-builder'); ?>"><span class="webform-count-value"><?php echo esc_html($embed_counts[$form->ID] ?? 0); ?></span></td>
                            <td data-colname="<?php esc_attr_e('Status', 'mahfuzar-form-builder'); ?>"><span class="webform-status webform-status-<?php echo esc_attr($form->post_status); ?>"><?php echo esc_html($form->post_status === 'trash' ? __('Trashed', 'mahfuzar-form-builder') : ($form->post_status === 'publish' ? __('Published', 'mahfuzar-form-builder') : __('Draft', 'mahfuzar-form-builder'))); ?></span></td>
                            <td data-colname="<?php esc_attr_e('Created', 'mahfuzar-form-builder'); ?>" title="<?php echo esc_attr(get_the_date('c', $form)); ?>"><span class="webform-date"><?php echo esc_html(get_the_date('M j, Y', $form)); ?></span></td>
                            <td data-colname="<?php esc_attr_e('Updated', 'mahfuzar-form-builder'); ?>" title="<?php echo esc_attr(get_the_modified_date('c', $form)); ?>"><span class="webform-date"><?php echo esc_html(get_the_modified_date('M j, Y', $form)); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="webform-no-search-results" hidden><td colspan="7"><span class="dashicons dashicons-search"></span><strong><?php esc_html_e('No matching forms', 'mahfuzar-form-builder'); ?></strong><small><?php esc_html_e('Try another form name or ID.', 'mahfuzar-form-builder'); ?></small></td></tr>
                    </tbody></table></div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    private function entry_count($form_id) {
        $query = new WP_Query(array('post_type' => 'webform_entry', 'post_status' => 'private', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_query' => array('relation' => 'AND', array('key' => '_webform_form_id', 'value' => $form_id), array('relation' => 'OR', array('key' => '_webform_entry_status', 'compare' => 'NOT EXISTS'), array('key' => '_webform_entry_status', 'value' => 'submitted')))));
        return $query->found_posts;
    }

    private function embed_counts() {
        $counts = array();
        $content_ids = get_posts(array('post_type' => 'any', 'post_status' => array('publish', 'private', 'draft', 'future'), 'posts_per_page' => 2000, 'fields' => 'ids', 's' => 'webform', 'no_found_rows' => true, 'orderby' => 'none'));
        foreach ($content_ids as $content_id) {
            $content = (string) get_post_field('post_content', $content_id);
            if (!preg_match_all('/\[webform\b[^\]]*\bid\s*=\s*(["\']?)(\d+)\1[^\]]*\]/i', $content, $matches)) {
                continue;
            }
            foreach ($matches[2] as $embedded_form_id) {
                $embedded_form_id = absint($embedded_form_id);
                $counts[$embedded_form_id] = ($counts[$embedded_form_id] ?? 0) + 1;
            }
        }
        return $counts;
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
                <div><h1><?php echo $form ? esc_html__('Edit form', 'mahfuzar-form-builder') : esc_html__('Create form', 'mahfuzar-form-builder'); ?></h1><p><?php esc_html_e('Drag fields into a stage, then select a field to configure it.', 'mahfuzar-form-builder'); ?></p></div>
                <div><span id="webform-save-status"></span> <button class="button button-primary button-hero" id="webform-save"><?php esc_html_e('Save form', 'mahfuzar-form-builder'); ?></button></div>
            </div>
            <input type="hidden" id="webform-id" value="<?php echo esc_attr($form_id); ?>">
            <input type="hidden" id="webform-schema" value="<?php echo esc_attr(wp_json_encode($schema ? $schema : array())); ?>">
            <input type="hidden" id="webform-settings" value="<?php echo esc_attr(wp_json_encode($settings ? $settings : array())); ?>">
            <div class="webform-name-row">
                <label for="webform-name"><?php esc_html_e('Form name', 'mahfuzar-form-builder'); ?></label>
                <input id="webform-name" class="regular-text" value="<?php echo esc_attr($form ? $form->post_title : __('Untitled form', 'mahfuzar-form-builder')); ?>">
            </div>
            <div class="webform-builder">
                <aside class="webform-field-picker" id="webform-field-picker" aria-hidden="true">
                    <div class="webform-field-picker-backdrop"></div><div class="webform-field-picker-dialog"><div class="webform-field-picker-head"><div><h2><?php esc_html_e('Add a field', 'mahfuzar-form-builder'); ?></h2><p><?php esc_html_e('Choose a field to add to the current stage.', 'mahfuzar-form-builder'); ?></p></div><button type="button" class="webform-field-picker-close" aria-label="<?php esc_attr_e('Close field picker', 'mahfuzar-form-builder'); ?>">×</button></div>
                    <h3><?php esc_html_e('Standard fields', 'mahfuzar-form-builder'); ?></h3>
                    <div id="webform-palette" class="webform-palette">
                        <?php
                        $standard_fields = array(
                            'name' => __('Name', 'mahfuzar-form-builder'),
                            'text' => __('Text', 'mahfuzar-form-builder'),
                            'email' => __('Email', 'mahfuzar-form-builder'),
                            'textarea' => __('Long text', 'mahfuzar-form-builder'),
                            'select' => __('Dropdown', 'mahfuzar-form-builder'),
                            'radio' => __('Radio', 'mahfuzar-form-builder'),
                            'checkbox' => __('Checkbox', 'mahfuzar-form-builder'),
                            'number' => __('Number', 'mahfuzar-form-builder'),
                            'date' => __('Date', 'mahfuzar-form-builder'),
                            'time' => __('Time', 'mahfuzar-form-builder'),
                            'phone' => __('Phone', 'mahfuzar-form-builder'),
                            'url' => __('Website', 'mahfuzar-form-builder'),
                            'file' => __('File upload', 'mahfuzar-form-builder'),
                            'consent' => __('Consent', 'mahfuzar-form-builder'),
                            'poll' => __('Poll', 'mahfuzar-form-builder'),
                            'quiz' => __('Quiz question', 'mahfuzar-form-builder'),
                            'rating' => __('Rating', 'mahfuzar-form-builder'),
                            'slider' => __('Slider', 'mahfuzar-form-builder'),
                            'hidden' => __('Hidden field', 'mahfuzar-form-builder'),
                            'html' => __('HTML content', 'mahfuzar-form-builder'),
                            'captcha' => __('Google CAPTCHA', 'mahfuzar-form-builder'),
                            'page_break' => __('Page break', 'mahfuzar-form-builder'),
                            'heading' => __('Heading', 'mahfuzar-form-builder'),
                        );
                        $fields = apply_filters('webform_field_palette', $standard_fields);
                        $field_icons = array('name' => 'dashicons-admin-users', 'text' => 'dashicons-editor-textcolor', 'email' => 'dashicons-email', 'textarea' => 'dashicons-editor-alignleft', 'select' => 'dashicons-arrow-down-alt2', 'radio' => 'dashicons-marker', 'checkbox' => 'dashicons-yes', 'number' => 'dashicons-editor-ol', 'date' => 'dashicons-calendar', 'time' => 'dashicons-clock', 'phone' => 'dashicons-phone', 'url' => 'dashicons-admin-links', 'file' => 'dashicons-upload', 'consent' => 'dashicons-privacy', 'poll' => 'dashicons-chart-bar', 'quiz' => 'dashicons-welcome-learn-more', 'rating' => 'dashicons-star-filled', 'slider' => 'dashicons-image-flip-horizontal', 'hidden' => 'dashicons-hidden', 'html' => 'dashicons-editor-code', 'captcha' => 'dashicons-shield', 'page_break' => 'dashicons-controls-forward', 'heading' => 'dashicons-heading', 'calculation' => 'dashicons-editor-table', 'field_group' => 'dashicons-grid-view', 'signature' => 'dashicons-edit', 'rich_text' => 'dashicons-editor-paste-word', 'address' => 'dashicons-location-alt', 'repeater' => 'dashicons-list-view', 'appointment' => 'dashicons-calendar-alt', 'nps' => 'dashicons-chart-line', 'currency' => 'dashicons-money-alt', 'product' => 'dashicons-cart', 'price' => 'dashicons-tag');
                        foreach (array_intersect_key($fields, $standard_fields) as $type => $label) {
                            printf('<button type="button" class="webform-palette-item" data-type="%s"><span class="dashicons %s"></span><span>%s</span></button>', esc_attr($type), esc_attr($field_icons[$type] ?? 'dashicons-plus-alt2'), esc_html($label));
                        }
                        ?>
                    </div>
                    <?php $pro_palette_fields = array_diff_key($fields, $standard_fields); if ($pro_palette_fields) : ?>
                        <h3 class="webform-pro-fields-title"><?php esc_html_e('PRO FIELDS', 'mahfuzar-form-builder'); ?></h3>
                        <div class="webform-palette webform-pro-palette"><?php foreach ($pro_palette_fields as $type => $label) : ?><button type="button" class="webform-palette-item webform-palette-item-pro" data-type="<?php echo esc_attr($type); ?>"><span class="dashicons <?php echo esc_attr($field_icons[$type] ?? 'dashicons-plus-alt2'); ?>"></span><span><?php echo esc_html(str_replace(' (Pro)', '', $label)); ?></span></button><?php endforeach; ?></div>
                    <?php endif; ?>
                    <?php if (!$this->is_pro_active()) : ?><div class="webform-picker-pro"><div><span class="webform-pro-badge"><?php esc_html_e('RECOMMENDED PRO', 'mahfuzar-form-builder'); ?></span><h3><?php esc_html_e('Build revenue and automated workflows', 'mahfuzar-form-builder'); ?></h3><p><?php esc_html_e('Upgrade for calculated totals, grouped layouts, signatures, PDF notifications, hosted payments, email marketing, automation, premium styles, and 20 business templates.', 'mahfuzar-form-builder'); ?></p></div><h4><?php esc_html_e('Pro fields', 'mahfuzar-form-builder'); ?></h4><div class="webform-pro-field-list"><div><span class="dashicons dashicons-editor-table"></span><?php esc_html_e('Calculations', 'mahfuzar-form-builder'); ?></div><div><span class="dashicons dashicons-grid-view"></span><?php esc_html_e('Field groups', 'mahfuzar-form-builder'); ?></div><div><span class="dashicons dashicons-edit"></span><?php esc_html_e('E-signatures', 'mahfuzar-form-builder'); ?></div><div><span class="dashicons dashicons-editor-paste-word"></span><?php esc_html_e('Rich text / contracts', 'mahfuzar-form-builder'); ?></div></div><h4><?php esc_html_e('Pro integrations', 'mahfuzar-form-builder'); ?></h4><div class="webform-pro-field-list webform-pro-integration-fields"><div><span class="dashicons dashicons-email"></span>Mailchimp</div><div><span class="dashicons dashicons-megaphone"></span>Brevo</div><div><span class="dashicons dashicons-randomize"></span>Zapier</div><div><span class="dashicons dashicons-money-alt"></span><?php esc_html_e('Stripe / PayPal', 'mahfuzar-form-builder'); ?></div></div><a class="button button-primary" href="<?php echo esc_url($this->upgrade_url('field-picker')); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('See everything in Pro — $19.99/year', 'mahfuzar-form-builder'); ?></a></div><?php endif; ?>
                    </div>
                    <?php if (!$this->is_pro_active()) : ?>
                        <div class="webform-picker-pro webform-pro-field-preview-only">
                            <h4><?php esc_html_e('More fields in Webform Pro', 'mahfuzar-form-builder'); ?></h4>
                            <div class="webform-pro-field-list">
                                <div><span class="dashicons dashicons-location-alt"></span><?php esc_html_e('Address', 'mahfuzar-form-builder'); ?></div>
                                <div><span class="dashicons dashicons-list-view"></span><?php esc_html_e('Repeater', 'mahfuzar-form-builder'); ?></div>
                                <div><span class="dashicons dashicons-calendar-alt"></span><?php esc_html_e('Appointment', 'mahfuzar-form-builder'); ?></div>
                                <div><span class="dashicons dashicons-chart-line"></span><?php esc_html_e('NPS score', 'mahfuzar-form-builder'); ?></div>
                                <div><span class="dashicons dashicons-money-alt"></span><?php esc_html_e('Currency', 'mahfuzar-form-builder'); ?></div>
                                <div><span class="dashicons dashicons-editor-paste-word"></span><?php esc_html_e('Rich text / contracts', 'mahfuzar-form-builder'); ?></div>
                                <div><span class="dashicons dashicons-lock"></span><?php esc_html_e('Advanced uploads', 'mahfuzar-form-builder'); ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </aside>
                <main class="webform-canvas-panel">
                    <div class="webform-stage-tabs"><div id="webform-stage-tabs"></div><div class="webform-canvas-tools"><div class="webform-device-switcher" aria-label="<?php esc_attr_e('Preview size', 'mahfuzar-form-builder'); ?>"><button type="button" class="is-active" data-device="desktop" title="<?php esc_attr_e('Desktop preview', 'mahfuzar-form-builder'); ?>"><span class="dashicons dashicons-desktop"></span></button><button type="button" data-device="tablet" title="<?php esc_attr_e('Tablet preview', 'mahfuzar-form-builder'); ?>"><span class="dashicons dashicons-tablet"></span></button><button type="button" data-device="mobile" title="<?php esc_attr_e('Mobile preview', 'mahfuzar-form-builder'); ?>"><span class="dashicons dashicons-smartphone"></span></button></div><button type="button" class="button button-primary webform-open-field-picker"><span class="dashicons dashicons-plus-alt2"></span><?php esc_html_e('Add field', 'mahfuzar-form-builder'); ?></button><button class="button" id="webform-add-stage">+ <?php esc_html_e('Add stage', 'mahfuzar-form-builder'); ?></button></div></div>
                    <div id="webform-canvas" class="webform-canvas"></div>
                </main>
                <aside class="webform-properties">
                    <div class="webform-property-tabs"><button type="button" class="is-active" data-panel="field"><?php esc_html_e('Field', 'mahfuzar-form-builder'); ?></button><button type="button" data-panel="confirmation"><?php esc_html_e('Confirmation', 'mahfuzar-form-builder'); ?></button><button type="button" data-panel="integrations"><?php esc_html_e('Integrations', 'mahfuzar-form-builder'); ?></button><?php do_action('webform_builder_property_tabs', $form_id, $settings); ?><button type="button" data-panel="access"><?php esc_html_e('Access', 'mahfuzar-form-builder'); ?></button><button type="button" data-panel="style"><?php esc_html_e('Style', 'mahfuzar-form-builder'); ?></button></div>
                    <div class="webform-property-panel is-active" data-panel="field"><h2><?php esc_html_e('Field settings', 'mahfuzar-form-builder'); ?></h2><div id="webform-field-settings"><p class="description"><?php esc_html_e('Select a field to edit its options.', 'mahfuzar-form-builder'); ?></p></div></div>
                    <div class="webform-property-panel" data-panel="confirmation"><h2><?php esc_html_e('Confirmation', 'mahfuzar-form-builder'); ?></h2>
                    <?php $confirmation_types = apply_filters('webform_confirmation_types', array('message' => __('Show confirmation message', 'mahfuzar-form-builder'), 'redirect' => __('Redirect to URL', 'mahfuzar-form-builder'))); ?>
                    <label><?php esc_html_e('After submission', 'mahfuzar-form-builder'); ?><select id="webform-confirmation-type"><?php foreach ($confirmation_types as $confirmation_key => $confirmation_label) : ?><option value="<?php echo esc_attr($confirmation_key); ?>" <?php selected($settings['confirmation_type'] ?? 'message', $confirmation_key); ?>><?php echo esc_html($confirmation_label); ?></option><?php endforeach; ?></select></label>
                    <div class="webform-confirmation-option" data-confirmation-option="message"><label><?php esc_html_e('Success message', 'mahfuzar-form-builder'); ?></label><?php if (has_action('webform_confirmation_message_editor')) : do_action('webform_confirmation_message_editor', $settings); else : ?><textarea id="webform-success-message" rows="3"><?php echo esc_textarea(isset($settings['success_message']) ? $settings['success_message'] : __('Thanks! Your response has been submitted.', 'mahfuzar-form-builder')); ?></textarea><div class="webform-pro-readonly-note">🔒 <?php esc_html_e('Rich-text confirmation messages are available in Pro.', 'mahfuzar-form-builder'); ?></div><?php endif; ?></div>
                    <label><?php esc_html_e('Admin notification email', 'mahfuzar-form-builder'); ?><input type="email" id="webform-notification-email" value="<?php echo esc_attr(isset($settings['notification_email']) ? $settings['notification_email'] : get_option('admin_email')); ?>"><small><?php esc_html_e('Receives the standard submission notice and optional admin PDF.', 'mahfuzar-form-builder'); ?></small></label>
                    <label><?php esc_html_e('Submit button text', 'mahfuzar-form-builder'); ?><input type="text" id="webform-submit-label" value="<?php echo esc_attr(isset($settings['submit_label']) ? $settings['submit_label'] : __('Submit', 'mahfuzar-form-builder')); ?>"></label>
                    <div class="webform-confirmation-option" data-confirmation-option="redirect"><label><?php esc_html_e('Redirect URL', 'mahfuzar-form-builder'); ?><input type="url" id="webform-redirect-url" value="<?php echo esc_attr($settings['redirect_url'] ?? ''); ?>"></label></div>
                    <?php do_action('webform_builder_confirmation_controls', $form_id, $settings); ?>
                    <?php if (!$this->is_pro_active()) : ?><div class="webform-pro-readonly-note"><strong><?php esc_html_e('More confirmation tools in Webform Pro', 'mahfuzar-form-builder'); ?></strong><ul><li><?php esc_html_e('Save & Continue with secure resume links', 'mahfuzar-form-builder'); ?></li><li><?php esc_html_e('Custom confirmation emails for visitors', 'mahfuzar-form-builder'); ?></li><li><?php esc_html_e('PDF submission attachments', 'mahfuzar-form-builder'); ?></li></ul></div><?php endif; ?>
                    </div><div class="webform-property-panel" data-panel="access"><h2><?php esc_html_e('Access and limits', 'mahfuzar-form-builder'); ?></h2>
                    <label class="webform-check"><input type="checkbox" id="webform-require-login" <?php checked(!empty($settings['require_login'])); ?>> <?php esc_html_e('Require visitors to log in', 'mahfuzar-form-builder'); ?></label>
                    <label><?php esc_html_e('Maximum total entries', 'mahfuzar-form-builder'); ?><input type="number" min="0" id="webform-submission-limit" value="<?php echo esc_attr(absint($settings['submission_limit'] ?? 0)); ?>"><small><?php esc_html_e('Use 0 for unlimited.', 'mahfuzar-form-builder'); ?></small></label>
                    <label><?php esc_html_e('Closed form message', 'mahfuzar-form-builder'); ?><textarea id="webform-closed-message" rows="3"><?php echo esc_textarea($settings['closed_message'] ?? __('This form is currently unavailable.', 'mahfuzar-form-builder')); ?></textarea></label>
                    <?php do_action('webform_builder_access_controls', $settings, $form_id); ?>
                    </div><div class="webform-property-panel" data-panel="style"><h2><?php esc_html_e('Appearance', 'mahfuzar-form-builder'); ?></h2>
                    <?php $free_presets = array('modern' => __('Modern', 'mahfuzar-form-builder'), 'minimal' => __('Minimal', 'mahfuzar-form-builder'), 'rounded' => __('Rounded', 'mahfuzar-form-builder')); $all_presets = apply_filters('webform_style_presets', $free_presets); $locked_presets = array('elegant' => __('Elegant', 'mahfuzar-form-builder'), 'glass' => __('Glass', 'mahfuzar-form-builder'), 'dark' => __('Dark', 'mahfuzar-form-builder'), 'corporate' => __('Corporate', 'mahfuzar-form-builder'), 'editorial' => __('Editorial', 'mahfuzar-form-builder'), 'pastel' => __('Soft Pastel', 'mahfuzar-form-builder'), 'contrast' => __('High Contrast', 'mahfuzar-form-builder'), 'compact' => __('Compact', 'mahfuzar-form-builder'), 'spacious' => __('Spacious', 'mahfuzar-form-builder'), 'neon' => __('Neon', 'mahfuzar-form-builder'), 'earthy' => __('Earthy', 'mahfuzar-form-builder'), 'luxury' => __('Luxury', 'mahfuzar-form-builder'), 'playful' => __('Playful', 'mahfuzar-form-builder')); ?>
                    <label><?php esc_html_e('Style preset', 'mahfuzar-form-builder'); ?><span class="webform-preset-picker"><select id="webform-style-preset"><optgroup label="<?php esc_attr_e('Free presets', 'mahfuzar-form-builder'); ?>"><?php foreach ($free_presets as $preset_key => $preset_label) : ?><option value="<?php echo esc_attr($preset_key); ?>" <?php selected($settings['style_preset'] ?? 'modern', $preset_key); ?>><?php echo esc_html($preset_label); ?></option><?php endforeach; ?></optgroup><?php if ($this->is_pro_active()) : ?><optgroup label="<?php esc_attr_e('Pro presets', 'mahfuzar-form-builder'); ?>"><?php foreach (array_diff_key($all_presets, $free_presets) as $preset_key => $preset_label) : ?><option value="<?php echo esc_attr($preset_key); ?>" <?php selected($settings['style_preset'] ?? '', $preset_key); ?>><?php echo esc_html($preset_label); ?></option><?php endforeach; ?></optgroup><?php else : ?><optgroup label="<?php esc_attr_e('Pro presets — upgrade to use', 'mahfuzar-form-builder'); ?>"><?php foreach ($locked_presets as $preset_label) : ?><option disabled>🔒 <?php echo esc_html($preset_label); ?></option><?php endforeach; ?></optgroup><?php endif; ?></select><button type="button" class="button webform-preview-preset" title="<?php esc_attr_e('Preview selected preset', 'mahfuzar-form-builder'); ?>" aria-label="<?php esc_attr_e('Preview selected preset', 'mahfuzar-form-builder'); ?>"><span class="dashicons dashicons-visibility"></span></button></span></label>
                    <label><?php esc_html_e('Accent color', 'mahfuzar-form-builder'); ?><input type="color" id="webform-accent-color" value="<?php echo esc_attr($settings['accent_color'] ?? '#6c4bd4'); ?>"></label>
                    <label><?php esc_html_e('Button text color', 'mahfuzar-form-builder'); ?><input type="color" id="webform-button-text-color" value="<?php echo esc_attr($settings['button_text_color'] ?? '#ffffff'); ?>"></label>
                    <?php if ($this->is_pro_active()) : do_action('webform_builder_pro_style_controls', $settings); else : ?><div class="webform-pro-style-lock"><span class="webform-pro-badge"><?php esc_html_e('PRO DESIGN CONTROLS', 'mahfuzar-form-builder'); ?></span><p><?php esc_html_e('Unlock complete form styling without editing your theme.', 'mahfuzar-form-builder'); ?></p><div class="webform-locked-style-grid"><span>🔒 <?php esc_html_e('Reusable saved themes', 'mahfuzar-form-builder'); ?></span><span>🔒 <?php esc_html_e('Per-field styling', 'mahfuzar-form-builder'); ?></span><span>🔒 <?php esc_html_e('Font family', 'mahfuzar-form-builder'); ?></span><span>🔒 <?php esc_html_e('Font sizes', 'mahfuzar-form-builder'); ?></span><span>🔒 <?php esc_html_e('Form and field colors', 'mahfuzar-form-builder'); ?></span><span>🔒 <?php esc_html_e('Width and spacing', 'mahfuzar-form-builder'); ?></span><span>🔒 <?php esc_html_e('Borders and radius', 'mahfuzar-form-builder'); ?></span><span>🔒 <?php esc_html_e('Button styling', 'mahfuzar-form-builder'); ?></span><span>🔒 <?php esc_html_e('Custom CSS', 'mahfuzar-form-builder'); ?></span></div><a class="button button-primary" href="<?php echo esc_url($this->upgrade_url('style-controls')); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Unlock Pro styling', 'mahfuzar-form-builder'); ?></a></div><?php endif; ?>
                    </div>
                    <div class="webform-preset-preview-modal" id="webform-preset-preview-modal" hidden><button type="button" class="webform-preset-preview-backdrop" aria-label="<?php esc_attr_e('Close preview', 'mahfuzar-form-builder'); ?>"></button><div class="webform-preset-preview-dialog" role="dialog" aria-modal="true" aria-labelledby="webform-preset-preview-title"><div class="webform-preset-preview-head"><div><small><?php esc_html_e('STYLE PREVIEW', 'mahfuzar-form-builder'); ?></small><h2 id="webform-preset-preview-title"></h2></div><button type="button" class="button webform-preset-preview-close" aria-label="<?php esc_attr_e('Close preview', 'mahfuzar-form-builder'); ?>">×</button></div><div class="webform-public webform-preset-preview-form"><div class="webform-progress"><div class="webform-progress-bar" style="width:52%"></div></div><ol class="webform-steps"><li class="is-active"><?php esc_html_e('Your details', 'mahfuzar-form-builder'); ?></li><li><?php esc_html_e('Finish', 'mahfuzar-form-builder'); ?></li></ol><div class="webform-stage is-active"><h2><?php esc_html_e('Tell us about yourself', 'mahfuzar-form-builder'); ?></h2><div class="webform-field"><label><?php esc_html_e('Full name', 'mahfuzar-form-builder'); ?> <span>*</span></label><input type="text" value="<?php esc_attr_e('Jane Smith', 'mahfuzar-form-builder'); ?>" readonly></div><fieldset class="webform-field"><legend><?php esc_html_e('Preferred service', 'mahfuzar-form-builder'); ?></legend><div class="webform-choices"><label><input type="radio" checked disabled> <?php esc_html_e('Consultation', 'mahfuzar-form-builder'); ?></label><label><input type="radio" disabled> <?php esc_html_e('Project estimate', 'mahfuzar-form-builder'); ?></label></div></fieldset><div class="webform-field"><label><?php esc_html_e('Message', 'mahfuzar-form-builder'); ?></label><textarea readonly><?php esc_html_e('This is how your form text and fields will look.', 'mahfuzar-form-builder'); ?></textarea></div><div class="webform-actions"><button type="button"><?php esc_html_e('Continue', 'mahfuzar-form-builder'); ?></button></div></div></div></div></div>
                    <div class="webform-property-panel" data-panel="integrations"><h2><?php esc_html_e('Integrations', 'mahfuzar-form-builder'); ?></h2><?php if ($this->is_pro_active()) : do_action('webform_builder_integrations_panel', $form_id); else : ?><p class="description"><?php esc_html_e('Connect form submissions to your business tools with Webform Pro.', 'mahfuzar-form-builder'); ?></p><div class="webform-integration-list"><div><span class="dashicons dashicons-email"></span><strong>Mailchimp</strong><small><?php esc_html_e('Email audiences', 'mahfuzar-form-builder'); ?></small></div><div><span class="dashicons dashicons-megaphone"></span><strong>Brevo</strong><small><?php esc_html_e('Marketing automation', 'mahfuzar-form-builder'); ?></small></div><div><span class="dashicons dashicons-randomize"></span><strong>Zapier</strong><small><?php esc_html_e('Thousands of apps', 'mahfuzar-form-builder'); ?></small></div><div><span class="dashicons dashicons-money-alt"></span><strong>Stripe / PayPal</strong><small><?php esc_html_e('Hosted payments', 'mahfuzar-form-builder'); ?></small></div></div><a class="button button-primary" href="<?php echo esc_url($this->upgrade_url('integrations-tab')); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Unlock integrations', 'mahfuzar-form-builder'); ?></a><?php endif; ?></div>
                    <?php do_action('webform_builder_property_panels', $form_id, $settings); ?>
                </aside>
            </div>
            <?php if (!$form_id && !$template_key) : ?><div class="webform-template-modal" id="webform-template-modal" role="dialog" aria-modal="true" aria-labelledby="webform-template-title"><div class="webform-template-dialog"><button type="button" class="webform-template-close" aria-label="<?php esc_attr_e('Close', 'mahfuzar-form-builder'); ?>">×</button><h2 id="webform-template-title"><?php esc_html_e('Choose a starting template', 'mahfuzar-form-builder'); ?></h2><p><?php esc_html_e('Select a template or start from a blank form.', 'mahfuzar-form-builder'); ?></p><div class="webform-template-modal-grid"><a class="webform-template-choice" href="#"><strong><?php esc_html_e('Blank Form', 'mahfuzar-form-builder'); ?></strong><span><?php esc_html_e('Build from scratch', 'mahfuzar-form-builder'); ?></span></a><?php foreach ($this->free_templates() as $key => $template) : ?><a class="webform-template-choice" href="<?php echo esc_url(admin_url('admin.php?page=webform-builder&template=' . $key)); ?>"><strong><?php echo esc_html($template['name']); ?></strong><span><?php echo esc_html($template['description']); ?></span></a><?php endforeach; ?></div></div></div><?php endif; ?>
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
        <div class="wrap webform-wrap"><div class="webform-page-head"><div><h1><?php esc_html_e('Entries', 'mahfuzar-form-builder'); ?></h1><p><?php esc_html_e('Review, filter, export, or remove submissions.', 'mahfuzar-form-builder'); ?></p></div>
        <a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=webform_export_entries&form_id=' . $form_filter), 'webform_export_entries')); ?>"><?php esc_html_e('Export CSV', 'mahfuzar-form-builder'); ?></a></div>
        <form method="get" class="webform-entry-filter"><input type="hidden" name="page" value="webform-entries"><select name="form_id"><option value="0"><?php esc_html_e('All forms', 'mahfuzar-form-builder'); ?></option><?php foreach ($forms as $form) : ?><option value="<?php echo esc_attr($form->ID); ?>" <?php selected($form_filter, $form->ID); ?>><?php echo esc_html($form->post_title); ?></option><?php endforeach; ?></select><button class="button"><?php esc_html_e('Filter', 'mahfuzar-form-builder'); ?></button></form>
        <div class="webform-card"><table class="widefat striped"><thead><tr><th><?php esc_html_e('Form', 'mahfuzar-form-builder'); ?></th><th><?php esc_html_e('Status', 'mahfuzar-form-builder'); ?></th><th><?php esc_html_e('Submitted data', 'mahfuzar-form-builder'); ?></th><th><?php esc_html_e('Date', 'mahfuzar-form-builder'); ?></th><th></th></tr></thead><tbody>
        <?php if (!$entries) : ?><tr><td colspan="5"><?php esc_html_e('No entries yet.', 'mahfuzar-form-builder'); ?></td></tr><?php endif; ?>
        <?php foreach ($entries as $entry) : $data = get_post_meta($entry->ID, '_webform_data', true); $form_id = get_post_meta($entry->ID, '_webform_form_id', true); ?>
            <tr><td><?php echo esc_html(get_the_title($form_id)); ?></td><td><?php $entry_status = get_post_meta($entry->ID, '_webform_entry_status', true) ?: 'submitted'; ?><span class="webform-entry-status webform-entry-status-<?php echo esc_attr($entry_status); ?>"><?php echo esc_html($entry_status === 'draft' ? __('Draft', 'mahfuzar-form-builder') : __('Submitted', 'mahfuzar-form-builder')); ?></span></td><td><?php foreach ((array) $data as $key => $item) : $item = is_array($item) && isset($item['label']) ? $item : array('label' => $key, 'value' => $item); ?><div><strong><?php echo esc_html($item['label']); ?>:</strong> <?php echo esc_html(is_array($item['value']) ? implode(', ', $item['value']) : $item['value']); ?></div><?php endforeach; ?></td><td><?php echo esc_html(get_the_date('', $entry)); ?></td><td><a class="button-link-delete" onclick="return confirm('<?php echo esc_js(__('Permanently delete this entry?', 'mahfuzar-form-builder')); ?>')" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=webform_delete_entry&entry_id=' . $entry->ID), 'webform_delete_entry_' . $entry->ID)); ?>"><?php esc_html_e('Delete', 'mahfuzar-form-builder'); ?></a></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
        <?php echo wp_kses_post((string) paginate_links(array('base' => add_query_arg('paged', '%#%'), 'format' => '', 'current' => $paged, 'total' => $query->max_num_pages, 'type' => 'list'))); ?></div>
        <?php
    }

    public function save_form() {
        check_ajax_referer('webform_admin', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'mahfuzar-form-builder')), 403);
        }
        $form_id = isset($_POST['form_id']) ? absint($_POST['form_id']) : 0;
        if ($form_id && (get_post_type($form_id) !== 'webform_form' || !current_user_can('edit_post', $form_id))) {
            wp_send_json_error(array('message' => __('Form not found or permission denied.', 'mahfuzar-form-builder')), 403);
        }
        $schema_json = isset($_POST['schema']) ? wp_unslash($_POST['schema']) : '[]';
        $settings_json = isset($_POST['settings']) ? wp_unslash($_POST['settings']) : '{}';
        $decoded_schema = json_decode($schema_json, true);
        $decoded_settings = json_decode($settings_json, true);
        if (!is_array($decoded_schema) || !is_array($decoded_settings)) {
            wp_send_json_error(array('message' => __('Invalid form data.', 'mahfuzar-form-builder')), 400);
        }
        $schema = $this->sanitize_schema($decoded_schema);
        $settings = map_deep($decoded_settings, array($this, 'sanitize_decoded_setting'));
        if (count($schema) > 20) {
            wp_send_json_error(array('message' => __('A form may contain up to 20 stages.', 'mahfuzar-form-builder')), 400);
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
        update_post_meta($form_id, '_webform_schema', $schema);
        $confirmation_types = array_keys(apply_filters('webform_confirmation_types', array('message' => 'Message', 'redirect' => 'Redirect')));
        $clean_settings = array(
            'success_message' => wp_kses_post($settings['success_message'] ?? ''),
            'confirmation_type' => in_array($settings['confirmation_type'] ?? 'message', $confirmation_types, true) ? $settings['confirmation_type'] : 'message',
            'notification_email' => sanitize_email($settings['notification_email'] ?? ''),
            'submit_label' => sanitize_text_field($settings['submit_label'] ?? __('Submit', 'mahfuzar-form-builder')),
            'redirect_url' => esc_url_raw($settings['redirect_url'] ?? ''),
            'require_login' => !empty($settings['require_login']),
            'submission_limit' => absint($settings['submission_limit'] ?? 0),
            'closed_message' => sanitize_textarea_field($settings['closed_message'] ?? __('This form is currently unavailable.', 'mahfuzar-form-builder')),
            'style_preset' => array_key_exists($settings['style_preset'] ?? '', apply_filters('webform_style_presets', array('modern' => 'Modern', 'minimal' => 'Minimal', 'rounded' => 'Rounded'))) ? $settings['style_preset'] : 'modern',
            'accent_color' => sanitize_hex_color($settings['accent_color'] ?? '') ?: '#6c4bd4',
            'button_text_color' => sanitize_hex_color($settings['button_text_color'] ?? '') ?: '#ffffff',
        );
        update_post_meta($form_id, '_webform_settings', apply_filters('webform_sanitize_form_settings', $clean_settings, $settings, $form_id));
        wp_send_json_success(array('id' => $form_id, 'message' => __('Saved', 'mahfuzar-form-builder'), 'shortcode' => '[webform id="' . $form_id . '"]'));
    }

    public function sanitize_decoded_setting($value) {
        return is_string($value) ? wp_kses_post($value) : $value;
    }

    private function sanitize_schema($schema) {
        $clean = array();
        $allowed_types = apply_filters('webform_allowed_field_types', array('name', 'text', 'email', 'textarea', 'select', 'radio', 'checkbox', 'number', 'date', 'time', 'phone', 'url', 'file', 'consent', 'poll', 'quiz', 'rating', 'slider', 'hidden', 'html', 'captcha', 'heading'));
        $allowed_operators = array('equals', 'not_equals', 'contains', 'starts_with', 'ends_with', 'greater_than', 'less_than', 'not_empty', 'empty');
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
                $date_rule = in_array($field['date_rule'] ?? 'any', array('any', 'future', 'past', 'custom'), true) ? $field['date_rule'] : 'any';
                $date_min = sanitize_text_field($field['date_min'] ?? '');
                $date_max = sanitize_text_field($field['date_max'] ?? '');
                $date_min_object = DateTime::createFromFormat('!Y-m-d', $date_min);
                $date_max_object = DateTime::createFromFormat('!Y-m-d', $date_max);
                $date_min = $date_min_object && $date_min_object->format('Y-m-d') === $date_min ? $date_min : '';
                $date_max = $date_max_object && $date_max_object->format('Y-m-d') === $date_max ? $date_max : '';
                if ($date_min && $date_max && $date_min > $date_max) {
                    $date_swap = $date_min;
                    $date_min = $date_max;
                    $date_max = $date_swap;
                }
                $clean_field = array(
                    'id' => $field_id,
                    'type' => $type,
                    'label' => substr(sanitize_text_field($field['label'] ?? ''), 0, 200),
                    'placeholder' => substr(sanitize_text_field($field['placeholder'] ?? ''), 0, 300),
                    'required' => !empty($field['required']),
                    'row_start' => !empty($field['row_start']),
                    'options' => array_slice(array_values(array_filter(array_map('sanitize_text_field', (array) ($field['options'] ?? array())))), 0, 100),
                    'allowed_extensions' => preg_replace('/[^a-z0-9,\s]/', '', strtolower($field['allowed_extensions'] ?? 'jpg,jpeg,png,pdf,doc,docx')),
                    'max_size' => min(20, max(1, absint($field['max_size'] ?? 5))),
                    'correct_answer' => sanitize_text_field($field['correct_answer'] ?? ''),
                    'points' => min(100, max(1, absint($field['points'] ?? 1))),
                    'default_value' => sanitize_text_field($field['default_value'] ?? ''),
                    'html' => wp_kses_post($field['html'] ?? ''),
                    'rows' => min(30, max(2, absint($field['rows'] ?? 5))),
                    'date_rule' => $date_rule,
                    'date_min' => $date_min,
                    'date_max' => $date_max,
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
                if (in_array($type, $pro_types, true)) {
                    $clean_field['formula'] = preg_replace('/[^a-zA-Z0-9_{}+\-*\/().\s]/', '', $field['formula'] ?? '');
                    $clean_field['decimal_places'] = min(6, absint($field['decimal_places'] ?? 2));
                    $clean_field['group_count'] = min(6, max(1, absint($field['group_count'] ?? 2)));
                    $clean_field['group_columns'] = min(4, max(1, absint($field['group_columns'] ?? 2)));
                    $clean_field['repeater_min'] = min(20, max(1, absint($field['repeater_min'] ?? 1)));
                    $clean_field['repeater_max'] = min(50, max(1, absint($field['repeater_max'] ?? 10)));
                    $clean_field['repeater_button'] = substr(sanitize_text_field($field['repeater_button'] ?? ''), 0, 80);
                    $clean_field['currency_symbol'] = substr(sanitize_text_field($field['currency_symbol'] ?? '$'), 0, 5);
                    $clean_field['price_amount'] = max(0, floatval($field['price_amount'] ?? 0));
                    $clean_field['currency_code'] = in_array(strtoupper($field['currency_code'] ?? 'USD'), array('USD', 'EUR', 'GBP', 'CAD', 'AUD', 'BDT'), true) ? strtoupper($field['currency_code']) : 'USD';
                    $clean_field['min_date'] = sanitize_text_field($field['min_date'] ?? '');
                    $clean_field['max_date'] = sanitize_text_field($field['max_date'] ?? '');
                    $clean_field['style'] = array(
                        'width' => in_array((string) ($field['style']['width'] ?? '100'), array('auto', '100', '75', '50', '33'), true) ? (string) $field['style']['width'] : '100',
                        'label_color' => sanitize_hex_color($field['style']['label_color'] ?? '') ?: '#1d2327',
                        'background_color' => sanitize_hex_color($field['style']['background_color'] ?? '') ?: '#ffffff',
                        'text_color' => sanitize_hex_color($field['style']['text_color'] ?? '') ?: '#1d2327',
                        'radius' => min(40, absint($field['style']['radius'] ?? 7)),
                        'css_class' => sanitize_html_class($field['style']['css_class'] ?? ''),
                        'customized' => !empty($field['style']['customized']),
                    );
                }
                $clean_stage['fields'][] = apply_filters('webform_sanitize_field', $clean_field, $field);
            }
            $clean[] = $clean_stage;
        }
        return $clean;
    }

    public function delete_form() {
        check_ajax_referer('webform_admin', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'mahfuzar-form-builder')), 403);
        }
        $form_id = isset($_POST['form_id']) ? absint($_POST['form_id']) : 0;
        if (!$form_id || get_post_type($form_id) !== 'webform_form') {
            wp_send_json_error(array('message' => __('Form not found.', 'mahfuzar-form-builder')), 404);
        }
        wp_trash_post($form_id);
        wp_send_json_success();
    }

    public function restore_form() {
        if (!current_user_can('manage_options')) wp_die(esc_html__('Permission denied.', 'mahfuzar-form-builder'));
        $form_id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;
        check_admin_referer('webform_restore_' . $form_id);
        if (!$form_id || get_post_type($form_id) !== 'webform_form' || get_post_status($form_id) !== 'trash') wp_die(esc_html__('Trashed form not found.', 'mahfuzar-form-builder'));
        wp_untrash_post($form_id);
        wp_safe_redirect(admin_url('admin.php?page=webform&form_status=trash'));
        exit;
    }

    public function permanently_delete_form() {
        if (!current_user_can('manage_options')) wp_die(esc_html__('Permission denied.', 'mahfuzar-form-builder'));
        $form_id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;
        check_admin_referer('webform_permanently_delete_' . $form_id);
        if (!$form_id || get_post_type($form_id) !== 'webform_form' || get_post_status($form_id) !== 'trash') wp_die(esc_html__('Trashed form not found.', 'mahfuzar-form-builder'));
        $entry_ids = get_posts(array('post_type' => 'webform_entry', 'post_status' => 'private', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_webform_form_id', 'meta_value' => $form_id));
        foreach ($entry_ids as $entry_id) wp_delete_post($entry_id, true);
        wp_delete_post($form_id, true);
        wp_safe_redirect(admin_url('admin.php?page=webform&form_status=trash'));
        exit;
    }

    public function duplicate_form() {
        if (!current_user_can('manage_options')) wp_die(esc_html__('Permission denied.', 'mahfuzar-form-builder'));
        $form_id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;
        check_admin_referer('webform_duplicate_' . $form_id);
        if (get_post_type($form_id) !== 'webform_form') wp_die(esc_html__('Form not found.', 'mahfuzar-form-builder'));
        $copy_id = wp_insert_post(array('post_type' => 'webform_form', 'post_status' => 'publish', 'post_title' => sprintf(__('%s (Copy)', 'mahfuzar-form-builder'), get_the_title($form_id))));
        update_post_meta($copy_id, '_webform_schema', get_post_meta($form_id, '_webform_schema', true));
        update_post_meta($copy_id, '_webform_settings', get_post_meta($form_id, '_webform_settings', true));
        wp_safe_redirect(admin_url('admin.php?page=webform-builder&form_id=' . $copy_id));
        exit;
    }

    public function delete_entry() {
        if (!current_user_can('manage_options')) wp_die(esc_html__('Permission denied.', 'mahfuzar-form-builder'));
        $entry_id = isset($_GET['entry_id']) ? absint($_GET['entry_id']) : 0;
        check_admin_referer('webform_delete_entry_' . $entry_id);
        if (get_post_type($entry_id) === 'webform_entry') wp_delete_post($entry_id, true);
        wp_safe_redirect(wp_get_referer() ?: admin_url('admin.php?page=webform-entries'));
        exit;
    }

    public function export_entries() {
        if (!current_user_can('manage_options')) wp_die(esc_html__('Permission denied.', 'mahfuzar-form-builder'));
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
        $stored_settings = (array) get_option('webform_global_settings', array());
        $default_recaptcha_mode = !empty($stored_settings['recaptcha_secret_key']) ? 'classic' : 'enterprise';
        $settings = wp_parse_args($stored_settings, array('recaptcha_enabled' => false, 'recaptcha_mode' => $default_recaptcha_mode, 'recaptcha_site_key' => '', 'recaptcha_secret_key' => '', 'recaptcha_project_id' => '', 'recaptcha_api_key' => '', 'recaptcha_action' => 'WEBFORM_SUBMIT'));
        ?>
        <div class="wrap webform-wrap"><div class="webform-page-head"><div><h1><?php esc_html_e('Webform Settings', 'mahfuzar-form-builder'); ?></h1><p><?php esc_html_e('Global security and service configuration.', 'mahfuzar-form-builder'); ?></p></div></div>
        <form class="webform-settings-card webform-recaptcha-settings" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post"><input type="hidden" name="action" value="webform_save_global_settings"><?php wp_nonce_field('webform_save_global_settings'); ?>
            <div class="webform-settings-card-head"><span class="dashicons dashicons-shield"></span><div><h2><?php esc_html_e('Google reCAPTCHA', 'mahfuzar-form-builder'); ?></h2><p><?php esc_html_e('Protect forms with a Google Cloud checkbox key or a compatible classic v2 key.', 'mahfuzar-form-builder'); ?></p></div></div>
            <label class="webform-settings-toggle"><input type="checkbox" name="recaptcha_enabled" value="1" <?php checked(!empty($settings['recaptcha_enabled'])); ?>><span><?php esc_html_e('Enable Google reCAPTCHA on CAPTCHA fields', 'mahfuzar-form-builder'); ?></span></label>
            <label><?php esc_html_e('Integration type', 'mahfuzar-form-builder'); ?><select name="recaptcha_mode" id="webform-recaptcha-mode"><option value="enterprise" <?php selected($settings['recaptcha_mode'], 'enterprise'); ?>><?php esc_html_e('Google Cloud reCAPTCHA (recommended)', 'mahfuzar-form-builder'); ?></option><option value="classic" <?php selected($settings['recaptcha_mode'], 'classic'); ?>><?php esc_html_e('Classic or migrated v2 compatibility', 'mahfuzar-form-builder'); ?></option></select></label>
            <label><?php esc_html_e('Site key', 'mahfuzar-form-builder'); ?><input name="recaptcha_site_key" value="<?php echo esc_attr($settings['recaptcha_site_key']); ?>" autocomplete="off"><small><?php esc_html_e('This is the value used in the data-sitekey attribute Google provides.', 'mahfuzar-form-builder'); ?></small></label>
            <div class="webform-recaptcha-panel" data-mode="enterprise"><label><?php esc_html_e('Google Cloud project ID', 'mahfuzar-form-builder'); ?><input name="recaptcha_project_id" value="<?php echo esc_attr($settings['recaptcha_project_id']); ?>" autocomplete="off"></label><label><?php esc_html_e('Google Cloud API key', 'mahfuzar-form-builder'); ?><input type="password" name="recaptcha_api_key" value="<?php echo esc_attr($settings['recaptcha_api_key']); ?>" autocomplete="new-password"><small><?php esc_html_e('Use a restricted server API key with the reCAPTCHA Enterprise API enabled.', 'mahfuzar-form-builder'); ?></small></label><label><?php esc_html_e('Expected action', 'mahfuzar-form-builder'); ?><input name="recaptcha_action" value="<?php echo esc_attr($settings['recaptcha_action']); ?>" pattern="[A-Za-z0-9_/-]+"><small><?php esc_html_e('The frontend and backend must use the same action.', 'mahfuzar-form-builder'); ?></small></label></div>
            <div class="webform-recaptcha-panel" data-mode="classic"><label><?php esc_html_e('Secret key', 'mahfuzar-form-builder'); ?><input type="password" name="recaptcha_secret_key" value="<?php echo esc_attr($settings['recaptcha_secret_key']); ?>" autocomplete="new-password"></label><p class="description"><?php esc_html_e('Migrated classic keys can continue using SiteVerify. New Google Cloud keys should use the recommended mode above.', 'mahfuzar-form-builder'); ?></p></div>
            <button class="button button-primary"><?php esc_html_e('Save settings', 'mahfuzar-form-builder'); ?></button>
        </form></div>
        <?php
    }

    public function save_global_settings() {
        if (!current_user_can('manage_options')) wp_die(esc_html__('Permission denied.', 'mahfuzar-form-builder'));
        check_admin_referer('webform_save_global_settings');
        update_option('webform_global_settings', array(
            'recaptcha_enabled' => !empty($_POST['recaptcha_enabled']),
            'recaptcha_mode' => in_array($_POST['recaptcha_mode'] ?? '', array('enterprise', 'classic'), true) ? sanitize_key(wp_unslash($_POST['recaptcha_mode'])) : 'enterprise',
            'recaptcha_site_key' => sanitize_text_field(wp_unslash($_POST['recaptcha_site_key'] ?? '')),
            'recaptcha_secret_key' => sanitize_text_field(wp_unslash($_POST['recaptcha_secret_key'] ?? '')),
            'recaptcha_project_id' => sanitize_text_field(wp_unslash($_POST['recaptcha_project_id'] ?? '')),
            'recaptcha_api_key' => sanitize_text_field(wp_unslash($_POST['recaptcha_api_key'] ?? '')),
            'recaptcha_action' => preg_replace('/[^A-Za-z0-9_\\/-]/', '', wp_unslash($_POST['recaptcha_action'] ?? 'WEBFORM_SUBMIT')),
        ), false);
        wp_safe_redirect(admin_url('admin.php?page=webform-settings'));
        exit;
    }

    public function import_page() {
        if (!current_user_can('manage_options')) return;
        $forms = get_posts(array('post_type' => 'webform_form', 'post_status' => array('publish', 'draft'), 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC'));
        ?>
        <div class="wrap webform-wrap"><div class="webform-page-head"><div><h1><?php esc_html_e('Import and Export Forms', 'mahfuzar-form-builder'); ?></h1><p><?php esc_html_e('Move forms between websites or migrate from another form builder.', 'mahfuzar-form-builder'); ?></p></div></div>
        <div class="webform-transfer-grid">
            <form class="webform-settings-card" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="webform_import"><?php wp_nonce_field('webform_import'); ?>
                <h2><?php esc_html_e('Import a form', 'mahfuzar-form-builder'); ?></h2>
                <label><?php esc_html_e('Source', 'mahfuzar-form-builder'); ?><select name="source"><option value="auto"><?php esc_html_e('Detect automatically', 'mahfuzar-form-builder'); ?></option><option value="webform">Webform</option><option value="wpforms">WPForms</option><option value="gravity">Gravity Forms</option><option value="fluent">Fluent Forms</option><option value="formidable">Formidable Forms</option><option value="forminator">Forminator</option><option value="cf7">Contact Form 7</option></select></label>
                <label><?php esc_html_e('Import file', 'mahfuzar-form-builder'); ?><input type="file" name="import_file" accept=".json,.csv,.xml,.txt,application/json,text/csv,application/xml,text/xml,text/plain"></label>
                <p class="description"><?php esc_html_e('Supported formats: JSON, CSV, XML, and Contact Form 7 text markup. Maximum file size: 5 MB.', 'mahfuzar-form-builder'); ?></p>
                <label><?php esc_html_e('Or paste exported content', 'mahfuzar-form-builder'); ?><textarea name="import_content" rows="10"></textarea></label>
                <button class="button button-primary"><?php esc_html_e('Import and edit', 'mahfuzar-form-builder'); ?></button>
            </form>
            <div><?php do_action('webform_import_export_tools', $forms); ?><?php if (!apply_filters('webform_can_export_forms', false)) : ?><div class="webform-settings-card webform-export-pro-card"><span class="webform-pro-badge"><?php esc_html_e('PRO', 'mahfuzar-form-builder'); ?></span><h2><?php esc_html_e('Export forms', 'mahfuzar-form-builder'); ?></h2><p><?php esc_html_e('Export complete form structures and settings as JSON, CSV, or XML for backups and migrations.', 'mahfuzar-form-builder'); ?></p><ul><li><?php esc_html_e('Portable fields, stages, and settings', 'mahfuzar-form-builder'); ?></li><li><?php esc_html_e('JSON, CSV, and XML downloads', 'mahfuzar-form-builder'); ?></li><li><?php esc_html_e('Import files on another Webform website', 'mahfuzar-form-builder'); ?></li></ul><a class="button button-primary" href="<?php echo esc_url($this->upgrade_url('form-export')); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Unlock form export', 'mahfuzar-form-builder'); ?></a></div><?php endif; ?></div>
        </div></div>
        <?php
    }

    public function import_form() {
        if (!current_user_can('manage_options')) wp_die(esc_html__('Permission denied.', 'mahfuzar-form-builder'));
        check_admin_referer('webform_import');
        $source = sanitize_key(wp_unslash($_POST['source'] ?? ''));
        $content = trim((string) wp_unslash($_POST['import_content'] ?? ''));
        $filename = '';
        if (!$content && !empty($_FILES['import_file']['tmp_name']) && absint($_FILES['import_file']['size']) <= 5 * MB_IN_BYTES) {
            $filename = sanitize_file_name(wp_unslash($_FILES['import_file']['name'] ?? ''));
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
            global $wp_filesystem;
            $content = $wp_filesystem->get_contents($_FILES['import_file']['tmp_name']);
        }
        if (!$content) wp_die(esc_html__('No import content was provided.', 'mahfuzar-form-builder'));
        if ($source === 'cf7') {
            $converted = $this->convert_cf7($content);
        } else {
            $format = $this->detect_import_format($content, $filename);
            if ($format === 'csv') {
                $converted = $this->convert_csv_form($content);
            } elseif ($format === 'xml') {
                $converted = $this->convert_xml_form($content, $source);
            } else {
                $decoded = json_decode($content, true);
                if (!is_array($decoded)) wp_die(esc_html__('The import file is not valid JSON.', 'mahfuzar-form-builder'));
                if ($source === 'forminator' || ($source === 'auto' && $this->looks_like_forminator($decoded))) {
                    $converted = $this->convert_forminator($decoded);
                } elseif ($source === 'formidable' || ($source === 'auto' && $this->looks_like_formidable($decoded))) {
                    $converted = $this->convert_formidable($decoded);
                } else {
                    $converted = $this->convert_json_form($decoded);
                }
            }
        }
        if (empty($converted['schema']) || !$this->import_has_fields($converted['schema'])) wp_die(esc_html__('No supported fields were found in the export. Confirm the correct source plugin is selected and export the form structure rather than its entries.', 'mahfuzar-form-builder'));
        $form_id = wp_insert_post(array('post_type' => 'webform_form', 'post_status' => 'publish', 'post_title' => sanitize_text_field($converted['name'] ?: __('Imported Form', 'mahfuzar-form-builder'))));
        update_post_meta($form_id, '_webform_schema', $this->sanitize_schema($converted['schema']));
        update_post_meta($form_id, '_webform_settings', $this->sanitize_import_settings($converted['settings'] ?? array()));
        wp_safe_redirect(admin_url('admin.php?page=webform-builder&form_id=' . $form_id));
        exit;
    }

    private function detect_import_format($content, $filename) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($extension, array('csv', 'xml', 'json'), true)) {
            return $extension;
        }
        $trimmed = ltrim($content);
        if (strpos($trimmed, '<') === 0) return 'xml';
        if (strpos($trimmed, '{') === 0 || strpos($trimmed, '[') === 0) return 'json';
        return 'csv';
    }

    private function convert_csv_form($content) {
        $lines = preg_split('/\r\n|\r|\n/', trim($content));
        $header = array_map('sanitize_key', str_getcsv((string) array_shift($lines)));
        $required_columns = array('stage_title', 'field_type', 'label');
        if (array_diff($required_columns, $header)) {
            wp_die(esc_html__('The CSV must include stage_title, field_type, and label columns.', 'mahfuzar-form-builder'));
        }
        $stages = array();
        $name = __('Imported Form', 'mahfuzar-form-builder');
        $settings = array();
        foreach ($lines as $line) {
            if (trim($line) === '') continue;
            $values = str_getcsv($line);
            $row = array_combine($header, array_pad($values, count($header), ''));
            if (!$row) continue;
            if (!empty($row['form_name'])) $name = sanitize_text_field($row['form_name']);
            if (!empty($row['settings_json']) && !$settings) {
                $decoded_settings = json_decode($row['settings_json'], true);
                if (is_array($decoded_settings)) $settings = $decoded_settings;
            }
            $stage_title = sanitize_text_field($row['stage_title']);
            $stage_key = sanitize_key($row['stage_id'] ?? $stage_title) ?: 'stage_' . (count($stages) + 1);
            if (!isset($stages[$stage_key])) $stages[$stage_key] = array('id' => $stage_key, 'title' => $stage_title ?: __('Imported Form', 'mahfuzar-form-builder'), 'fields' => array());
            $options = !empty($row['options']) ? preg_split('/\s*\|\s*/', $row['options']) : array();
            $field = array('id' => sanitize_key($row['field_id'] ?? '') ?: 'field_' . wp_generate_password(6, false, false), 'type' => $this->map_import_type($row['field_type']), 'label' => sanitize_text_field($row['label']), 'placeholder' => sanitize_text_field($row['placeholder'] ?? ''), 'required' => in_array(strtolower((string) ($row['required'] ?? '')), array('1', 'yes', 'true', 'required'), true), 'options' => array_map('sanitize_text_field', $options));
            if (!empty($row['field_json'])) {
                $decoded_field = json_decode($row['field_json'], true);
                if (is_array($decoded_field)) $field = array_merge($field, $decoded_field);
            }
            $stages[$stage_key]['fields'][] = $field;
        }
        return array('name' => $name, 'schema' => array_values($stages), 'settings' => $settings);
    }

    private function convert_xml_form($content, $source) {
        if (!function_exists('simplexml_load_string')) {
            wp_die(esc_html__('XML support is not available on this server.', 'mahfuzar-form-builder'));
        }
        $xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
        if ($xml === false) wp_die(esc_html__('The import file is not valid XML.', 'mahfuzar-form-builder'));
        $root_name = $xml->getName();
        if ($root_name === 'webform-export') {
            $name = sanitize_text_field((string) $xml->form->name);
            $settings = json_decode((string) $xml->form->settings, true);
            $stages = array();
            foreach ($xml->form->stages->stage as $stage_node) {
                $stage = array('id' => sanitize_key((string) $stage_node['id']), 'title' => sanitize_text_field((string) $stage_node['title']), 'fields' => array());
                foreach ($stage_node->field as $field_node) {
                    $field = json_decode((string) $field_node, true);
                    if (is_array($field)) $stage['fields'][] = $field;
                }
                $stages[] = $stage;
            }
            return array('name' => $name, 'schema' => $stages, 'settings' => is_array($settings) ? $settings : array());
        }
        if ($source === 'formidable' || stripos($root_name, 'formidable') !== false) {
            $formidable = $this->convert_formidable_xml($xml);
            if (!empty($formidable['schema'][0]['fields'])) return $formidable;
        }
        $decoded = json_decode(wp_json_encode($xml), true);
        return $source === 'forminator' ? $this->convert_forminator($decoded) : ($source === 'formidable' || stripos($root_name, 'formidable') !== false ? $this->convert_formidable($decoded) : $this->convert_json_form($decoded));
    }

    private function convert_formidable_xml($xml) {
        $form_nodes = $xml->xpath('//*[local-name()="form"]');
        $form_node = !empty($form_nodes) ? $form_nodes[0] : $xml;
        $name_nodes = $form_node->xpath('.//*[local-name()="name" or local-name()="title"]');
        $name = !empty($name_nodes) ? sanitize_text_field((string) $name_nodes[0]) : __('Imported Formidable Form', 'mahfuzar-form-builder');
        $field_nodes = $form_node->xpath('.//*[local-name()="field"]');
        if (empty($field_nodes)) $field_nodes = $xml->xpath('//*[local-name()="field"]');
        $fields = array();
        foreach ((array) $field_nodes as $field_node) {
            $field = json_decode(wp_json_encode($field_node), true);
            if (!is_array($field)) continue;
            foreach ($field_node->attributes() as $attribute => $value) $field[(string) $attribute] = (string) $value;
            $fields[] = $field;
        }
        return $this->build_imported_form($name, $fields, 'formidable');
    }

    private function convert_forminator($data) {
        $node = isset($data['data']) && is_array($data['data']) ? $data['data'] : $data;
        $name = sanitize_text_field($node['name'] ?? ($node['settings']['formName'] ?? ($node['settings']['form_name'] ?? __('Imported Forminator Form', 'mahfuzar-form-builder'))));
        $fields = $node['fields'] ?? array();
        if (!$fields && !empty($node['wrappers'])) $fields = $this->flatten_import_fields($node['wrappers']);
        return $this->build_imported_form($name, $fields, 'forminator');
    }

    private function convert_formidable($data) {
        $node = $this->find_form_node($data);
        if (!empty($data['forms'][0]) && is_array($data['forms'][0])) $node = $data['forms'][0];
        $name = sanitize_text_field($node['name'] ?? ($node['form_key'] ?? ($node['title'] ?? __('Imported Formidable Form', 'mahfuzar-form-builder'))));
        $fields = $node['fields'] ?? ($data['fields'] ?? array());
        return $this->build_imported_form($name, $this->flatten_import_fields($fields), 'formidable');
    }

    private function build_imported_form($name, $fields, $source) {
        $stages = array(array('id' => 'stage_imported', 'title' => $name, 'fields' => array()));
        foreach ((array) $fields as $key => $field) {
            if (!is_array($field)) continue;
            $field_options = $field['field_options'] ?? array();
            if (is_string($field_options)) {
                $decoded_options = json_decode(html_entity_decode(wp_unslash($field_options), ENT_QUOTES, get_bloginfo('charset')), true);
                $field_options = is_array($decoded_options) ? $decoded_options : maybe_unserialize($field_options);
            }
            if (!is_array($field_options)) $field_options = array();
            $raw_type = $this->import_scalar($field['type'] ?? ($field['field_type'] ?? ($field['element_type'] ?? 'text')));
            if (in_array(sanitize_key($raw_type), array('page-break', 'page_break', 'pagebreak', 'section'), true)) {
                $stages[] = array('id' => 'stage_' . (count($stages) + 1), 'title' => sanitize_text_field($this->import_scalar($field['field_label'] ?? ($field['name'] ?? sprintf(__('Stage %d', 'mahfuzar-form-builder'), count($stages) + 1)))), 'fields' => array());
                continue;
            }
            $choices = $field['options'] ?? ($field['choices'] ?? array());
            if (isset($field_options['options'])) $choices = $field_options['options'];
            $normalized_choices = $this->normalize_import_choices($choices);
            $stages[count($stages) - 1]['fields'][] = array(
                'id' => sanitize_key($this->import_scalar($field['element_id'] ?? ($field['field_key'] ?? ($field['id'] ?? $key)))) ?: 'field_' . wp_generate_password(6, false, false),
                'type' => $this->map_import_type($raw_type),
                'label' => sanitize_text_field($this->import_scalar($field['field_label'] ?? ($field['name'] ?? ($field['label'] ?? __('Imported Field', 'mahfuzar-form-builder'))))),
                'placeholder' => sanitize_text_field($this->import_scalar($field['placeholder'] ?? ($field_options['placeholder'] ?? ''))),
                'required' => !empty($field['required']) || !empty($field['mandatory']) || (($field_options['required_indicator'] ?? '') !== ''),
                'options' => array_filter($normalized_choices),
            );
        }
        return array('name' => $name, 'schema' => $stages, 'source' => $source);
    }

    private function normalize_import_choices($choices) {
        if (is_string($choices)) {
            $decoded = json_decode(html_entity_decode(wp_unslash($choices), ENT_QUOTES, get_bloginfo('charset')), true);
            if (is_array($decoded)) {
                $choices = $decoded;
            } else {
                $unserialized = maybe_unserialize($choices);
                if (is_array($unserialized)) $choices = $unserialized;
            }
        }
        $normalized = array();
        foreach ((array) $choices as $choice) {
            if (is_string($choice)) {
                $nested = json_decode(html_entity_decode(wp_unslash($choice), ENT_QUOTES, get_bloginfo('charset')), true);
                if (is_array($nested)) {
                    $normalized = array_merge($normalized, $this->normalize_import_choices($nested));
                    continue;
                }
                $value = $choice;
            } elseif (is_array($choice)) {
                $value = $choice['label'] ?? ($choice['name'] ?? ($choice['value'] ?? ($choice['option_value'] ?? '')));
                if (is_array($value)) {
                    $normalized = array_merge($normalized, $this->normalize_import_choices($value));
                    continue;
                }
            } else {
                $value = '';
            }
            $value = trim(sanitize_text_field($this->import_scalar($value)));
            if ($value !== '') $normalized[] = $value;
        }
        return array_values(array_unique($normalized));
    }

    private function import_has_fields($schema) {
        foreach ((array) $schema as $stage) {
            if (!empty($stage['fields']) && is_array($stage['fields'])) return true;
        }
        return false;
    }

    private function flatten_import_fields($nodes) {
        $fields = array();
        foreach ((array) $nodes as $node) {
            if (!is_array($node)) continue;
            if (isset($node['type']) || isset($node['field_type']) || isset($node['element_type'])) $fields[] = $node;
            foreach (array('fields', 'wrappers', 'columns', 'elements') as $child_key) {
                if (!empty($node[$child_key]) && is_array($node[$child_key])) $fields = array_merge($fields, $this->flatten_import_fields($node[$child_key]));
            }
        }
        return $fields;
    }

    private function import_scalar($value) {
        if (is_scalar($value)) return (string) $value;
        if (is_array($value)) {
            $first = reset($value);
            return is_scalar($first) ? (string) $first : '';
        }
        return '';
    }

    private function looks_like_forminator($data) {
        return isset($data['data']['wrappers']) || isset($data['data']['settings']['formName']) || (($data['type'] ?? '') === 'form');
    }

    private function looks_like_formidable($data) {
        return isset($data['forms']) || isset($data['form_key']) || isset($data['frm_form']);
    }

    private function map_import_type($type) {
        $type = sanitize_key($type);
        $map = array('name' => 'name', 'first_name' => 'text', 'last_name' => 'text', 'input' => 'text', 'phone' => 'phone', 'tel' => 'phone', 'email' => 'email', 'textarea' => 'textarea', 'paragraph' => 'textarea', 'select' => 'select', 'dropdown' => 'select', 'radio' => 'radio', 'checkbox' => 'checkbox', 'number' => 'number', 'date' => 'date', 'time' => 'time', 'url' => 'url', 'website' => 'url', 'file' => 'file', 'upload' => 'file', 'html' => 'html', 'section' => 'heading', 'divider' => 'heading', 'heading' => 'heading', 'rating' => 'rating', 'star' => 'rating', 'scale' => 'slider', 'slider' => 'slider', 'hidden' => 'hidden', 'consent' => 'consent', 'captcha' => 'captcha', 'calculation' => 'calculation', 'currency' => 'currency', 'signature' => 'signature', 'address' => 'address');
        return $map[$type] ?? 'text';
    }

    private function sanitize_import_settings($settings) {
        $defaults = array('success_message' => __('Thanks! Your response has been submitted.', 'mahfuzar-form-builder'), 'notification_email' => get_option('admin_email'), 'submit_label' => __('Submit', 'mahfuzar-form-builder'), 'confirmation_type' => 'message', 'style_preset' => 'modern', 'accent_color' => '#6c4bd4', 'button_text_color' => '#ffffff');
        $settings = wp_parse_args((array) $settings, $defaults);
        $confirmation_types = array_keys(apply_filters('webform_confirmation_types', array('message' => 'Message', 'redirect' => 'Redirect')));
        $confirmation_type = in_array($settings['confirmation_type'], $confirmation_types, true) ? $settings['confirmation_type'] : 'message';
        $clean = array('success_message' => wp_kses_post($settings['success_message']), 'notification_email' => sanitize_email($settings['notification_email']), 'submit_label' => sanitize_text_field($settings['submit_label']), 'confirmation_type' => $confirmation_type, 'redirect_url' => esc_url_raw($settings['redirect_url'] ?? ''), 'require_login' => !empty($settings['require_login']), 'submission_limit' => absint($settings['submission_limit'] ?? 0), 'style_preset' => sanitize_key($settings['style_preset']), 'accent_color' => sanitize_hex_color($settings['accent_color']) ?: '#6c4bd4', 'button_text_color' => sanitize_hex_color($settings['button_text_color']) ?: '#ffffff');
        return apply_filters('webform_sanitize_form_settings', $clean, $settings, 0);
    }

    private function convert_json_form($data) {
        if (isset($data['webform_export_version'], $data['form'])) {
            $form = (array) $data['form'];
            return array('name' => sanitize_text_field($form['name'] ?? __('Imported Form', 'mahfuzar-form-builder')), 'schema' => (array) ($form['schema'] ?? array()), 'settings' => (array) ($form['settings'] ?? array()));
        }
        $node = $this->find_form_node($data);
        $name = sanitize_text_field($node['title'] ?? ($node['name'] ?? ($node['settings']['form_title'] ?? __('Imported Form', 'mahfuzar-form-builder'))));
        $source_fields = $node['fields'] ?? ($node['form_fields'] ?? array());
        if (is_string($source_fields)) $source_fields = json_decode($source_fields, true);
        $stages = array(array('id' => 'stage_imported', 'title' => __('Imported Form', 'mahfuzar-form-builder'), 'fields' => array()));
        foreach ((array) $source_fields as $key => $source) {
            if (!is_array($source)) continue;
            $type = sanitize_key($source['type'] ?? ($source['element'] ?? 'text'));
            if (in_array($type, array('page', 'pagebreak', 'step'), true)) {
                $stages[] = array('id' => 'stage_' . count($stages), 'title' => sanitize_text_field($source['label'] ?? sprintf(__('Stage %d', 'mahfuzar-form-builder'), count($stages) + 1)), 'fields' => array());
                continue;
            }
            $type = $this->map_import_type($type);
            $choices = $this->normalize_import_choices($source['choices'] ?? ($source['options'] ?? array()));
            $stages[count($stages) - 1]['fields'][] = array('id' => sanitize_key($source['id'] ?? $key) ?: 'field_' . wp_generate_password(6, false, false), 'type' => $type, 'label' => sanitize_text_field($source['label'] ?? ($source['adminLabel'] ?? __('Imported Field', 'mahfuzar-form-builder'))), 'placeholder' => sanitize_text_field($source['placeholder'] ?? ''), 'required' => !empty($source['required']) || !empty($source['isRequired']), 'options' => array_filter($choices));
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
        return array('name' => __('Imported Contact Form', 'mahfuzar-form-builder'), 'schema' => array(array('id' => 'stage_imported', 'title' => __('Contact Form', 'mahfuzar-form-builder'), 'fields' => $fields)));
    }

    public function templates_page() {
        if (!current_user_can('manage_options')) return;
        ?>
        <div class="wrap webform-wrap">
            <div class="webform-page-head"><div><h1><?php esc_html_e('Form Templates', 'mahfuzar-form-builder'); ?></h1><p><?php esc_html_e('Start with a complete form, then customize every field and stage.', 'mahfuzar-form-builder'); ?></p></div></div>
            <div class="webform-template-grid">
                <div class="webform-template-card"><span class="dashicons dashicons-plus-alt2"></span><h2><?php esc_html_e('Blank Form', 'mahfuzar-form-builder'); ?></h2><p><?php esc_html_e('Start with an empty stage.', 'mahfuzar-form-builder'); ?></p><a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=webform-builder')); ?>"><?php esc_html_e('Create', 'mahfuzar-form-builder'); ?></a></div>
                <?php foreach ($this->free_templates() as $key => $template) : ?>
                    <div class="webform-template-card"><?php if (!empty($template['pro'])) : ?><span class="webform-pro-badge"><?php esc_html_e('PRO', 'mahfuzar-form-builder'); ?></span><?php else : ?><span class="dashicons <?php echo esc_attr($template['icon']); ?>"></span><?php endif; ?><h2><?php echo esc_html($template['name']); ?></h2><p><?php echo esc_html($template['description']); ?></p><a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=webform-builder&template=' . $key)); ?>"><?php esc_html_e('Use template', 'mahfuzar-form-builder'); ?></a></div>
                <?php endforeach; ?>
                <?php if (!$this->is_pro_active()) : ?><div class="webform-template-card webform-template-pro"><span class="webform-pro-badge"><?php esc_html_e('PRO', 'mahfuzar-form-builder'); ?></span><h2><?php esc_html_e('20 Premium Templates', 'mahfuzar-form-builder'); ?></h2><p><?php esc_html_e('Payments, lead generation, bookings, applications, orders, onboarding, and advanced business workflows.', 'mahfuzar-form-builder'); ?></p><a class="button" href="<?php echo esc_url($this->upgrade_url('templates')); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Explore Pro', 'mahfuzar-form-builder'); ?></a></div><?php endif; ?>
            </div>
        </div>
        <?php
    }

    private function free_templates() {
        return apply_filters('webform_templates', array(
            'contact' => array('name' => __('Contact Form', 'mahfuzar-form-builder'), 'description' => __('Name, email, phone, and message.', 'mahfuzar-form-builder'), 'icon' => 'dashicons-email-alt', 'schema' => array($this->template_stage(__('Contact Us', 'mahfuzar-form-builder'), array($this->template_field('name', 'text', __('Name', 'mahfuzar-form-builder'), true), $this->template_field('email', 'email', __('Email', 'mahfuzar-form-builder'), true), $this->template_field('phone', 'phone', __('Phone', 'mahfuzar-form-builder')), $this->template_field('message', 'textarea', __('Message', 'mahfuzar-form-builder'), true))))),
            'feedback' => array('name' => __('Customer Feedback', 'mahfuzar-form-builder'), 'description' => __('Satisfaction poll and written feedback.', 'mahfuzar-form-builder'), 'icon' => 'dashicons-format-chat', 'schema' => array($this->template_stage(__('Your Feedback', 'mahfuzar-form-builder'), array($this->template_field('satisfaction', 'poll', __('How satisfied are you?', 'mahfuzar-form-builder'), true, array(__('Very satisfied', 'mahfuzar-form-builder'), __('Satisfied', 'mahfuzar-form-builder'), __('Neutral', 'mahfuzar-form-builder'), __('Dissatisfied', 'mahfuzar-form-builder'))), $this->template_field('feedback', 'textarea', __('What can we improve?', 'mahfuzar-form-builder')))))),
            'job-application' => array('name' => __('Job Application', 'mahfuzar-form-builder'), 'description' => __('Applicant details, role, résumé, and consent.', 'mahfuzar-form-builder'), 'icon' => 'dashicons-businessperson', 'schema' => array($this->template_stage(__('Applicant Details', 'mahfuzar-form-builder'), array($this->template_field('name', 'text', __('Full name', 'mahfuzar-form-builder'), true), $this->template_field('email', 'email', __('Email', 'mahfuzar-form-builder'), true), $this->template_field('role', 'select', __('Position', 'mahfuzar-form-builder'), true, array(__('Developer', 'mahfuzar-form-builder'), __('Designer', 'mahfuzar-form-builder'), __('Marketing', 'mahfuzar-form-builder'))))), $this->template_stage(__('Application', 'mahfuzar-form-builder'), array($this->template_field('resume', 'file', __('Résumé', 'mahfuzar-form-builder'), true), $this->template_field('cover', 'textarea', __('Cover letter', 'mahfuzar-form-builder')), $this->template_field('consent', 'consent', __('I consent to the processing of my application.', 'mahfuzar-form-builder'), true))))),
            'event-registration' => array('name' => __('Event Registration', 'mahfuzar-form-builder'), 'description' => __('Attendee information and session choice.', 'mahfuzar-form-builder'), 'icon' => 'dashicons-calendar-alt', 'schema' => array($this->template_stage(__('Registration', 'mahfuzar-form-builder'), array($this->template_field('name', 'text', __('Attendee name', 'mahfuzar-form-builder'), true), $this->template_field('email', 'email', __('Email', 'mahfuzar-form-builder'), true), $this->template_field('session', 'radio', __('Preferred session', 'mahfuzar-form-builder'), true, array(__('Morning', 'mahfuzar-form-builder'), __('Afternoon', 'mahfuzar-form-builder'))), $this->template_field('notes', 'textarea', __('Accessibility or dietary needs', 'mahfuzar-form-builder')))))),
            'quote-request' => array('name' => __('Request a Quote', 'mahfuzar-form-builder'), 'description' => __('Project type, budget, timing, and requirements.', 'mahfuzar-form-builder'), 'icon' => 'dashicons-money-alt', 'schema' => array($this->template_stage(__('Project', 'mahfuzar-form-builder'), array($this->template_field('service', 'select', __('Service needed', 'mahfuzar-form-builder'), true, array(__('Website', 'mahfuzar-form-builder'), __('Ecommerce', 'mahfuzar-form-builder'), __('Marketing', 'mahfuzar-form-builder'), __('Other', 'mahfuzar-form-builder'))), $this->template_field('budget', 'select', __('Budget range', 'mahfuzar-form-builder'), true, array(__('Under $1,000', 'mahfuzar-form-builder'), __('$1,000–$5,000', 'mahfuzar-form-builder'), __('$5,000+', 'mahfuzar-form-builder'))), $this->template_field('details', 'textarea', __('Project details', 'mahfuzar-form-builder'), true))), $this->template_stage(__('Contact', 'mahfuzar-form-builder'), array($this->template_field('name', 'text', __('Name', 'mahfuzar-form-builder'), true), $this->template_field('email', 'email', __('Email', 'mahfuzar-form-builder'), true))))),
            'newsletter' => array('name' => __('Newsletter Signup', 'mahfuzar-form-builder'), 'description' => __('Simple email subscription with consent.', 'mahfuzar-form-builder'), 'icon' => 'dashicons-megaphone', 'schema' => array($this->template_stage(__('Stay Updated', 'mahfuzar-form-builder'), array($this->template_field('name', 'text', __('Name', 'mahfuzar-form-builder')), $this->template_field('email', 'email', __('Email', 'mahfuzar-form-builder'), true), $this->template_field('consent', 'consent', __('I agree to receive email updates.', 'mahfuzar-form-builder'), true))))),
            'support-request' => array('name' => __('Support Request', 'mahfuzar-form-builder'), 'description' => __('Issue details, priority, and attachment.', 'mahfuzar-form-builder'), 'icon' => 'dashicons-sos', 'schema' => array($this->template_stage(__('Support Ticket', 'mahfuzar-form-builder'), array($this->template_field('email', 'email', __('Email', 'mahfuzar-form-builder'), true), $this->template_field('priority', 'select', __('Priority', 'mahfuzar-form-builder'), true, array(__('Low', 'mahfuzar-form-builder'), __('Normal', 'mahfuzar-form-builder'), __('Urgent', 'mahfuzar-form-builder'))), $this->template_field('issue', 'textarea', __('Describe the issue', 'mahfuzar-form-builder'), true), $this->template_field('attachment', 'file', __('Screenshot or document', 'mahfuzar-form-builder')))))),
            'survey' => array('name' => __('Product Survey', 'mahfuzar-form-builder'), 'description' => __('Three quick polls with an open comment.', 'mahfuzar-form-builder'), 'icon' => 'dashicons-chart-bar', 'schema' => array($this->template_stage(__('Product Survey', 'mahfuzar-form-builder'), array($this->template_field('ease', 'poll', __('How easy is the product to use?', 'mahfuzar-form-builder'), true, array('1', '2', '3', '4', '5')), $this->template_field('recommend', 'poll', __('Would you recommend it?', 'mahfuzar-form-builder'), true, array(__('Yes', 'mahfuzar-form-builder'), __('Maybe', 'mahfuzar-form-builder'), __('No', 'mahfuzar-form-builder'))), $this->template_field('favorite', 'textarea', __('What is your favorite feature?', 'mahfuzar-form-builder')))))),
            'quiz' => array('name' => __('Simple Knowledge Quiz', 'mahfuzar-form-builder'), 'description' => __('A ready-to-edit scored three-question quiz.', 'mahfuzar-form-builder'), 'icon' => 'dashicons-welcome-learn-more', 'schema' => array($this->template_stage(__('Quick Quiz', 'mahfuzar-form-builder'), array($this->template_field('q1', 'quiz', __('What is the capital of France?', 'mahfuzar-form-builder'), true, array(__('Paris', 'mahfuzar-form-builder'), __('Rome', 'mahfuzar-form-builder'), __('Madrid', 'mahfuzar-form-builder')), __('Paris', 'mahfuzar-form-builder')), $this->template_field('q2', 'quiz', __('How many days are in a leap year?', 'mahfuzar-form-builder'), true, array('365', '366', '367'), '366'), $this->template_field('q3', 'quiz', __('Which planet is known as the Red Planet?', 'mahfuzar-form-builder'), true, array(__('Mars', 'mahfuzar-form-builder'), __('Venus', 'mahfuzar-form-builder'), __('Jupiter', 'mahfuzar-form-builder')), __('Mars', 'mahfuzar-form-builder')))))),
            'volunteer' => array('name' => __('Volunteer Registration', 'mahfuzar-form-builder'), 'description' => __('Availability, interests, and contact details.', 'mahfuzar-form-builder'), 'icon' => 'dashicons-groups', 'schema' => array($this->template_stage(__('Volunteer With Us', 'mahfuzar-form-builder'), array($this->template_field('name', 'text', __('Name', 'mahfuzar-form-builder'), true), $this->template_field('email', 'email', __('Email', 'mahfuzar-form-builder'), true), $this->template_field('interests', 'checkbox', __('Areas of interest', 'mahfuzar-form-builder'), true, array(__('Events', 'mahfuzar-form-builder'), __('Fundraising', 'mahfuzar-form-builder'), __('Community outreach', 'mahfuzar-form-builder'))), $this->template_field('availability', 'textarea', __('Availability', 'mahfuzar-form-builder')))))),
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
            __('Stripe and PayPal payments', 'mahfuzar-form-builder'),
            __('Mailchimp, Brevo, ActiveCampaign, and ConvertKit', 'mahfuzar-form-builder'),
            __('Zapier and advanced webhook automation', 'mahfuzar-form-builder'),
            __('CRM integrations and lead routing', 'mahfuzar-form-builder'),
            __('20 additional premium form templates', 'mahfuzar-form-builder'),
            __('Calculated fields and order forms', 'mahfuzar-form-builder'),
            __('Electronic signatures and PDF documents', 'mahfuzar-form-builder'),
            __('Advanced spam protection and priority support', 'mahfuzar-form-builder'),
            __('Automatic updates with every paid license', 'mahfuzar-form-builder'),
        );
        $plans = array(
            array('name' => __('Pro Annual', 'mahfuzar-form-builder'), 'price' => '$19.99', 'term' => __('per year', 'mahfuzar-form-builder'), 'sites' => __('1 website', 'mahfuzar-form-builder'), 'url' => $this->purchase_url('single', 'plans-single'), 'featured' => false),
            array('name' => __('Pro Bundle', 'mahfuzar-form-builder'), 'price' => '$99.99', 'term' => __('per year', 'mahfuzar-form-builder'), 'sites' => __('Up to 10 websites', 'mahfuzar-form-builder'), 'url' => $this->purchase_url('bundle', 'plans-bundle'), 'featured' => true),
            array('name' => __('Pro Lifetime', 'mahfuzar-form-builder'), 'price' => '$249.99', 'term' => __('one-time payment', 'mahfuzar-form-builder'), 'sites' => __('1 website · lifetime license', 'mahfuzar-form-builder'), 'url' => $this->purchase_url('lifetime', 'plans-lifetime'), 'featured' => false),
        );
        ?>
        <div class="wrap webform-wrap webform-pro-page">
            <div class="webform-pro-hero">
                <span class="webform-pro-badge"><?php esc_html_e('WEBFORM PRO', 'mahfuzar-form-builder'); ?></span>
                <h1><?php esc_html_e('Turn every form into a connected workflow', 'mahfuzar-form-builder'); ?></h1>
                <p><?php esc_html_e('Keep everything in Webform Free, then add payments, email marketing, automation, and advanced business tools.', 'mahfuzar-form-builder'); ?></p>
                <p class="webform-pro-price-intro"><?php esc_html_e('Choose the license that fits your websites.', 'mahfuzar-form-builder'); ?></p>
            </div>
            <div class="webform-plan-grid"><?php foreach ($plans as $plan) : ?><article class="webform-plan-card <?php echo $plan['featured'] ? 'is-featured' : ''; ?>"><?php if ($plan['featured']) : ?><span class="webform-plan-popular"><?php esc_html_e('BEST FOR AGENCIES', 'mahfuzar-form-builder'); ?></span><?php endif; ?><h2><?php echo esc_html($plan['name']); ?></h2><div class="webform-plan-price"><?php echo esc_html($plan['price']); ?></div><p><?php echo esc_html($plan['term']); ?></p><strong><?php echo esc_html($plan['sites']); ?></strong><a class="button button-primary" href="<?php echo esc_url($plan['url']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Choose this plan', 'mahfuzar-form-builder'); ?></a></article><?php endforeach; ?></div>
            <div class="webform-pro-grid">
                <?php foreach ($features as $feature) : ?><div class="webform-pro-feature"><span class="dashicons dashicons-yes-alt"></span><strong><?php echo esc_html($feature); ?></strong></div><?php endforeach; ?>
            </div>
            <p class="description"><?php esc_html_e('Webform Pro will install as a separate licensed add-on. Your forms and entries remain compatible with the free plugin.', 'mahfuzar-form-builder'); ?></p>
        </div>
        <?php
    }

    private function upgrade_url($source) {
        return apply_filters('webform_upgrade_url', $this->purchase_url('single', $source));
    }

    private function purchase_url($plan, $source) {
        $urls = array(
            'single' => 'https://www.webninjallc.com/product/webform-pro/',
            'bundle' => 'https://www.webninjallc.com/product/webform-pro-bundle/',
            'lifetime' => 'https://www.webninjallc.com/product/webform-lifetime/',
        );
        return add_query_arg(array('utm_source' => 'webform-plugin', 'utm_medium' => 'upgrade', 'utm_campaign' => sanitize_key($source)), $urls[$plan] ?? $urls['single']);
    }

    private function is_pro_active() {
        return defined('WEBFORM_PRO_PLUGIN_VERSION') || defined('WEBFORM_PRO_VERSION') || (bool) apply_filters('webform_is_pro_active', false);
    }
}
