<?php

defined('ABSPATH') || exit; // FormOrbit administration.

class Webform_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'menu'));
        add_action('admin_init', array($this, 'redirect_legacy_pages'));
        add_action('admin_enqueue_scripts', array($this, 'assets'));
        add_action('wp_ajax_webform_save_form', array($this, 'save_form'));
        add_action('wp_ajax_webform_delete_form', array($this, 'delete_form'));
        add_action('wp_ajax_formorbit_save_form', array($this, 'save_form'));
        add_action('wp_ajax_formorbit_delete_form', array($this, 'delete_form'));
        add_action('admin_post_webform_restore_form', array($this, 'restore_form'));
        add_action('admin_post_webform_permanently_delete_form', array($this, 'permanently_delete_form'));
        add_action('admin_post_webform_duplicate_form', array($this, 'duplicate_form'));
        add_action('admin_post_webform_export_entries', array($this, 'export_entries'));
        add_action('admin_post_webform_delete_entry', array($this, 'delete_entry'));
        add_action('admin_post_webform_save_global_settings', array($this, 'save_global_settings'));
        add_action('admin_post_webform_import', array($this, 'import_form'));
        add_action('admin_post_formorbit_restore_form', array($this, 'restore_form'));
        add_action('admin_post_formorbit_permanently_delete_form', array($this, 'permanently_delete_form'));
        add_action('admin_post_formorbit_duplicate_form', array($this, 'duplicate_form'));
        add_action('admin_post_formorbit_export_entries', array($this, 'export_entries'));
        add_action('admin_post_formorbit_delete_entry', array($this, 'delete_entry'));
        add_action('admin_post_formorbit_save_global_settings', array($this, 'save_global_settings'));
        add_action('admin_post_formorbit_import', array($this, 'import_form'));
        add_action('admin_post_formorbit_dismiss_review', array($this, 'dismiss_review'));
        add_action('admin_head', array($this, 'suppress_editor_notices'), 1);
        add_action('admin_notices', array($this, 'review_notice'));
        add_filter('plugin_action_links_' . plugin_basename(WEBFORM_FILE), array($this, 'plugin_action_links'));
    }

    public function menu() {
        add_menu_page(
            __('FormOrbit', 'formorbit'),
            __('FormOrbit', 'formorbit'),
            'manage_options',
            'formorbit',
            array($this, 'forms_page'),
            'dashicons-feedback',
            26
        );
        add_submenu_page('formorbit', __('All Forms', 'formorbit'), __('All Forms', 'formorbit'), 'manage_options', 'formorbit', array($this, 'forms_page'), 0);
        add_submenu_page('formorbit', __('Add New', 'formorbit'), __('Add New', 'formorbit'), 'manage_options', 'formorbit-builder', array($this, 'builder_page'), 1);
        add_submenu_page('formorbit', __('Entries', 'formorbit'), __('Entries', 'formorbit'), 'manage_options', 'formorbit-entries', array($this, 'entries_page'), 2);
        add_submenu_page('formorbit', __('Analytics & Reporting', 'formorbit'), __('Analytics', 'formorbit'), 'manage_options', 'formorbit-analytics', array($this, 'analytics_page'), 3);
        add_submenu_page('formorbit', __('Form Templates', 'formorbit'), __('Templates', 'formorbit'), 'manage_options', 'formorbit-templates', array($this, 'templates_page'), 4);
        add_submenu_page('formorbit', __('FormOrbit Tools', 'formorbit'), __('Tools', 'formorbit'), 'manage_options', 'formorbit-tools', array($this, 'tools_page'), 6);
        add_submenu_page('formorbit', __('FormOrbit Settings', 'formorbit'), __('Settings', 'formorbit'), 'manage_options', 'formorbit-settings', array($this, 'settings_page'), 7);
        if (!$this->is_pro_active()) {
            add_submenu_page('formorbit', __('FormOrbit Pro', 'formorbit'), __('Upgrade to Pro', 'formorbit'), 'manage_options', 'formorbit-pro', array($this, 'pro_page'), 8);
        }
    }

    public function plugin_action_links($links) {
        $add_form = '<a href="' . esc_url(admin_url('admin.php?page=formorbit-builder')) . '">' . esc_html__('Add New Form', 'formorbit') . '</a>';
        array_unshift($links, $add_form);
        if (!$this->is_pro_active()) {
            $upgrade = '<a href="' . esc_url($this->upgrade_url('plugins-page')) . '" target="_blank" rel="noopener noreferrer" style="color:#5b3fc1;font-weight:700">' . esc_html__('Go Pro', 'formorbit') . '</a>';
            array_splice($links, 1, 0, array($upgrade));
        }
        return $links;
    }

    public function review_notice() {
        if (!current_user_can('manage_options') || $this->is_pro_active() || get_option('formorbit_review_dismissed')) return;
        $activated = absint(get_option('formorbit_activated_at', 0));
        if (!$activated) {
            update_option('formorbit_activated_at', time(), false);
            return;
        }
        $form_count = wp_count_posts('webform_form');
        $published = absint($form_count->publish ?? 0) + absint($form_count->draft ?? 0);
        if ($published < 2 && time() - $activated < 7 * DAY_IN_SECONDS) return;
        $dismiss = wp_nonce_url(admin_url('admin-post.php?action=formorbit_dismiss_review'), 'formorbit_dismiss_review');
        echo '<div class="notice notice-info is-dismissible webform-review-notice"><p><strong>' . esc_html__('Enjoying FormOrbit?', 'formorbit') . '</strong> ' . esc_html__('A short WordPress.org review helps other site owners discover the free form builder.', 'formorbit') . '</p><p><a class="button button-primary" target="_blank" rel="noopener noreferrer" href="https://wordpress.org/support/plugin/formorbit/reviews/#new-post">' . esc_html__('Leave a review', 'formorbit') . '</a> <a class="button" href="' . esc_url($dismiss) . '">' . esc_html__('Maybe later', 'formorbit') . '</a></p></div>';
    }

    public function dismiss_review() {
        if (!current_user_can('manage_options')) wp_die(esc_html__('Permission denied.', 'formorbit'));
        check_admin_referer('formorbit_dismiss_review');
        update_option('formorbit_review_dismissed', 1, false);
        wp_safe_redirect(wp_get_referer() ?: admin_url('admin.php?page=formorbit'));
        exit;
    }

    public function redirect_legacy_pages() {
        if (!is_admin() || !current_user_can('manage_options') || empty($_GET['page'])) {
            return;
        }
        $legacy = array(
            'webform' => 'formorbit',
            'webform-builder' => 'formorbit-builder',
            'webform-templates' => 'formorbit-templates',
            'webform-entries' => 'formorbit-entries',
            'webform-import' => 'formorbit-tools',
            'formorbit-import' => 'formorbit-tools',
            'webform-settings' => 'formorbit-settings',
            'webform-pro' => 'formorbit-pro',
            'webform-email-delivery' => 'formorbit-email-delivery',
        );
        $page = sanitize_key(wp_unslash($_GET['page']));
        if (!isset($legacy[$page])) {
            return;
        }
        $query = wp_unslash($_GET);
        $query['page'] = $legacy[$page];
        wp_safe_redirect(add_query_arg(map_deep($query, 'sanitize_text_field'), admin_url('admin.php')));
        exit;
    }

    public function assets($hook) {
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
        if (strpos($page, 'formorbit') !== 0 && strpos($hook, 'formorbit') === false) {
            return;
        }
        wp_enqueue_style('webform-admin', WEBFORM_URL . 'assets/css/admin.css', array(), WEBFORM_VERSION);
        wp_enqueue_style('webform-builder-layout', WEBFORM_URL . 'assets/css/builder-refresh.css', array('webform-admin'), WEBFORM_VERSION);
        wp_enqueue_style('webform-responsive-controls', WEBFORM_URL . 'assets/css/responsive-upgrades.css', array('webform-builder-layout'), WEBFORM_VERSION);
        wp_enqueue_style('webform-field-previews', WEBFORM_URL . 'assets/css/field-previews.css', array('webform-responsive-controls'), WEBFORM_VERSION);
        wp_enqueue_style('webform-public-preview', WEBFORM_URL . 'assets/css/public.css', array('webform-field-previews'), WEBFORM_VERSION);
        wp_enqueue_style('formorbit-icon-alignment', WEBFORM_URL . 'assets/css/icon-alignment.css', array('webform-public-preview'), WEBFORM_VERSION);
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-droppable');
        wp_enqueue_script('webform-admin', WEBFORM_URL . 'assets/js/admin.js', array('jquery', 'jquery-ui-sortable', 'jquery-ui-droppable'), WEBFORM_VERSION, true);
        wp_localize_script('webform-admin', 'WebformAdmin', apply_filters('webform_admin_script_data', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('webform_admin'),
            'formsUrl' => admin_url('admin.php?page=formorbit'),
            'postTypes' => array_map(function ($type) { return $type->labels->singular_name; }, get_post_types(array('show_ui' => true), 'objects')),
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
                <div><h1><?php esc_html_e('FormOrbit', 'formorbit'); ?></h1><p><?php esc_html_e('Build and manage forms without code.', 'formorbit'); ?></p></div>
                <a class="button button-primary webform-create-button" href="<?php echo esc_url(admin_url('admin.php?page=formorbit-builder')); ?>"><svg class="webform-button-icon" viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="M9 3h2v6h6v2h-6v6H9v-6H3V9h6z"></path></svg><?php esc_html_e('Create form', 'formorbit'); ?></a>
            </div>
            <div class="webform-card webform-list-card">
                <div class="webform-list-toolbar">
                    <nav class="webform-list-tabs" aria-label="<?php esc_attr_e('Form views', 'formorbit'); ?>">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=formorbit')); ?>" class="<?php echo $current_view === 'all' ? 'is-active' : ''; ?>"><span class="dashicons dashicons-feedback"></span><?php esc_html_e('All forms', 'formorbit'); ?><strong><?php echo esc_html($active_count); ?></strong></a>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=formorbit&form_status=trash')); ?>" class="<?php echo $current_view === 'trash' ? 'is-active' : ''; ?>"><span class="dashicons dashicons-trash"></span><?php esc_html_e('Trash', 'formorbit'); ?><strong><?php echo esc_html($trash_count); ?></strong></a>
                    </nav>
                    <?php if ($forms) : ?><label class="webform-list-search"><span class="dashicons dashicons-search"></span><span class="screen-reader-text"><?php esc_html_e('Search forms', 'formorbit'); ?></span><input type="search" id="webform-form-search" placeholder="<?php esc_attr_e('Search forms…', 'formorbit'); ?>" autocomplete="off"></label><?php endif; ?>
                </div>
                <?php if (!$forms) : ?>
                    <div class="webform-empty"><span class="dashicons <?php echo $current_view === 'trash' ? 'dashicons-trash' : 'dashicons-feedback'; ?>"></span><h2><?php echo $current_view === 'trash' ? esc_html__('Trash is empty', 'formorbit') : esc_html__('Create your first form', 'formorbit'); ?></h2><p><?php echo $current_view === 'trash' ? esc_html__('Deleted forms will appear here until they are restored or permanently removed.', 'formorbit') : esc_html__('Add fields, arrange stages, and publish it with a shortcode.', 'formorbit'); ?></p></div>
                <?php else : ?>
                    <div class="webform-list-table-wrap"><table class="wp-list-table widefat fixed table-view-list webform-forms-table"><thead><tr>
                        <th class="column-primary"><?php esc_html_e('Form', 'formorbit'); ?></th><th class="column-shortcode"><?php esc_html_e('Shortcode', 'formorbit'); ?></th><th class="column-number"><?php esc_html_e('Entries', 'formorbit'); ?></th><th class="column-number"><?php esc_html_e('Embeds', 'formorbit'); ?></th><th class="column-status"><?php esc_html_e('Status', 'formorbit'); ?></th><th class="column-date"><?php esc_html_e('Created', 'formorbit'); ?></th><th class="column-date"><?php esc_html_e('Updated', 'formorbit'); ?></th>
                    </tr></thead><tbody>
                    <?php foreach ($forms as $form) : ?>
                        <?php $edit_url = admin_url('admin.php?page=formorbit-builder&form_id=' . $form->ID); $preview_url = wp_nonce_url(add_query_arg('formorbit_preview', $form->ID, home_url('/')), 'webform_preview_' . $form->ID); $schema = (array) get_post_meta($form->ID, '_webform_schema', true); $stage_count = max(1, count($schema)); $shortcode = '[formorbit id="' . $form->ID . '"]'; ?>
                        <tr data-form-title="<?php echo esc_attr(strtolower($form->post_title . ' ' . $form->ID)); ?>">
                            <td class="column-primary" data-colname="<?php esc_attr_e('Form', 'formorbit'); ?>"><div class="webform-form-cell"><span class="webform-form-icon"><span class="dashicons dashicons-feedback"></span></span><div class="webform-form-summary"><strong class="webform-form-title"><?php if ($current_view === 'trash') : ?><?php echo esc_html($form->post_title); ?><?php else : ?><a class="row-title" href="<?php echo esc_url($edit_url); ?>"><?php echo esc_html($form->post_title); ?></a><a class="webform-title-preview" href="<?php echo esc_url($preview_url); ?>" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e('Preview form', 'formorbit'); ?>" aria-label="<?php esc_attr_e('Preview form', 'formorbit'); ?>"><span class="dashicons dashicons-visibility" aria-hidden="true"></span></a><?php endif; ?></strong><small><?php echo esc_html(sprintf(_n('ID %1$d · %2$d stage', 'ID %1$d · %2$d stages', $stage_count, 'formorbit'), $form->ID, $stage_count)); ?></small>
                                <?php if ($current_view === 'trash') : ?>
                                    <div class="row-actions"><span><a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=formorbit_restore_form&form_id=' . $form->ID), 'webform_restore_' . $form->ID)); ?>"><span class="dashicons dashicons-undo"></span><?php esc_html_e('Restore', 'formorbit'); ?></a></span><span class="delete"><a onclick="return confirm('<?php echo esc_js(__('Permanently delete this form and all of its entries?', 'formorbit')); ?>')" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=formorbit_permanently_delete_form&form_id=' . $form->ID), 'webform_permanently_delete_' . $form->ID)); ?>"><span class="dashicons dashicons-trash"></span><?php esc_html_e('Delete permanently', 'formorbit'); ?></a></span></div>
                                <?php else : ?>
                                    <div class="row-actions"><span><a href="<?php echo esc_url($edit_url); ?>"><span class="dashicons dashicons-edit"></span><?php esc_html_e('Edit', 'formorbit'); ?></a></span><span><a href="<?php echo esc_url(add_query_arg('panel', 'confirmation', $edit_url)); ?>"><span class="dashicons dashicons-admin-generic"></span><?php esc_html_e('Settings', 'formorbit'); ?></a></span><span><a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=formorbit_duplicate_form&form_id=' . $form->ID), 'webform_duplicate_' . $form->ID)); ?>"><span class="dashicons dashicons-admin-page"></span><?php esc_html_e('Duplicate', 'formorbit'); ?></a></span><span class="trash"><button type="button" class="button-link-delete webform-delete" data-id="<?php echo esc_attr($form->ID); ?>"><span class="dashicons dashicons-trash"></span><?php esc_html_e('Trash', 'formorbit'); ?></button></span></div>
                                <?php endif; ?>
                                </div></div>
                                <button type="button" class="toggle-row"><span class="screen-reader-text"><?php esc_html_e('Show more details', 'formorbit'); ?></span></button>
                            </td>
                            <td data-colname="<?php esc_attr_e('Shortcode', 'formorbit'); ?>"><span class="webform-shortcode"><code><?php echo esc_html($shortcode); ?></code><button type="button" class="webform-copy-shortcode" data-shortcode="<?php echo esc_attr($shortcode); ?>" title="<?php esc_attr_e('Copy shortcode', 'formorbit'); ?>" aria-label="<?php esc_attr_e('Copy shortcode', 'formorbit'); ?>"><span class="dashicons dashicons-admin-page"></span></button></span></td>
                            <td data-colname="<?php esc_attr_e('Entries', 'formorbit'); ?>"><a class="webform-count-link" href="<?php echo esc_url(admin_url('admin.php?page=formorbit-entries&form_id=' . $form->ID)); ?>"><?php echo esc_html($this->entry_count($form->ID)); ?></a></td>
                            <td data-colname="<?php esc_attr_e('Embeds', 'formorbit'); ?>"><span class="webform-count-value"><?php echo esc_html($embed_counts[$form->ID] ?? 0); ?></span></td>
                            <td data-colname="<?php esc_attr_e('Status', 'formorbit'); ?>"><span class="webform-status webform-status-<?php echo esc_attr($form->post_status); ?>"><?php echo esc_html($form->post_status === 'trash' ? __('Trashed', 'formorbit') : ($form->post_status === 'publish' ? __('Published', 'formorbit') : __('Draft', 'formorbit'))); ?></span></td>
                            <td data-colname="<?php esc_attr_e('Created', 'formorbit'); ?>" title="<?php echo esc_attr(get_the_date('c', $form)); ?>"><span class="webform-date"><?php echo esc_html(get_the_date('M j, Y', $form)); ?></span></td>
                            <td data-colname="<?php esc_attr_e('Updated', 'formorbit'); ?>" title="<?php echo esc_attr(get_the_modified_date('c', $form)); ?>"><span class="webform-date"><?php echo esc_html(get_the_modified_date('M j, Y', $form)); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="webform-no-search-results" hidden><td colspan="7"><span class="dashicons dashicons-search"></span><strong><?php esc_html_e('No matching forms', 'formorbit'); ?></strong><small><?php esc_html_e('Try another form name or ID.', 'formorbit'); ?></small></td></tr>
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

    public function analytics_page() {
        if (!current_user_can('manage_options')) return;
        $days = isset($_GET['range']) ? absint($_GET['range']) : 30;
        if (!in_array($days, array(7, 30, 90, 365), true)) $days = 30;
        $form_filter = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;
        $forms = get_posts(array('post_type' => 'webform_form', 'post_status' => array('publish', 'draft'), 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC'));
        $start = strtotime('-' . ($days - 1) . ' days', current_time('timestamp'));
        $daily = array();
        $by_form = array();
        $totals = array('views' => 0, 'visitors' => 0, 'clicks' => 0, 'submissions' => 0);
        for ($offset = 0; $offset < $days; $offset++) {
            $date = wp_date('Y-m-d', $start + $offset * DAY_IN_SECONDS);
            $daily[$date] = array('views' => 0, 'visitors' => 0, 'clicks' => 0, 'submissions' => 0);
        }
        $months = array_unique(array_map(function ($date) { return substr($date, 0, 7); }, array_keys($daily)));
        foreach ($months as $month) {
            $stored = (array) get_option('formorbit_analytics_' . str_replace('-', '_', $month), array());
            foreach ($stored as $date => $form_rows) {
                if (!isset($daily[$date])) continue;
                foreach ((array) $form_rows as $form_id => $metrics) {
                    $form_id = absint($form_id);
                    if ($form_filter && $form_filter !== $form_id) continue;
                    if (!isset($by_form[$form_id])) $by_form[$form_id] = array('views' => 0, 'visitors' => 0, 'clicks' => 0, 'submissions' => 0);
                    foreach (array_keys($totals) as $metric) {
                        $value = absint($metrics[$metric] ?? 0);
                        $daily[$date][$metric] += $value;
                        $by_form[$form_id][$metric] += $value;
                        $totals[$metric] += $value;
                    }
                }
            }
        }
        $conversion = $totals['views'] ? round($totals['submissions'] / $totals['views'] * 100, 1) : 0;
        ?>
        <div class="wrap webform-wrap webform-analytics-wrap">
            <div class="webform-page-head"><div><h1><?php esc_html_e('Analytics & Reporting', 'formorbit'); ?></h1><p><?php esc_html_e('Understand form visibility, engagement, and submission performance.', 'formorbit'); ?></p></div></div>
            <form class="webform-analytics-filters" method="get"><input type="hidden" name="page" value="formorbit-analytics"><label><?php esc_html_e('Date range', 'formorbit'); ?><select name="range"><?php foreach (array(7 => __('Last 7 days', 'formorbit'), 30 => __('Last 30 days', 'formorbit'), 90 => __('Last 90 days', 'formorbit'), 365 => __('Last year', 'formorbit')) as $value => $label) : ?><option value="<?php echo esc_attr($value); ?>" <?php selected($days, $value); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></label><label><?php esc_html_e('Form', 'formorbit'); ?><select name="form_id"><option value="0"><?php esc_html_e('All forms', 'formorbit'); ?></option><?php foreach ($forms as $form) : ?><option value="<?php echo esc_attr($form->ID); ?>" <?php selected($form_filter, $form->ID); ?>><?php echo esc_html($form->post_title); ?></option><?php endforeach; ?></select></label><button class="button button-primary"><?php esc_html_e('Apply filters', 'formorbit'); ?></button></form>
            <div class="webform-analytics-cards">
                <?php foreach (array('views' => array(__('Form views', 'formorbit'), 'dashicons-visibility'), 'visitors' => array(__('Unique visitors', 'formorbit'), 'dashicons-groups'), 'clicks' => array(__('Engaged visitors', 'formorbit'), 'dashicons-marker'), 'submissions' => array(__('Submissions', 'formorbit'), 'dashicons-yes-alt')) as $key => $card) : ?><article><span class="dashicons <?php echo esc_attr($card[1]); ?>"></span><small><?php echo esc_html($card[0]); ?></small><strong><?php echo esc_html(number_format_i18n($totals[$key])); ?></strong></article><?php endforeach; ?>
                <article><span class="dashicons dashicons-chart-line"></span><small><?php esc_html_e('View conversion', 'formorbit'); ?></small><strong><?php echo esc_html($conversion); ?>%</strong></article>
            </div>
            <div class="webform-analytics-grid">
                <section class="webform-card"><h2><?php esc_html_e('Performance by form', 'formorbit'); ?></h2><div class="webform-analytics-table-wrap"><table class="widefat striped"><thead><tr><th><?php esc_html_e('Form', 'formorbit'); ?></th><th><?php esc_html_e('Views', 'formorbit'); ?></th><th><?php esc_html_e('Visitors', 'formorbit'); ?></th><th><?php esc_html_e('Engaged', 'formorbit'); ?></th><th><?php esc_html_e('Submissions', 'formorbit'); ?></th><th><?php esc_html_e('Conversion', 'formorbit'); ?></th></tr></thead><tbody><?php if (!$by_form) : ?><tr><td colspan="6"><?php esc_html_e('Analytics will appear after visitors view and interact with your forms.', 'formorbit'); ?></td></tr><?php else : foreach ($by_form as $form_id => $metrics) : ?><tr><td><a href="<?php echo esc_url(admin_url('admin.php?page=formorbit-builder&form_id=' . $form_id)); ?>"><?php echo esc_html(get_the_title($form_id) ?: sprintf(__('Form #%d', 'formorbit'), $form_id)); ?></a></td><td><?php echo esc_html(number_format_i18n($metrics['views'])); ?></td><td><?php echo esc_html(number_format_i18n($metrics['visitors'])); ?></td><td><?php echo esc_html(number_format_i18n($metrics['clicks'])); ?></td><td><?php echo esc_html(number_format_i18n($metrics['submissions'])); ?></td><td><?php echo esc_html($metrics['views'] ? round($metrics['submissions'] / $metrics['views'] * 100, 1) : 0); ?>%</td></tr><?php endforeach; endif; ?></tbody></table></div></section>
                <section class="webform-card"><h2><?php esc_html_e('Daily activity', 'formorbit'); ?></h2><div class="webform-analytics-table-wrap"><table class="widefat striped"><thead><tr><th><?php esc_html_e('Date', 'formorbit'); ?></th><th><?php esc_html_e('Views', 'formorbit'); ?></th><th><?php esc_html_e('Engaged', 'formorbit'); ?></th><th><?php esc_html_e('Submissions', 'formorbit'); ?></th></tr></thead><tbody><?php foreach (array_reverse($daily, true) as $date => $metrics) : ?><tr><td><?php echo esc_html(wp_date(get_option('date_format'), strtotime($date))); ?></td><td><?php echo esc_html(number_format_i18n($metrics['views'])); ?></td><td><?php echo esc_html(number_format_i18n($metrics['clicks'])); ?></td><td><?php echo esc_html(number_format_i18n($metrics['submissions'])); ?></td></tr><?php endforeach; ?></tbody></table></div></section>
            </div><p class="description"><?php esc_html_e('Unique visitors are counted once per browser session for each form. Engagement records the first field interaction. Tracking begins with this release and stores aggregate counts only.', 'formorbit'); ?></p>
        </div>
        <?php
    }

    private function embed_counts() {
        $counts = array();
        $content_ids = array();
        foreach (array('formorbit', 'webform') as $shortcode_name) {
            $content_ids = array_merge($content_ids, get_posts(array('post_type' => 'any', 'post_status' => array('publish', 'private', 'draft', 'future'), 'posts_per_page' => 2000, 'fields' => 'ids', 's' => $shortcode_name, 'no_found_rows' => true, 'orderby' => 'none')));
        }
        $content_ids = array_unique(array_map('absint', $content_ids));
        foreach ($content_ids as $content_id) {
            $content = (string) get_post_field('post_content', $content_id);
            if (!preg_match_all('/\[(?:formorbit|webform)\b[^\]]*\bid\s*=\s*(["\']?)(\d+)\1[^\]]*\]/i', $content, $matches)) {
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
        $shortcode = $form_id ? '[formorbit id="' . $form_id . '"]' : '';
        $php_embed = $form_id ? "<?php echo do_shortcode( '[formorbit id=\"" . $form_id . "\"]' ); ?>" : '';
        ?>
        <div class="wrap webform-wrap webform-builder-wrap">
            <div class="webform-page-head">
                <div><h1><?php echo $form ? esc_html__('Edit form', 'formorbit') : esc_html__('Create form', 'formorbit'); ?></h1><p><?php esc_html_e('Drag fields into a stage, then select a field to configure it.', 'formorbit'); ?></p></div>
                <div class="webform-editor-actions">
                    <span id="webform-save-status"></span>
                    <div class="webform-history-actions" role="group" aria-label="<?php esc_attr_e('Edit history', 'formorbit'); ?>"><button type="button" class="button" id="webform-undo" disabled title="<?php esc_attr_e('Undo last change', 'formorbit'); ?>" aria-label="<?php esc_attr_e('Undo last change', 'formorbit'); ?>"><span class="dashicons dashicons-undo" aria-hidden="true"></span></button><button type="button" class="button" id="webform-redo" disabled title="<?php esc_attr_e('Redo last change', 'formorbit'); ?>" aria-label="<?php esc_attr_e('Redo last change', 'formorbit'); ?>"><span class="dashicons dashicons-redo" aria-hidden="true"></span></button></div>
                    <button type="button" class="button webform-embed-trigger" id="webform-open-embed" aria-haspopup="dialog" aria-controls="webform-embed-panel"<?php echo $form_id ? '' : ' hidden'; ?>><span class="dashicons dashicons-editor-code" aria-hidden="true"></span><?php esc_html_e('Embed', 'formorbit'); ?></button>
                    <button type="button" class="button button-primary button-hero" id="webform-save"><?php esc_html_e('Save form', 'formorbit'); ?></button>
                </div>
            </div>
            <input type="hidden" id="webform-id" value="<?php echo esc_attr($form_id); ?>">
            <input type="hidden" id="webform-schema" value="<?php echo esc_attr(wp_json_encode($schema ? $schema : array())); ?>">
            <input type="hidden" id="webform-settings" value="<?php echo esc_attr(wp_json_encode($settings ? $settings : array())); ?>">
            <section id="webform-embed-panel" class="webform-editor-embed-modal" hidden aria-hidden="true">
                <button type="button" class="webform-editor-embed-backdrop" aria-label="<?php esc_attr_e('Close embed options', 'formorbit'); ?>"></button>
                <div class="webform-editor-embed-dialog" role="dialog" aria-modal="true" aria-labelledby="webform-embed-title" aria-describedby="webform-embed-description">
                    <header class="webform-editor-embed-head">
                        <div class="webform-editor-embed-icon"><span class="dashicons dashicons-editor-code" aria-hidden="true"></span></div>
                        <div><h2 id="webform-embed-title"><?php esc_html_e('Embed this form', 'formorbit'); ?></h2><p id="webform-embed-description"><?php esc_html_e('Copy the format that matches where you are adding the form.', 'formorbit'); ?></p></div>
                        <button type="button" class="webform-editor-embed-close" aria-label="<?php esc_attr_e('Close embed options', 'formorbit'); ?>">×</button>
                    </header>
                    <div class="webform-editor-embed-options">
                        <div class="webform-editor-embed-option">
                            <div><strong><?php esc_html_e('WordPress shortcode', 'formorbit'); ?></strong><small><?php esc_html_e('For posts, pages, widgets, and Shortcode blocks.', 'formorbit'); ?></small></div>
                            <code id="webform-editor-shortcode"><?php echo esc_html($shortcode); ?></code>
                            <button type="button" class="button button-primary webform-copy-embed" data-copy-target="webform-editor-shortcode"><span class="dashicons dashicons-admin-page" aria-hidden="true"></span><span class="webform-copy-label"><?php esc_html_e('Copy', 'formorbit'); ?></span></button>
                        </div>
                        <div class="webform-editor-embed-option">
                            <div><strong><?php esc_html_e('PHP template code', 'formorbit'); ?></strong><small><?php esc_html_e('For a trusted theme or plugin PHP template.', 'formorbit'); ?></small></div>
                            <code id="webform-editor-php"><?php echo esc_html($php_embed); ?></code>
                            <button type="button" class="button webform-copy-embed" data-copy-target="webform-editor-php"><span class="dashicons dashicons-admin-page" aria-hidden="true"></span><span class="webform-copy-label"><?php esc_html_e('Copy', 'formorbit'); ?></span></button>
                        </div>
                    </div>
                    <footer class="webform-editor-embed-footer"><span class="dashicons dashicons-info-outline" aria-hidden="true"></span><?php esc_html_e('Save the form before copying to ensure the embed code uses the latest form ID.', 'formorbit'); ?></footer>
                </div>
            </section>
            <div class="webform-name-row">
                <label for="webform-name"><?php esc_html_e('Form name', 'formorbit'); ?></label>
                <input id="webform-name" class="regular-text" value="<?php echo esc_attr($form ? $form->post_title : __('Untitled form', 'formorbit')); ?>">
            </div>
            <div class="webform-builder">
                <aside class="webform-field-picker" id="webform-field-picker" aria-hidden="true">
                    <div class="webform-field-picker-backdrop"></div><div class="webform-field-picker-dialog"><div class="webform-field-picker-head"><div><h2><?php esc_html_e('Add a field', 'formorbit'); ?></h2><p><?php esc_html_e('Choose a field to add to the current stage.', 'formorbit'); ?></p></div><button type="button" class="webform-field-picker-close" aria-label="<?php esc_attr_e('Close field picker', 'formorbit'); ?>">×</button></div>
                    <h3><?php esc_html_e('Standard fields', 'formorbit'); ?></h3>
                    <div id="webform-palette" class="webform-palette">
                        <?php
                        $standard_fields = array(
                            'name' => __('Name', 'formorbit'),
                            'text' => __('Text', 'formorbit'),
                            'email' => __('Email', 'formorbit'),
                            'textarea' => __('Long text', 'formorbit'),
                            'select' => __('Dropdown', 'formorbit'),
                            'radio' => __('Radio', 'formorbit'),
                            'checkbox' => __('Checkbox', 'formorbit'),
                            'number' => __('Number', 'formorbit'),
                            'date' => __('Date', 'formorbit'),
                            'time' => __('Time', 'formorbit'),
                            'phone' => __('Phone', 'formorbit'),
                            'url' => __('Website', 'formorbit'),
                            'file' => __('File upload', 'formorbit'),
                            'consent' => __('Consent', 'formorbit'),
                            'poll' => __('Poll', 'formorbit'),
                            'quiz' => __('Quiz question', 'formorbit'),
                            'rating' => __('Rating', 'formorbit'),
                            'slider' => __('Slider', 'formorbit'),
                            'hidden' => __('Hidden field', 'formorbit'),
                            'html' => __('HTML content', 'formorbit'),
                            'captcha' => __('Google CAPTCHA', 'formorbit'),
                            'page_break' => __('Page break', 'formorbit'),
                            'heading' => __('Heading', 'formorbit'),
                        );
                        $fields = apply_filters('webform_field_palette', $standard_fields);
                        $field_icons = array('name' => 'dashicons-admin-users', 'text' => 'dashicons-editor-textcolor', 'email' => 'dashicons-email', 'textarea' => 'dashicons-editor-alignleft', 'select' => 'dashicons-arrow-down-alt2', 'radio' => 'dashicons-marker', 'checkbox' => 'dashicons-yes', 'number' => 'dashicons-editor-ol', 'date' => 'dashicons-calendar', 'time' => 'dashicons-clock', 'phone' => 'dashicons-phone', 'url' => 'dashicons-admin-links', 'file' => 'dashicons-upload', 'consent' => 'dashicons-privacy', 'poll' => 'dashicons-chart-bar', 'quiz' => 'dashicons-welcome-learn-more', 'rating' => 'dashicons-star-filled', 'slider' => 'dashicons-image-flip-horizontal', 'hidden' => 'dashicons-hidden', 'html' => 'dashicons-editor-code', 'captcha' => 'dashicons-shield', 'page_break' => 'dashicons-controls-forward', 'heading' => 'dashicons-heading');
                        foreach ($fields as $type => $label) {
                            printf('<button type="button" class="webform-palette-item" data-type="%s"><span class="dashicons %s"></span><span>%s</span></button>', esc_attr($type), esc_attr($field_icons[$type] ?? 'dashicons-plus-alt2'), esc_html($label));
                        }
                        ?>
                    </div>
                    </div>
                </aside>
                <main class="webform-canvas-panel">
                    <div class="webform-stage-tabs"><div id="webform-stage-tabs"></div><div class="webform-canvas-tools"><div class="webform-device-switcher" aria-label="<?php esc_attr_e('Preview size', 'formorbit'); ?>"><button type="button" class="is-active" data-device="desktop" title="<?php esc_attr_e('Desktop preview', 'formorbit'); ?>"><span class="dashicons dashicons-desktop"></span></button><button type="button" data-device="tablet" title="<?php esc_attr_e('Tablet preview', 'formorbit'); ?>"><span class="dashicons dashicons-tablet"></span></button><button type="button" data-device="mobile" title="<?php esc_attr_e('Mobile preview', 'formorbit'); ?>"><span class="dashicons dashicons-smartphone"></span></button></div><button type="button" class="button button-primary webform-open-field-picker"><span class="dashicons dashicons-plus-alt2"></span><?php esc_html_e('Add field', 'formorbit'); ?></button><button class="button" id="webform-add-stage">+ <?php esc_html_e('Add stage', 'formorbit'); ?></button></div></div>
                    <div id="webform-canvas" class="webform-canvas"></div>
                </main>
                <aside class="webform-properties">
                    <div class="webform-property-tabs"><button type="button" class="is-active" data-panel="field"><?php esc_html_e('Field', 'formorbit'); ?></button><button type="button" data-panel="confirmation"><?php esc_html_e('Confirmation', 'formorbit'); ?></button><?php do_action('webform_builder_property_tabs', $form_id, $settings); ?><button type="button" data-panel="access"><?php esc_html_e('Access', 'formorbit'); ?></button><button type="button" data-panel="style"><?php esc_html_e('Style', 'formorbit'); ?></button></div>
                    <div class="webform-property-panel is-active" data-panel="field"><h2><?php esc_html_e('Field settings', 'formorbit'); ?></h2><div id="webform-field-settings"><p class="description"><?php esc_html_e('Select a field to edit its options.', 'formorbit'); ?></p></div></div>
                    <div class="webform-property-panel" data-panel="confirmation"><h2><?php esc_html_e('Confirmation', 'formorbit'); ?></h2>
                    <?php $confirmation_types = apply_filters('webform_confirmation_types', array('message' => __('Show confirmation message', 'formorbit'), 'redirect' => __('Redirect to URL', 'formorbit'))); ?>
                    <label><?php esc_html_e('After submission', 'formorbit'); ?><select id="webform-confirmation-type"><?php foreach ($confirmation_types as $confirmation_key => $confirmation_label) : ?><option value="<?php echo esc_attr($confirmation_key); ?>" <?php selected($settings['confirmation_type'] ?? 'message', $confirmation_key); ?>><?php echo esc_html($confirmation_label); ?></option><?php endforeach; ?></select></label>
                    <div class="webform-confirmation-option" data-confirmation-option="message"><label><?php esc_html_e('Success message', 'formorbit'); ?></label><?php if (has_action('webform_confirmation_message_editor')) : do_action('webform_confirmation_message_editor', $settings); else : ?><textarea id="webform-success-message" rows="3"><?php echo esc_textarea(isset($settings['success_message']) ? $settings['success_message'] : __('Thanks! Your response has been submitted.', 'formorbit')); ?></textarea><?php endif; ?></div>
                    <label><?php esc_html_e('Admin notification email', 'formorbit'); ?><input type="email" id="webform-notification-email" value="<?php echo esc_attr(isset($settings['notification_email']) ? $settings['notification_email'] : get_option('admin_email')); ?>"><small><?php esc_html_e('Receives the standard submission notice and optional admin PDF.', 'formorbit'); ?></small></label>
                    <label><?php esc_html_e('Submit button text', 'formorbit'); ?><input type="text" id="webform-submit-label" value="<?php echo esc_attr(isset($settings['submit_label']) ? $settings['submit_label'] : __('Submit', 'formorbit')); ?>"></label>
                    <div class="webform-confirmation-option" data-confirmation-option="redirect"><label><?php esc_html_e('Redirect URL', 'formorbit'); ?><input type="url" id="webform-redirect-url" value="<?php echo esc_attr($settings['redirect_url'] ?? ''); ?>"></label></div>
                    <?php do_action('webform_builder_confirmation_controls', $form_id, $settings); ?>
                    </div><div class="webform-property-panel" data-panel="access"><h2><?php esc_html_e('Access and limits', 'formorbit'); ?></h2>
                    <label class="webform-check"><input type="checkbox" id="webform-require-login" <?php checked(!empty($settings['require_login'])); ?>> <?php esc_html_e('Require visitors to log in', 'formorbit'); ?></label>
                    <label><?php esc_html_e('Maximum total entries', 'formorbit'); ?><input type="number" min="0" id="webform-submission-limit" value="<?php echo esc_attr(absint($settings['submission_limit'] ?? 0)); ?>"><small><?php esc_html_e('Use 0 for unlimited.', 'formorbit'); ?></small></label>
                    <label><?php esc_html_e('Closed form message', 'formorbit'); ?><textarea id="webform-closed-message" rows="3"><?php echo esc_textarea($settings['closed_message'] ?? __('This form is currently unavailable.', 'formorbit')); ?></textarea></label>
                    <?php do_action('webform_builder_access_controls', $settings, $form_id); ?>
                    </div><div class="webform-property-panel" data-panel="style"><h2><?php esc_html_e('Appearance', 'formorbit'); ?></h2>
                    <?php $free_presets = array('modern' => __('Modern', 'formorbit'), 'minimal' => __('Minimal', 'formorbit'), 'rounded' => __('Rounded', 'formorbit')); $all_presets = apply_filters('webform_style_presets', $free_presets); ?>
                    <label><?php esc_html_e('Style preset', 'formorbit'); ?><span class="webform-preset-picker"><select id="webform-style-preset"><?php foreach ($all_presets as $preset_key => $preset_label) : ?><option value="<?php echo esc_attr($preset_key); ?>" <?php selected($settings['style_preset'] ?? 'modern', $preset_key); ?>><?php echo esc_html($preset_label); ?></option><?php endforeach; ?></select></span></label>
                    <label><?php esc_html_e('Accent color', 'formorbit'); ?><input type="color" id="webform-accent-color" value="<?php echo esc_attr($settings['accent_color'] ?? '#6c4bd4'); ?>"></label>
                    <label><?php esc_html_e('Button text color', 'formorbit'); ?><input type="color" id="webform-button-text-color" value="<?php echo esc_attr($settings['button_text_color'] ?? '#ffffff'); ?>"></label>
                    <?php do_action('webform_builder_style_controls', $settings); ?>
                    </div>
                    <?php do_action('webform_builder_property_panels', $form_id, $settings); ?>
                </aside>
            </div>
            <?php if (!$form_id && !$template_key) : ?><div class="webform-template-modal" id="webform-template-modal" role="dialog" aria-modal="true" aria-labelledby="webform-template-title"><div class="webform-template-dialog"><button type="button" class="webform-template-close" aria-label="<?php esc_attr_e('Close', 'formorbit'); ?>">×</button><h2 id="webform-template-title"><?php esc_html_e('Choose a starting template', 'formorbit'); ?></h2><p><?php esc_html_e('Select a template or start from a blank form.', 'formorbit'); ?></p><div class="webform-template-modal-grid"><a class="webform-template-choice" href="#"><strong><?php esc_html_e('Blank Form', 'formorbit'); ?></strong><span><?php esc_html_e('Build from scratch', 'formorbit'); ?></span></a><?php foreach ($this->free_templates() as $key => $template) : ?><a class="webform-template-choice" href="<?php echo esc_url(admin_url('admin.php?page=formorbit-builder&template=' . $key)); ?>"><strong><?php echo esc_html($template['name']); ?></strong><span><?php echo esc_html($template['description']); ?></span></a><?php endforeach; ?></div></div></div><?php endif; ?>
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
        <div class="wrap webform-wrap"><div class="webform-page-head"><div><h1><?php esc_html_e('Entries', 'formorbit'); ?></h1><p><?php esc_html_e('Review, filter, export, or remove submissions.', 'formorbit'); ?></p></div>
        <a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=formorbit_export_entries&form_id=' . $form_filter), 'webform_export_entries')); ?>"><?php esc_html_e('Export CSV', 'formorbit'); ?></a></div>
        <form method="get" class="webform-entry-filter"><input type="hidden" name="page" value="formorbit-entries"><select name="form_id"><option value="0"><?php esc_html_e('All forms', 'formorbit'); ?></option><?php foreach ($forms as $form) : ?><option value="<?php echo esc_attr($form->ID); ?>" <?php selected($form_filter, $form->ID); ?>><?php echo esc_html($form->post_title); ?></option><?php endforeach; ?></select><button class="button"><?php esc_html_e('Filter', 'formorbit'); ?></button></form>
        <div class="webform-card"><table class="widefat striped"><thead><tr><th><?php esc_html_e('Form', 'formorbit'); ?></th><th><?php esc_html_e('Status', 'formorbit'); ?></th><th><?php esc_html_e('Submitted data', 'formorbit'); ?></th><th><?php esc_html_e('Date', 'formorbit'); ?></th><th></th></tr></thead><tbody>
        <?php if (!$entries) : ?><tr><td colspan="5"><?php esc_html_e('No entries yet.', 'formorbit'); ?></td></tr><?php endif; ?>
        <?php foreach ($entries as $entry) : $data = get_post_meta($entry->ID, '_webform_data', true); $form_id = get_post_meta($entry->ID, '_webform_form_id', true); ?>
            <tr><td><?php echo esc_html(get_the_title($form_id)); ?></td><td><?php $entry_status = get_post_meta($entry->ID, '_webform_entry_status', true) ?: 'submitted'; ?><span class="webform-entry-status webform-entry-status-<?php echo esc_attr($entry_status); ?>"><?php echo esc_html($entry_status === 'draft' ? __('Draft', 'formorbit') : __('Submitted', 'formorbit')); ?></span></td><td><?php foreach ((array) $data as $key => $item) : $item = is_array($item) && isset($item['label']) ? $item : array('label' => $key, 'value' => $item); ?><div><strong><?php echo esc_html($item['label']); ?>:</strong> <?php echo esc_html(is_array($item['value']) ? implode(', ', $item['value']) : $item['value']); ?></div><?php endforeach; ?></td><td><?php echo esc_html(get_the_date('', $entry)); ?></td><td><a class="button-link-delete" onclick="return confirm('<?php echo esc_js(__('Permanently delete this entry?', 'formorbit')); ?>')" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=formorbit_delete_entry&entry_id=' . $entry->ID), 'webform_delete_entry_' . $entry->ID)); ?>"><?php esc_html_e('Delete', 'formorbit'); ?></a></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
        <?php echo wp_kses_post((string) paginate_links(array('base' => add_query_arg('paged', '%#%'), 'format' => '', 'current' => $paged, 'total' => $query->max_num_pages, 'type' => 'list'))); ?></div>
        <?php
    }

    public function save_form() {
        check_ajax_referer('webform_admin', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'formorbit')), 403);
        }
        $form_id = isset($_POST['form_id']) ? absint($_POST['form_id']) : 0;
        if ($form_id && (get_post_type($form_id) !== 'webform_form' || !current_user_can('edit_post', $form_id))) {
            wp_send_json_error(array('message' => __('Form not found or permission denied.', 'formorbit')), 403);
        }
        $schema_json = isset($_POST['schema']) ? wp_unslash($_POST['schema']) : '[]';
        $settings_json = isset($_POST['settings']) ? wp_unslash($_POST['settings']) : '{}';
        $decoded_schema = json_decode($schema_json, true);
        $decoded_settings = json_decode($settings_json, true);
        if (!is_array($decoded_schema) || !is_array($decoded_settings)) {
            wp_send_json_error(array('message' => __('Invalid form data.', 'formorbit')), 400);
        }
        $schema = $this->sanitize_schema($decoded_schema);
        $settings = map_deep($decoded_settings, array($this, 'sanitize_decoded_setting'));
        if (count($schema) > 20) {
            wp_send_json_error(array('message' => __('A form may contain up to 20 stages.', 'formorbit')), 400);
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
            'submit_label' => sanitize_text_field($settings['submit_label'] ?? __('Submit', 'formorbit')),
            'redirect_url' => esc_url_raw($settings['redirect_url'] ?? ''),
            'require_login' => !empty($settings['require_login']),
            'submission_limit' => absint($settings['submission_limit'] ?? 0),
            'closed_message' => sanitize_textarea_field($settings['closed_message'] ?? __('This form is currently unavailable.', 'formorbit')),
            'style_preset' => array_key_exists($settings['style_preset'] ?? '', apply_filters('webform_style_presets', array('modern' => 'Modern', 'minimal' => 'Minimal', 'rounded' => 'Rounded'))) ? $settings['style_preset'] : 'modern',
            'accent_color' => sanitize_hex_color($settings['accent_color'] ?? '') ?: '#6c4bd4',
            'button_text_color' => sanitize_hex_color($settings['button_text_color'] ?? '') ?: '#ffffff',
        );
        update_post_meta($form_id, '_webform_settings', apply_filters('webform_sanitize_form_settings', $clean_settings, $settings, $form_id));
        wp_send_json_success(array('id' => $form_id, 'message' => __('Saved', 'formorbit'), 'shortcode' => '[formorbit id="' . $form_id . '"]'));
    }

    public function sanitize_decoded_setting($value) {
        return is_string($value) ? wp_kses_post($value) : $value;
    }

    private function sanitize_schema($schema) {
        $clean = array();
        $base_types = array('name', 'text', 'email', 'textarea', 'select', 'radio', 'checkbox', 'number', 'date', 'time', 'phone', 'url', 'file', 'consent', 'poll', 'quiz', 'rating', 'slider', 'hidden', 'html', 'captcha', 'heading');
        $allowed_types = array_unique(array_merge($base_types, apply_filters('webform_allowed_field_types', $base_types)));
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
                    'hide_label' => !empty($field['hide_label']),
                    'required' => !empty($field['required']),
                    'row_start' => !empty($field['row_start']),
                    'options' => array_slice(array_values(array_filter(array_map('sanitize_text_field', (array) ($field['options'] ?? array())))), 0, 100),
                    'choice_columns' => min(4, max(1, absint($field['choice_columns'] ?? 1))),
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
                    'phone_country' => in_array(strtoupper($field['phone_country'] ?? 'US'), array('US', 'CA', 'GB', 'AU', 'BD', 'IN', 'PK', 'AE', 'SA'), true) ? strtoupper($field['phone_country']) : 'US',
                    'phone_country_selector' => !isset($field['phone_country_selector']) || !empty($field['phone_country_selector']),
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
            wp_send_json_error(array('message' => __('Permission denied.', 'formorbit')), 403);
        }
        $form_id = isset($_POST['form_id']) ? absint($_POST['form_id']) : 0;
        if (!$form_id || get_post_type($form_id) !== 'webform_form') {
            wp_send_json_error(array('message' => __('Form not found.', 'formorbit')), 404);
        }
        wp_trash_post($form_id);
        wp_send_json_success();
    }

    public function restore_form() {
        if (!current_user_can('manage_options')) wp_die(esc_html__('Permission denied.', 'formorbit'));
        $form_id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;
        check_admin_referer('webform_restore_' . $form_id);
        if (!$form_id || get_post_type($form_id) !== 'webform_form' || get_post_status($form_id) !== 'trash') wp_die(esc_html__('Trashed form not found.', 'formorbit'));
        wp_untrash_post($form_id);
        wp_safe_redirect(admin_url('admin.php?page=formorbit&form_status=trash'));
        exit;
    }

    public function permanently_delete_form() {
        if (!current_user_can('manage_options')) wp_die(esc_html__('Permission denied.', 'formorbit'));
        $form_id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;
        check_admin_referer('webform_permanently_delete_' . $form_id);
        if (!$form_id || get_post_type($form_id) !== 'webform_form' || get_post_status($form_id) !== 'trash') wp_die(esc_html__('Trashed form not found.', 'formorbit'));
        $entry_ids = get_posts(array('post_type' => 'webform_entry', 'post_status' => 'private', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_webform_form_id', 'meta_value' => $form_id));
        foreach ($entry_ids as $entry_id) wp_delete_post($entry_id, true);
        wp_delete_post($form_id, true);
        wp_safe_redirect(admin_url('admin.php?page=formorbit&form_status=trash'));
        exit;
    }

    public function duplicate_form() {
        if (!current_user_can('manage_options')) wp_die(esc_html__('Permission denied.', 'formorbit'));
        $form_id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;
        check_admin_referer('webform_duplicate_' . $form_id);
        if (get_post_type($form_id) !== 'webform_form') wp_die(esc_html__('Form not found.', 'formorbit'));
        $copy_id = wp_insert_post(array('post_type' => 'webform_form', 'post_status' => 'publish', 'post_title' => sprintf(__('%s (Copy)', 'formorbit'), get_the_title($form_id))));
        update_post_meta($copy_id, '_webform_schema', get_post_meta($form_id, '_webform_schema', true));
        update_post_meta($copy_id, '_webform_settings', get_post_meta($form_id, '_webform_settings', true));
        wp_safe_redirect(admin_url('admin.php?page=formorbit-builder&form_id=' . $copy_id));
        exit;
    }

    public function delete_entry() {
        if (!current_user_can('manage_options')) wp_die(esc_html__('Permission denied.', 'formorbit'));
        $entry_id = isset($_GET['entry_id']) ? absint($_GET['entry_id']) : 0;
        check_admin_referer('webform_delete_entry_' . $entry_id);
        if (get_post_type($entry_id) === 'webform_entry') wp_delete_post($entry_id, true);
        wp_safe_redirect(wp_get_referer() ?: admin_url('admin.php?page=formorbit-entries'));
        exit;
    }

    public function export_entries() {
        if (!current_user_can('manage_options')) wp_die(esc_html__('Permission denied.', 'formorbit'));
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
        if ($page !== 'formorbit-builder') return;
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
    }

    public function settings_page() {
        if (!current_user_can('manage_options')) return;
        $stored_settings = (array) get_option('webform_global_settings', array());
        $profile_status = (array) get_option('formorbit_site_profile_status', array());
        $default_recaptcha_mode = !empty($stored_settings['recaptcha_secret_key']) ? 'classic' : 'enterprise';
        $settings = wp_parse_args($stored_settings, array('recaptcha_enabled' => false, 'recaptcha_mode' => $default_recaptcha_mode, 'recaptcha_site_key' => '', 'recaptcha_secret_key' => '', 'recaptcha_project_id' => '', 'recaptcha_api_key' => '', 'recaptcha_action' => 'WEBFORM_SUBMIT', 'usage_sharing_enabled' => false));
        ?>
        <div class="wrap webform-wrap"><div class="webform-page-head"><div><h1><?php esc_html_e('FormOrbit Settings', 'formorbit'); ?></h1><p><?php esc_html_e('Global security and service configuration.', 'formorbit'); ?></p></div></div>
        <form class="webform-settings-card webform-recaptcha-settings" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post"><input type="hidden" name="action" value="formorbit_save_global_settings"><?php wp_nonce_field('webform_save_global_settings'); ?>
            <div class="webform-settings-card-head"><span class="dashicons dashicons-shield"></span><div><h2><?php esc_html_e('Google reCAPTCHA', 'formorbit'); ?></h2><p><?php esc_html_e('Protect forms with a Google Cloud checkbox key or a compatible classic v2 key.', 'formorbit'); ?></p></div></div>
            <label class="webform-settings-toggle"><input type="checkbox" name="recaptcha_enabled" value="1" <?php checked(!empty($settings['recaptcha_enabled'])); ?>><span><?php esc_html_e('Enable Google reCAPTCHA on CAPTCHA fields', 'formorbit'); ?></span></label>
            <label><?php esc_html_e('Integration type', 'formorbit'); ?><select name="recaptcha_mode" id="webform-recaptcha-mode"><option value="enterprise" <?php selected($settings['recaptcha_mode'], 'enterprise'); ?>><?php esc_html_e('Google Cloud reCAPTCHA (recommended)', 'formorbit'); ?></option><option value="classic" <?php selected($settings['recaptcha_mode'], 'classic'); ?>><?php esc_html_e('Classic or migrated v2 compatibility', 'formorbit'); ?></option></select></label>
            <label><?php esc_html_e('Site key', 'formorbit'); ?><input name="recaptcha_site_key" value="<?php echo esc_attr($settings['recaptcha_site_key']); ?>" autocomplete="off"><small><?php esc_html_e('This is the value used in the data-sitekey attribute Google provides.', 'formorbit'); ?></small></label>
            <div class="webform-recaptcha-panel" data-mode="enterprise"><label><?php esc_html_e('Google Cloud project ID', 'formorbit'); ?><input name="recaptcha_project_id" value="<?php echo esc_attr($settings['recaptcha_project_id']); ?>" autocomplete="off"></label><label><?php esc_html_e('Google Cloud API key', 'formorbit'); ?><input type="password" name="recaptcha_api_key" value="<?php echo esc_attr($settings['recaptcha_api_key']); ?>" autocomplete="new-password"><small><?php esc_html_e('Use a restricted server API key with the reCAPTCHA Enterprise API enabled.', 'formorbit'); ?></small></label><label><?php esc_html_e('Expected action', 'formorbit'); ?><input name="recaptcha_action" value="<?php echo esc_attr($settings['recaptcha_action']); ?>" pattern="[A-Za-z0-9_/-]+"><small><?php esc_html_e('The frontend and backend must use the same action.', 'formorbit'); ?></small></label></div>
            <div class="webform-recaptcha-panel" data-mode="classic"><label><?php esc_html_e('Secret key', 'formorbit'); ?><input type="password" name="recaptcha_secret_key" value="<?php echo esc_attr($settings['recaptcha_secret_key']); ?>" autocomplete="new-password"></label><p class="description"><?php esc_html_e('Migrated classic keys can continue using SiteVerify. New Google Cloud keys should use the recommended mode above.', 'formorbit'); ?></p></div>
            <div class="webform-settings-card-head"><span class="dashicons dashicons-update"></span><div><h2><?php esc_html_e('Compatibility profile', 'formorbit'); ?></h2><p><?php esc_html_e('Help Web Ninja LLC improve FormOrbit updates, compatibility, and support for your WordPress setup.', 'formorbit'); ?></p></div></div>
            <label class="webform-settings-toggle"><input type="checkbox" name="usage_sharing_enabled" value="1" <?php checked(!empty($settings['usage_sharing_enabled'])); ?>><span><?php esc_html_e('Share a basic compatibility profile with Web Ninja LLC', 'formorbit'); ?></span></label>
            <p class="description"><?php esc_html_e('Includes basic website and software details only. Forms, entries, passwords, payment information, and visitor data are never included.', 'formorbit'); ?></p>
            <?php if (!empty($profile_status['last_sent'])) : ?><p class="description"><strong><?php esc_html_e('Compatibility check:', 'formorbit'); ?></strong> <?php echo esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), absint($profile_status['last_sent']))); ?><?php foreach ((array) ($profile_status['results'] ?? array()) as $product => $result) : ?> · <?php echo esc_html($product); ?>: <?php echo !empty($result['success']) ? esc_html__('Connected', 'formorbit') : esc_html(sprintf(__('Not connected (HTTP %d)', 'formorbit'), absint($result['code'] ?? 0))); ?><?php if (empty($result['success']) && !empty($result['message'])) : ?> — <?php echo esc_html($result['message']); ?><?php endif; ?><?php endforeach; ?></p><?php endif; ?>
            <button class="button button-primary"><?php esc_html_e('Save settings', 'formorbit'); ?></button>
        </form></div>
        <?php
    }

    public function save_global_settings() {
        if (!current_user_can('manage_options')) wp_die(esc_html__('Permission denied.', 'formorbit'));
        check_admin_referer('webform_save_global_settings');
        $usage_sharing_enabled = !empty($_POST['usage_sharing_enabled']);
        update_option('webform_global_settings', array(
            'recaptcha_enabled' => !empty($_POST['recaptcha_enabled']),
            'recaptcha_mode' => in_array($_POST['recaptcha_mode'] ?? '', array('enterprise', 'classic'), true) ? sanitize_key(wp_unslash($_POST['recaptcha_mode'])) : 'enterprise',
            'recaptcha_site_key' => sanitize_text_field(wp_unslash($_POST['recaptcha_site_key'] ?? '')),
            'recaptcha_secret_key' => sanitize_text_field(wp_unslash($_POST['recaptcha_secret_key'] ?? '')),
            'recaptcha_project_id' => sanitize_text_field(wp_unslash($_POST['recaptcha_project_id'] ?? '')),
            'recaptcha_api_key' => sanitize_text_field(wp_unslash($_POST['recaptcha_api_key'] ?? '')),
            'recaptcha_action' => preg_replace('/[^A-Za-z0-9_\\/-]/', '', wp_unslash($_POST['recaptcha_action'] ?? 'WEBFORM_SUBMIT')),
            'usage_sharing_enabled' => $usage_sharing_enabled,
        ), false);
        if ($usage_sharing_enabled) do_action('formorbit_site_profile_sync');
        wp_safe_redirect(admin_url('admin.php?page=formorbit-settings'));
        exit;
    }

    public function tools_page() {
        if (!current_user_can('manage_options')) return;
        $active_tab = isset($_GET['tab']) && sanitize_key(wp_unslash($_GET['tab'])) === 'resources' ? 'resources' : 'transfer';
        $forms = get_posts(array('post_type' => 'webform_form', 'post_status' => array('publish', 'draft'), 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC'));
        ?>
        <div class="wrap webform-wrap webform-tools-wrap"><div class="webform-page-head"><div><h1><?php esc_html_e('FormOrbit Tools', 'formorbit'); ?></h1><p><?php esc_html_e('Move forms, access product resources, and get help in one place.', 'formorbit'); ?></p></div></div>
        <nav class="nav-tab-wrapper webform-tools-tabs" aria-label="<?php esc_attr_e('FormOrbit tools', 'formorbit'); ?>">
            <a class="nav-tab <?php echo esc_attr($active_tab === 'transfer' ? 'nav-tab-active' : ''); ?>" href="<?php echo esc_url(admin_url('admin.php?page=formorbit-tools')); ?>"><span class="dashicons dashicons-migrate" aria-hidden="true"></span><?php esc_html_e('Import / Export', 'formorbit'); ?></a>
            <a class="nav-tab <?php echo esc_attr($active_tab === 'resources' ? 'nav-tab-active' : ''); ?>" href="<?php echo esc_url(admin_url('admin.php?page=formorbit-tools&tab=resources')); ?>"><span class="dashicons dashicons-admin-links" aria-hidden="true"></span><?php esc_html_e('Resources', 'formorbit'); ?></a>
        </nav>
        <?php if ($active_tab === 'transfer') : ?><div class="webform-transfer-grid">
            <form class="webform-settings-card" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="formorbit_import"><?php wp_nonce_field('webform_import'); ?>
                <h2><?php esc_html_e('Import a form', 'formorbit'); ?></h2>
                <label><?php esc_html_e('Source', 'formorbit'); ?><select name="source"><option value="auto"><?php esc_html_e('Detect automatically', 'formorbit'); ?></option><option value="webform">FormOrbit</option><option value="wpforms">WPForms</option><option value="gravity">Gravity Forms</option><option value="fluent">Fluent Forms</option><option value="formidable">Formidable Forms</option><option value="forminator">Forminator</option><option value="cf7">Contact Form 7</option></select></label>
                <label><?php esc_html_e('Import file', 'formorbit'); ?><input type="file" name="import_file" accept=".json,.csv,.xml,.txt,application/json,text/csv,application/xml,text/xml,text/plain"></label>
                <p class="description"><?php esc_html_e('Supported formats: JSON, CSV, XML, and Contact Form 7 text markup. Maximum file size: 5 MB.', 'formorbit'); ?></p>
                <label><?php esc_html_e('Or paste exported content', 'formorbit'); ?><textarea name="import_content" rows="10"></textarea></label>
                <button class="button button-primary"><?php esc_html_e('Import and edit', 'formorbit'); ?></button>
            </form>
            <div><?php do_action('webform_import_export_tools', $forms); ?></div>
        </div><?php else : ?>
            <div class="webform-tools-resource-grid">
                <article class="webform-tool-resource-card"><span class="dashicons dashicons-welcome-learn-more" aria-hidden="true"></span><div><h2><?php esc_html_e('FormOrbit product page', 'formorbit'); ?></h2><p><?php esc_html_e('Explore FormOrbit Pro features, integrations, licensing plans, and product information.', 'formorbit'); ?></p><a class="button" href="<?php echo esc_url('https://www.webninjallc.com/plugins/formorbit/'); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Visit FormOrbit', 'formorbit'); ?><span class="dashicons dashicons-external" aria-hidden="true"></span></a></div></article>
                <?php do_action('formorbit_tools_resources'); ?>
            </div>
        <?php endif; ?></div>
        <?php
    }

    public function import_page() {
        $this->tools_page();
    }

    public function import_form() {
        if (!current_user_can('manage_options')) wp_die(esc_html__('Permission denied.', 'formorbit'));
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
        if (!$content) wp_die(esc_html__('No import content was provided.', 'formorbit'));
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
                if (!is_array($decoded)) wp_die(esc_html__('The import file is not valid JSON.', 'formorbit'));
                if ($source === 'forminator' || ($source === 'auto' && $this->looks_like_forminator($decoded))) {
                    $converted = $this->convert_forminator($decoded);
                } elseif ($source === 'formidable' || ($source === 'auto' && $this->looks_like_formidable($decoded))) {
                    $converted = $this->convert_formidable($decoded);
                } else {
                    $converted = $this->convert_json_form($decoded);
                }
            }
        }
        if (empty($converted['schema']) || !$this->import_has_fields($converted['schema'])) wp_die(esc_html__('No supported fields were found in the export. Confirm the correct source plugin is selected and export the form structure rather than its entries.', 'formorbit'));
        $form_id = wp_insert_post(array('post_type' => 'webform_form', 'post_status' => 'publish', 'post_title' => sanitize_text_field($converted['name'] ?: __('Imported Form', 'formorbit'))));
        update_post_meta($form_id, '_webform_schema', $this->sanitize_schema($converted['schema']));
        update_post_meta($form_id, '_webform_settings', $this->sanitize_import_settings($converted['settings'] ?? array()));
        wp_safe_redirect(admin_url('admin.php?page=formorbit-builder&form_id=' . $form_id));
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
            wp_die(esc_html__('The CSV must include stage_title, field_type, and label columns.', 'formorbit'));
        }
        $stages = array();
        $name = __('Imported Form', 'formorbit');
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
            if (!isset($stages[$stage_key])) $stages[$stage_key] = array('id' => $stage_key, 'title' => $stage_title ?: __('Imported Form', 'formorbit'), 'fields' => array());
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
            wp_die(esc_html__('XML support is not available on this server.', 'formorbit'));
        }
        $xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
        if ($xml === false) wp_die(esc_html__('The import file is not valid XML.', 'formorbit'));
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
        $name = !empty($name_nodes) ? sanitize_text_field((string) $name_nodes[0]) : __('Imported Formidable Form', 'formorbit');
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
        $name = sanitize_text_field($node['name'] ?? ($node['settings']['formName'] ?? ($node['settings']['form_name'] ?? __('Imported Forminator Form', 'formorbit'))));
        $fields = $node['fields'] ?? array();
        if (!$fields && !empty($node['wrappers'])) $fields = $this->flatten_import_fields($node['wrappers']);
        return $this->build_imported_form($name, $fields, 'forminator');
    }

    private function convert_formidable($data) {
        $node = $this->find_form_node($data);
        if (!empty($data['forms'][0]) && is_array($data['forms'][0])) $node = $data['forms'][0];
        $name = sanitize_text_field($node['name'] ?? ($node['form_key'] ?? ($node['title'] ?? __('Imported Formidable Form', 'formorbit'))));
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
                $stages[] = array('id' => 'stage_' . (count($stages) + 1), 'title' => sanitize_text_field($this->import_scalar($field['field_label'] ?? ($field['name'] ?? sprintf(__('Stage %d', 'formorbit'), count($stages) + 1)))), 'fields' => array());
                continue;
            }
            $choices = $field['options'] ?? ($field['choices'] ?? array());
            if (isset($field_options['options'])) $choices = $field_options['options'];
            $normalized_choices = $this->normalize_import_choices($choices);
            $stages[count($stages) - 1]['fields'][] = array(
                'id' => sanitize_key($this->import_scalar($field['element_id'] ?? ($field['field_key'] ?? ($field['id'] ?? $key)))) ?: 'field_' . wp_generate_password(6, false, false),
                'type' => $this->map_import_type($raw_type),
                'label' => sanitize_text_field($this->import_scalar($field['field_label'] ?? ($field['name'] ?? ($field['label'] ?? __('Imported Field', 'formorbit'))))),
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
        $map = array('name' => 'name', 'first_name' => 'text', 'last_name' => 'text', 'input' => 'text', 'phone' => 'phone', 'tel' => 'phone', 'email' => 'email', 'textarea' => 'textarea', 'paragraph' => 'textarea', 'select' => 'select', 'dropdown' => 'select', 'radio' => 'radio', 'checkbox' => 'checkbox', 'number' => 'number', 'date' => 'date', 'time' => 'time', 'url' => 'url', 'website' => 'url', 'file' => 'file', 'upload' => 'file', 'html' => 'html', 'section' => 'heading', 'divider' => 'heading', 'heading' => 'heading', 'rating' => 'rating', 'star' => 'rating', 'scale' => 'slider', 'slider' => 'slider', 'hidden' => 'hidden', 'consent' => 'consent', 'captcha' => 'captcha');
        return $map[$type] ?? 'text';
    }

    private function sanitize_import_settings($settings) {
        $defaults = array('success_message' => __('Thanks! Your response has been submitted.', 'formorbit'), 'notification_email' => get_option('admin_email'), 'submit_label' => __('Submit', 'formorbit'), 'confirmation_type' => 'message', 'style_preset' => 'modern', 'accent_color' => '#6c4bd4', 'button_text_color' => '#ffffff');
        $settings = wp_parse_args((array) $settings, $defaults);
        $confirmation_types = array_keys(apply_filters('webform_confirmation_types', array('message' => 'Message', 'redirect' => 'Redirect')));
        $confirmation_type = in_array($settings['confirmation_type'], $confirmation_types, true) ? $settings['confirmation_type'] : 'message';
        $clean = array('success_message' => wp_kses_post($settings['success_message']), 'notification_email' => sanitize_email($settings['notification_email']), 'submit_label' => sanitize_text_field($settings['submit_label']), 'confirmation_type' => $confirmation_type, 'redirect_url' => esc_url_raw($settings['redirect_url'] ?? ''), 'require_login' => !empty($settings['require_login']), 'submission_limit' => absint($settings['submission_limit'] ?? 0), 'style_preset' => sanitize_key($settings['style_preset']), 'accent_color' => sanitize_hex_color($settings['accent_color']) ?: '#6c4bd4', 'button_text_color' => sanitize_hex_color($settings['button_text_color']) ?: '#ffffff');
        return apply_filters('webform_sanitize_form_settings', $clean, $settings, 0);
    }

    private function convert_json_form($data) {
        if (isset($data['webform_export_version'], $data['form'])) {
            $form = (array) $data['form'];
            return array('name' => sanitize_text_field($form['name'] ?? __('Imported Form', 'formorbit')), 'schema' => (array) ($form['schema'] ?? array()), 'settings' => (array) ($form['settings'] ?? array()));
        }
        $node = $this->find_form_node($data);
        $name = sanitize_text_field($node['title'] ?? ($node['name'] ?? ($node['settings']['form_title'] ?? __('Imported Form', 'formorbit'))));
        $source_fields = $node['fields'] ?? ($node['form_fields'] ?? array());
        if (is_string($source_fields)) $source_fields = json_decode($source_fields, true);
        $stages = array(array('id' => 'stage_imported', 'title' => __('Imported Form', 'formorbit'), 'fields' => array()));
        foreach ((array) $source_fields as $key => $source) {
            if (!is_array($source)) continue;
            $type = sanitize_key($source['type'] ?? ($source['element'] ?? 'text'));
            if (in_array($type, array('page', 'pagebreak', 'step'), true)) {
                $stages[] = array('id' => 'stage_' . count($stages), 'title' => sanitize_text_field($source['label'] ?? sprintf(__('Stage %d', 'formorbit'), count($stages) + 1)), 'fields' => array());
                continue;
            }
            $type = $this->map_import_type($type);
            $choices = $this->normalize_import_choices($source['choices'] ?? ($source['options'] ?? array()));
            $stages[count($stages) - 1]['fields'][] = array('id' => sanitize_key($source['id'] ?? $key) ?: 'field_' . wp_generate_password(6, false, false), 'type' => $type, 'label' => sanitize_text_field($source['label'] ?? ($source['adminLabel'] ?? __('Imported Field', 'formorbit'))), 'placeholder' => sanitize_text_field($source['placeholder'] ?? ''), 'required' => !empty($source['required']) || !empty($source['isRequired']), 'options' => array_filter($choices));
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
        return array('name' => __('Imported Contact Form', 'formorbit'), 'schema' => array(array('id' => 'stage_imported', 'title' => __('Contact Form', 'formorbit'), 'fields' => $fields)));
    }

    public function templates_page() {
        if (!current_user_can('manage_options')) return;
        ?>
        <div class="wrap webform-wrap">
            <div class="webform-page-head"><div><h1><?php esc_html_e('Form Templates', 'formorbit'); ?></h1><p><?php esc_html_e('Start with a complete form, then customize every field and stage.', 'formorbit'); ?></p></div></div>
            <div class="webform-template-grid">
                <div class="webform-template-card"><span class="dashicons dashicons-plus-alt2"></span><h2><?php esc_html_e('Blank Form', 'formorbit'); ?></h2><p><?php esc_html_e('Start with an empty stage.', 'formorbit'); ?></p><a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=formorbit-builder')); ?>"><?php esc_html_e('Create', 'formorbit'); ?></a></div>
                <?php foreach ($this->free_templates() as $key => $template) : ?>
                    <div class="webform-template-card"><span class="dashicons <?php echo esc_attr($template['icon']); ?>"></span><h2><?php echo esc_html($template['name']); ?></h2><p><?php echo esc_html($template['description']); ?></p><a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=formorbit-builder&template=' . $key)); ?>"><?php esc_html_e('Use template', 'formorbit'); ?></a></div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    private function free_templates() {
        return apply_filters('webform_templates', array(
            'contact' => array('name' => __('Contact Form', 'formorbit'), 'description' => __('Name, email, phone, and message.', 'formorbit'), 'icon' => 'dashicons-email-alt', 'schema' => array($this->template_stage(__('Contact Us', 'formorbit'), array($this->template_field('name', 'text', __('Name', 'formorbit'), true), $this->template_field('email', 'email', __('Email', 'formorbit'), true), $this->template_field('phone', 'phone', __('Phone', 'formorbit')), $this->template_field('message', 'textarea', __('Message', 'formorbit'), true))))),
            'feedback' => array('name' => __('Customer Feedback', 'formorbit'), 'description' => __('Satisfaction poll and written feedback.', 'formorbit'), 'icon' => 'dashicons-format-chat', 'schema' => array($this->template_stage(__('Your Feedback', 'formorbit'), array($this->template_field('satisfaction', 'poll', __('How satisfied are you?', 'formorbit'), true, array(__('Very satisfied', 'formorbit'), __('Satisfied', 'formorbit'), __('Neutral', 'formorbit'), __('Dissatisfied', 'formorbit'))), $this->template_field('feedback', 'textarea', __('What can we improve?', 'formorbit')))))),
            'job-application' => array('name' => __('Job Application', 'formorbit'), 'description' => __('Applicant details, role, résumé, and consent.', 'formorbit'), 'icon' => 'dashicons-businessperson', 'schema' => array($this->template_stage(__('Applicant Details', 'formorbit'), array($this->template_field('name', 'text', __('Full name', 'formorbit'), true), $this->template_field('email', 'email', __('Email', 'formorbit'), true), $this->template_field('role', 'select', __('Position', 'formorbit'), true, array(__('Developer', 'formorbit'), __('Designer', 'formorbit'), __('Marketing', 'formorbit'))))), $this->template_stage(__('Application', 'formorbit'), array($this->template_field('resume', 'file', __('Résumé', 'formorbit'), true), $this->template_field('cover', 'textarea', __('Cover letter', 'formorbit')), $this->template_field('consent', 'consent', __('I consent to the processing of my application.', 'formorbit'), true))))),
            'event-registration' => array('name' => __('Event Registration', 'formorbit'), 'description' => __('Attendee information and session choice.', 'formorbit'), 'icon' => 'dashicons-calendar-alt', 'schema' => array($this->template_stage(__('Registration', 'formorbit'), array($this->template_field('name', 'text', __('Attendee name', 'formorbit'), true), $this->template_field('email', 'email', __('Email', 'formorbit'), true), $this->template_field('session', 'radio', __('Preferred session', 'formorbit'), true, array(__('Morning', 'formorbit'), __('Afternoon', 'formorbit'))), $this->template_field('notes', 'textarea', __('Accessibility or dietary needs', 'formorbit')))))),
            'quote-request' => array('name' => __('Request a Quote', 'formorbit'), 'description' => __('Project type, budget, timing, and requirements.', 'formorbit'), 'icon' => 'dashicons-money-alt', 'schema' => array($this->template_stage(__('Project', 'formorbit'), array($this->template_field('service', 'select', __('Service needed', 'formorbit'), true, array(__('Website', 'formorbit'), __('Ecommerce', 'formorbit'), __('Marketing', 'formorbit'), __('Other', 'formorbit'))), $this->template_field('budget', 'select', __('Budget range', 'formorbit'), true, array(__('Under $1,000', 'formorbit'), __('$1,000–$5,000', 'formorbit'), __('$5,000+', 'formorbit'))), $this->template_field('details', 'textarea', __('Project details', 'formorbit'), true))), $this->template_stage(__('Contact', 'formorbit'), array($this->template_field('name', 'text', __('Name', 'formorbit'), true), $this->template_field('email', 'email', __('Email', 'formorbit'), true))))),
            'newsletter' => array('name' => __('Newsletter Signup', 'formorbit'), 'description' => __('Simple email subscription with consent.', 'formorbit'), 'icon' => 'dashicons-megaphone', 'schema' => array($this->template_stage(__('Stay Updated', 'formorbit'), array($this->template_field('name', 'text', __('Name', 'formorbit')), $this->template_field('email', 'email', __('Email', 'formorbit'), true), $this->template_field('consent', 'consent', __('I agree to receive email updates.', 'formorbit'), true))))),
            'support-request' => array('name' => __('Support Request', 'formorbit'), 'description' => __('Issue details, priority, and attachment.', 'formorbit'), 'icon' => 'dashicons-sos', 'schema' => array($this->template_stage(__('Support Ticket', 'formorbit'), array($this->template_field('email', 'email', __('Email', 'formorbit'), true), $this->template_field('priority', 'select', __('Priority', 'formorbit'), true, array(__('Low', 'formorbit'), __('Normal', 'formorbit'), __('Urgent', 'formorbit'))), $this->template_field('issue', 'textarea', __('Describe the issue', 'formorbit'), true), $this->template_field('attachment', 'file', __('Screenshot or document', 'formorbit')))))),
            'survey' => array('name' => __('Product Survey', 'formorbit'), 'description' => __('Three quick polls with an open comment.', 'formorbit'), 'icon' => 'dashicons-chart-bar', 'schema' => array($this->template_stage(__('Product Survey', 'formorbit'), array($this->template_field('ease', 'poll', __('How easy is the product to use?', 'formorbit'), true, array('1', '2', '3', '4', '5')), $this->template_field('recommend', 'poll', __('Would you recommend it?', 'formorbit'), true, array(__('Yes', 'formorbit'), __('Maybe', 'formorbit'), __('No', 'formorbit'))), $this->template_field('favorite', 'textarea', __('What is your favorite feature?', 'formorbit')))))),
            'quiz' => array('name' => __('Simple Knowledge Quiz', 'formorbit'), 'description' => __('A ready-to-edit scored three-question quiz.', 'formorbit'), 'icon' => 'dashicons-welcome-learn-more', 'schema' => array($this->template_stage(__('Quick Quiz', 'formorbit'), array($this->template_field('q1', 'quiz', __('What is the capital of France?', 'formorbit'), true, array(__('Paris', 'formorbit'), __('Rome', 'formorbit'), __('Madrid', 'formorbit')), __('Paris', 'formorbit')), $this->template_field('q2', 'quiz', __('How many days are in a leap year?', 'formorbit'), true, array('365', '366', '367'), '366'), $this->template_field('q3', 'quiz', __('Which planet is known as the Red Planet?', 'formorbit'), true, array(__('Mars', 'formorbit'), __('Venus', 'formorbit'), __('Jupiter', 'formorbit')), __('Mars', 'formorbit')))))),
            'volunteer' => array('name' => __('Volunteer Registration', 'formorbit'), 'description' => __('Availability, interests, and contact details.', 'formorbit'), 'icon' => 'dashicons-groups', 'schema' => array($this->template_stage(__('Volunteer With Us', 'formorbit'), array($this->template_field('name', 'text', __('Name', 'formorbit'), true), $this->template_field('email', 'email', __('Email', 'formorbit'), true), $this->template_field('interests', 'checkbox', __('Areas of interest', 'formorbit'), true, array(__('Events', 'formorbit'), __('Fundraising', 'formorbit'), __('Community outreach', 'formorbit'))), $this->template_field('availability', 'textarea', __('Availability', 'formorbit')))))),
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
            __('Advanced fields', 'formorbit') => array(__('Calculations and formula builder', 'formorbit'), __('Field groups and repeatable rows', 'formorbit'), __('E-signatures and rich-text agreements', 'formorbit'), __('Addresses, appointments, NPS, currency, and dividers', 'formorbit'), __('WordPress post title, body, excerpt, tags, and custom fields', 'formorbit')),
            __('Payments and commerce', 'formorbit') => array(__('Products, quantities, options, shipping, donations, prices, and totals', 'formorbit'), __('Stripe hosted checkout', 'formorbit'), __('PayPal, Square, and bank transfer', 'formorbit'), __('Secure payment status and order workflows', 'formorbit')),
            __('Marketing and automation', 'formorbit') => array(__('Mailchimp, AWeber, Brevo, ActiveCampaign, Kit, and GetResponse', 'formorbit'), __('LeadConnector / GoHighLevel CRM routing', 'formorbit'), __('Zapier and signed webhooks', 'formorbit'), __('Twilio, Vonage, MessageBird, and Telnyx SMS', 'formorbit'), __('Google Calendar and Calendly appointment sync', 'formorbit')),
            __('Documents and communication', 'formorbit') => array(__('Personalized visitor confirmation emails', 'formorbit'), __('Immediate and scheduled email/SMS follow-ups', 'formorbit'), __('Admin and visitor PDF attachments', 'formorbit'), __('Save & Continue with secure resume links', 'formorbit')),
            __('Design and productivity', 'formorbit') => array(__('13+ premium style presets and Google Fonts', 'formorbit'), __('Submit button, spacing, color, typography, and custom CSS controls', 'formorbit'), __('Side-by-side responsive layouts and per-field icons', 'formorbit'), __('20 premium templates', 'formorbit'), __('Import a form from a public website URL', 'formorbit'), __('JSON, CSV, and XML form export', 'formorbit')),
            __('License and support', 'formorbit') => array(__('Automatic in-dashboard Pro updates', 'formorbit'), __('License-managed activations and secure downloads', 'formorbit'), __('Built-in support and feedback tools', 'formorbit'), __('Product documentation and detailed changelog', 'formorbit')),
        );
        $plans = array(
            array('name' => __('Pro Annual', 'formorbit'), 'price' => '$19.99', 'term' => __('per year', 'formorbit'), 'sites' => __('1 website', 'formorbit'), 'url' => $this->upgrade_url('plans-single'), 'featured' => false),
            array('name' => __('Pro Bundle', 'formorbit'), 'price' => '$99.99', 'term' => __('per year', 'formorbit'), 'sites' => __('Up to 10 websites', 'formorbit'), 'url' => $this->upgrade_url('plans-bundle'), 'featured' => true),
            array('name' => __('Pro Lifetime', 'formorbit'), 'price' => '$249.99', 'term' => __('one-time payment', 'formorbit'), 'sites' => __('1 website · lifetime license', 'formorbit'), 'url' => $this->upgrade_url('plans-lifetime'), 'featured' => false),
        );
        ?>
        <div class="wrap webform-wrap webform-pro-page">
            <div class="webform-pro-hero">
                <span class="webform-pro-badge"><?php esc_html_e('FORMORBIT PRO', 'formorbit'); ?></span>
                <h1><?php esc_html_e('Turn every form into a connected workflow', 'formorbit'); ?></h1>
                <p><?php esc_html_e('Keep everything in FormOrbit, then add payments, email marketing, automation, and advanced business tools.', 'formorbit'); ?></p>
                <p class="webform-pro-price-intro"><?php esc_html_e('Choose the license that fits your websites.', 'formorbit'); ?></p>
            </div>
            <div class="webform-plan-grid"><?php foreach ($plans as $plan) : ?><article class="webform-plan-card <?php echo $plan['featured'] ? 'is-featured' : ''; ?>"><?php if ($plan['featured']) : ?><span class="webform-plan-popular"><?php esc_html_e('BEST FOR AGENCIES', 'formorbit'); ?></span><?php endif; ?><h2><?php echo esc_html($plan['name']); ?></h2><div class="webform-plan-price"><?php echo esc_html($plan['price']); ?></div><p><?php echo esc_html($plan['term']); ?></p><strong><?php echo esc_html($plan['sites']); ?></strong><a class="button button-primary" href="<?php echo esc_url($plan['url']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Choose this plan', 'formorbit'); ?></a></article><?php endforeach; ?></div>
            <div class="webform-pro-showcase"><?php foreach ($features as $category => $category_features) : ?><section class="webform-pro-showcase-card"><h2><?php echo esc_html($category); ?></h2><ul><?php foreach ($category_features as $feature) : ?><li><span class="dashicons dashicons-yes-alt"></span><?php echo esc_html($feature); ?></li><?php endforeach; ?></ul></section><?php endforeach; ?></div>
            <p class="description"><?php esc_html_e('FormOrbit Pro will install as a separate licensed add-on. Your forms and entries remain compatible with the free plugin.', 'formorbit'); ?></p>
        </div>
        <?php
    }

    private function upgrade_url($source) {
        $url = add_query_arg(
            array(
                'utm_source' => 'formorbit-plugin',
                'utm_medium' => 'upgrade',
                'utm_campaign' => sanitize_key($source),
            ),
            'https://www.webninjallc.com/plugins/formorbit/'
        );
        return apply_filters('webform_upgrade_url', $url, $source);
    }

    private function is_pro_active() {
        return defined('WEBFORM_PRO_PLUGIN_VERSION') || defined('WEBFORM_PRO_VERSION') || (bool) apply_filters('webform_is_pro_active', false);
    }
}
