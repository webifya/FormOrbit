<?php

defined('ABSPATH') || exit; // FormOrbit Pro fields.

class FormOrbit_Fields {
    public function __construct() {
        add_filter('webform_field_palette', array($this, 'palette'));
        add_filter('webform_allowed_field_types', array($this, 'types'));
        add_filter('webform_sanitize_field', array($this, 'sanitize'), 10, 2);
        add_filter('webform_custom_field_html', array($this, 'render'), 10, 4);
        add_filter('webform_custom_field_allowed_html', array($this, 'allowed_html'), 10, 2);
        add_filter('webform_raw_submission_value', array($this, 'value'), 10, 5);
        add_filter('webform_process_field', array($this, 'process'), 10, 2);
        add_filter('webform_validate_field', array($this, 'validate'), 10, 5);
        add_filter('webform_style_presets', array($this, 'styles'));
        add_filter('webform_sanitize_form_settings', array($this, 'sanitize_style_settings'), 10, 2);
        add_filter('webform_inline_style_variables', array($this, 'style_variables'), 10, 2);
        add_filter('webform_field_custom_class', array($this, 'field_custom_class'), 10, 2);
        add_filter('webform_field_label_html', array($this, 'field_label_html'), 10, 2);
        add_filter('webform_templates', array($this, 'templates'));
        add_filter('webform_admin_script_data', array($this, 'admin_script_data'));
        add_action('webform_builder_style_controls', array($this, 'style_controls'));
        add_action('webform_before_form_markup', array($this, 'custom_css'), 10, 2);
        add_action('wp_enqueue_scripts', array($this, 'public_assets'));
        add_action('wp_enqueue_scripts', array($this, 'google_font'));
        add_action('webform_after_submission', array($this, 'create_post_from_submission'), 30, 4);
    }

    public function palette($fields) {
        $fields['calculation'] = __('Calculation', 'formorbit');
        $fields['field_group'] = __('Field group', 'formorbit');
        $fields['signature'] = __('E-signature', 'formorbit');
        $fields['rich_text'] = __('Rich text / agreement', 'formorbit');
        $fields['divider'] = __('Divider', 'formorbit');
        $fields['address'] = __('Address', 'formorbit');
        $fields['repeater'] = __('Repeater', 'formorbit');
        $fields['appointment'] = __('Appointment', 'formorbit');
        $fields['nps'] = __('NPS score', 'formorbit');
        $fields['currency'] = __('Currency', 'formorbit');
        $fields['product'] = __('Product selector', 'formorbit');
        $fields['price'] = __('Price', 'formorbit');
        $fields['quantity'] = __('Quantity', 'formorbit');
        $fields['product_option'] = __('Product option', 'formorbit');
        $fields['shipping'] = __('Shipping', 'formorbit');
        $fields['donation'] = __('Donation', 'formorbit');
        $fields['total'] = __('Order total', 'formorbit');
        $fields['post_title'] = __('Post title', 'formorbit');
        $fields['post_body'] = __('Post body', 'formorbit');
        $fields['post_excerpt'] = __('Post excerpt', 'formorbit');
        $fields['post_tags'] = __('Post tags', 'formorbit');
        $fields['post_custom'] = __('Post custom field', 'formorbit');
        return $fields;
    }

    public function admin_script_data($data) {
        $data['stylePalettes'] = $this->preset_palettes();
        return $data;
    }

    public function types($types) {
        return array_unique(array_merge($types, array('calculation', 'field_group', 'signature', 'rich_text', 'divider', 'address', 'repeater', 'appointment', 'nps', 'currency', 'product', 'price', 'quantity', 'product_option', 'shipping', 'donation', 'total', 'post_title', 'post_body', 'post_excerpt', 'post_tags', 'post_custom')));
    }

    public function styles($styles) {
        $styles['elegant'] = __('Elegant', 'formorbit');
        $styles['glass'] = __('Glass', 'formorbit');
        $styles['dark'] = __('Dark', 'formorbit');
        $styles['corporate'] = __('Corporate', 'formorbit');
        $styles['editorial'] = __('Editorial', 'formorbit');
        $styles['pastel'] = __('Soft Pastel', 'formorbit');
        $styles['contrast'] = __('High Contrast', 'formorbit');
        $styles['compact'] = __('Compact', 'formorbit');
        $styles['spacious'] = __('Spacious', 'formorbit');
        $styles['neon'] = __('Neon', 'formorbit');
        $styles['earthy'] = __('Earthy', 'formorbit');
        $styles['luxury'] = __('Luxury', 'formorbit');
        $styles['playful'] = __('Playful', 'formorbit');
        return $styles;
    }

    public function style_controls($settings) {
        $fonts = array('inherit' => __('Theme default', 'formorbit'), 'system-ui,-apple-system,sans-serif' => __('Modern System', 'formorbit'), 'Arial,sans-serif' => 'Arial', 'Georgia,serif' => 'Georgia', '"Trebuchet MS",sans-serif' => 'Trebuchet', '"Courier New",monospace' => 'Courier New', '"Inter",sans-serif' => 'Google — Inter', '"Roboto",sans-serif' => 'Google — Roboto', '"Open Sans",sans-serif' => 'Google — Open Sans', '"Poppins",sans-serif' => 'Google — Poppins', '"Montserrat",sans-serif' => 'Google — Montserrat', '"Lato",sans-serif' => 'Google — Lato', '"Playfair Display",serif' => 'Google — Playfair Display');
        $saved_themes = (array) get_option('webform_pro_saved_themes', array());
        ?>
        <div class="webform-pro-style-controls"><h3><?php esc_html_e('Saved themes', 'formorbit'); ?></h3><div class="webform-theme-library"><div class="webform-theme-actions"><select id="webform-saved-theme"><option value=""><?php esc_html_e('Choose a saved theme', 'formorbit'); ?></option><?php foreach ($saved_themes as $theme_id => $theme) : ?><option value="<?php echo esc_attr($theme_id); ?>"><?php echo esc_html($theme['name'] ?? $theme_id); ?></option><?php endforeach; ?></select><div class="webform-theme-buttons"><button type="button" class="button" id="webform-apply-theme"><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span><?php esc_html_e('Apply', 'formorbit'); ?></button><button type="button" class="button" id="webform-save-theme"><span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span><?php esc_html_e('Save current', 'formorbit'); ?></button><button type="button" class="button webform-theme-delete" id="webform-delete-theme"><span class="dashicons dashicons-trash" aria-hidden="true"></span><?php esc_html_e('Delete theme', 'formorbit'); ?></button></div></div><small class="webform-theme-status"></small></div><h3><?php esc_html_e('Typography', 'formorbit'); ?></h3>
        <label><?php esc_html_e('Font family', 'formorbit'); ?><select id="webform-font-family"><?php foreach ($fonts as $value => $label) : ?><option value="<?php echo esc_attr($value); ?>" <?php selected($settings['font_family'] ?? 'inherit', $value); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></label>
        <div class="webform-style-control-grid">
        <?php foreach (array('heading_font_size' => array(__('Heading size (px)', 'formorbit'), 26, 12, 72), 'line_height' => array(__('Line height', 'formorbit'), 1.5, 1, 2.5), 'form_padding' => array(__('Form padding (px)', 'formorbit'), 24, 0, 80), 'form_radius' => array(__('Form corner radius (px)', 'formorbit'), 12, 0, 60), 'control_padding' => array(__('Input padding (px)', 'formorbit'), 12, 4, 32)) as $key => $control) : ?>
            <label><?php echo esc_html($control[0]); ?><input id="webform-<?php echo esc_attr(str_replace('_', '-', $key)); ?>" type="number" min="<?php echo esc_attr($control[2]); ?>" max="<?php echo esc_attr($control[3]); ?>" step="<?php echo $key === 'line_height' ? '0.1' : '1'; ?>" value="<?php echo esc_attr($settings[$key] ?? $control[1]); ?>"></label>
        <?php endforeach; ?>
        <label><?php esc_html_e('Label weight', 'formorbit'); ?><select id="webform-label-weight"><?php foreach (array(400, 500, 600, 700) as $weight) : ?><option value="<?php echo esc_attr($weight); ?>" <?php selected($settings['label_weight'] ?? 600, $weight); ?>><?php echo esc_html($weight); ?></option><?php endforeach; ?></select></label>
        </div>
        <div class="webform-style-control-grid"><label><?php esc_html_e('Base font size', 'formorbit'); ?><input id="webform-base-font-size" type="number" min="12" max="40" value="<?php echo esc_attr($settings['base_font_size'] ?? 16); ?>"><small>px</small></label><label><?php esc_html_e('Label font size', 'formorbit'); ?><input id="webform-label-font-size" type="number" min="11" max="40" value="<?php echo esc_attr($settings['label_font_size'] ?? 14); ?>"><small>px</small></label></div>
        <h3><?php esc_html_e('Colors', 'formorbit'); ?></h3><div class="webform-style-color-grid"><label><?php esc_html_e('Text', 'formorbit'); ?><input id="webform-text-color" type="color" value="<?php echo esc_attr($settings['text_color'] ?? '#1d2327'); ?>"></label><label><?php esc_html_e('Form background', 'formorbit'); ?><input id="webform-form-background" type="color" value="<?php echo esc_attr($settings['form_background'] ?? '#ffffff'); ?>"></label><label><?php esc_html_e('Field background', 'formorbit'); ?><input id="webform-field-background" type="color" value="<?php echo esc_attr($settings['field_background'] ?? '#ffffff'); ?>"></label><label><?php esc_html_e('Border', 'formorbit'); ?><input id="webform-border-color" type="color" value="<?php echo esc_attr($settings['border_color'] ?? '#dfe1e6'); ?>"></label></div>
        <h3><?php esc_html_e('Layout and controls', 'formorbit'); ?></h3><div class="webform-style-control-grid"><label><?php esc_html_e('Maximum width', 'formorbit'); ?><input id="webform-form-max-width" type="number" min="320" max="1400" value="<?php echo esc_attr($settings['form_max_width'] ?? 720); ?>"><small>px</small></label><label><?php esc_html_e('Field spacing', 'formorbit'); ?><input id="webform-field-spacing" type="number" min="8" max="60" value="<?php echo esc_attr($settings['field_spacing'] ?? 20); ?>"><small>px</small></label><label><?php esc_html_e('Field corner radius', 'formorbit'); ?><input id="webform-field-radius" type="number" min="0" max="40" value="<?php echo esc_attr($settings['field_radius'] ?? 7); ?>"><small>px</small></label><label><?php esc_html_e('Button corner radius', 'formorbit'); ?><input id="webform-button-radius" type="number" min="0" max="40" value="<?php echo esc_attr($settings['button_radius'] ?? 7); ?>"><small>px</small></label><label><?php esc_html_e('Button vertical padding', 'formorbit'); ?><input id="webform-button-padding" type="number" min="6" max="30" value="<?php echo esc_attr($settings['button_padding'] ?? 11); ?>"><small>px</small></label></div>
        <h3><?php esc_html_e('Submit button', 'formorbit'); ?></h3>
        <div class="webform-style-control-grid">
            <label><?php esc_html_e('Alignment', 'formorbit'); ?><select id="webform-submit-alignment"><?php foreach (array('center' => __('Center', 'formorbit'), 'left' => __('Left', 'formorbit'), 'right' => __('Right', 'formorbit'), 'full' => __('Full width', 'formorbit')) as $value => $label) : ?><option value="<?php echo esc_attr($value); ?>" <?php selected($settings['submit_alignment'] ?? 'center', $value); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></label>
            <label><?php esc_html_e('Font size', 'formorbit'); ?><input id="webform-submit-font-size" type="number" min="12" max="30" value="<?php echo esc_attr($settings['submit_font_size'] ?? 16); ?>"><small>px</small></label>
            <label><?php esc_html_e('Font weight', 'formorbit'); ?><select id="webform-submit-font-weight"><?php foreach (array(400, 500, 600, 700, 800) as $weight) : ?><option value="<?php echo esc_attr($weight); ?>" <?php selected(absint($settings['submit_font_weight'] ?? 600), $weight); ?>><?php echo esc_html($weight); ?></option><?php endforeach; ?></select></label>
            <label><?php esc_html_e('Horizontal padding', 'formorbit'); ?><input id="webform-submit-padding-x" type="number" min="10" max="60" value="<?php echo esc_attr($settings['submit_padding_x'] ?? 24); ?>"><small>px</small></label>
            <label><?php esc_html_e('Vertical padding', 'formorbit'); ?><input id="webform-submit-padding-y" type="number" min="6" max="30" value="<?php echo esc_attr($settings['submit_padding_y'] ?? 12); ?>"><small>px</small></label>
            <label><?php esc_html_e('Border width', 'formorbit'); ?><input id="webform-submit-border-width" type="number" min="0" max="6" value="<?php echo esc_attr($settings['submit_border_width'] ?? 0); ?>"><small>px</small></label>
        </div>
        <div class="webform-style-color-grid"><label><?php esc_html_e('Background', 'formorbit'); ?><input id="webform-submit-background" type="color" value="<?php echo esc_attr($settings['submit_background'] ?? ($settings['accent_color'] ?? '#6c4bd4')); ?>"></label><label><?php esc_html_e('Hover', 'formorbit'); ?><input id="webform-submit-hover-background" type="color" value="<?php echo esc_attr($settings['submit_hover_background'] ?? '#5235b1'); ?>"></label><label><?php esc_html_e('Text', 'formorbit'); ?><input id="webform-submit-text-color" type="color" value="<?php echo esc_attr($settings['submit_text_color'] ?? '#ffffff'); ?>"></label><label><?php esc_html_e('Border', 'formorbit'); ?><input id="webform-submit-border-color" type="color" value="<?php echo esc_attr($settings['submit_border_color'] ?? '#6c4bd4'); ?>"></label></div>
        <h3><?php esc_html_e('Custom CSS', 'formorbit'); ?></h3><label><?php esc_html_e('CSS scoped to this form', 'formorbit'); ?><textarea id="webform-custom-css" rows="8" placeholder=".webform-field { ... }"><?php echo esc_textarea($settings['custom_css'] ?? ''); ?></textarea><small><?php esc_html_e('Selectors are automatically restricted to this form. Imports, remote URLs, scripts, and global selectors are removed.', 'formorbit'); ?></small></label></div>
        <?php
    }

    public function sanitize_style_settings($clean, $raw) {
        foreach (array('heading_font_size' => array(26, 12, 72), 'line_height' => array(1.5, 1, 2.5), 'form_padding' => array(24, 0, 80), 'form_radius' => array(12, 0, 60), 'control_padding' => array(12, 4, 32)) as $key => $range) $clean[$key] = min($range[2], max($range[1], floatval($raw[$key] ?? $range[0])));
        $clean['label_weight'] = in_array((int) ($raw['label_weight'] ?? 600), array(400, 500, 600, 700), true) ? (int) ($raw['label_weight'] ?? 600) : 600;
        $fonts = array('inherit', 'system-ui,-apple-system,sans-serif', 'Arial,sans-serif', 'Georgia,serif', '"Trebuchet MS",sans-serif', '"Courier New",monospace', '"Inter",sans-serif', '"Roboto",sans-serif', '"Open Sans",sans-serif', '"Poppins",sans-serif', '"Montserrat",sans-serif', '"Lato",sans-serif', '"Playfair Display",serif');
        $clean['font_family'] = in_array($raw['font_family'] ?? '', $fonts, true) ? $raw['font_family'] : 'inherit';
        $clean['base_font_size'] = min(40, max(12, absint($raw['base_font_size'] ?? 16)));
        $clean['label_font_size'] = min(40, max(11, absint($raw['label_font_size'] ?? 14)));
        foreach (array('text_color' => '#1d2327', 'form_background' => '#ffffff', 'field_background' => '#ffffff', 'border_color' => '#dfe1e6') as $key => $fallback) $clean[$key] = sanitize_hex_color($raw[$key] ?? '') ?: $fallback;
        $clean['form_max_width'] = min(1400, max(320, absint($raw['form_max_width'] ?? 720)));
        $clean['field_spacing'] = min(60, max(8, absint($raw['field_spacing'] ?? 20)));
        $clean['field_radius'] = min(40, absint($raw['field_radius'] ?? 7));
        $clean['button_radius'] = min(40, absint($raw['button_radius'] ?? 7));
        $clean['button_padding'] = min(30, max(6, absint($raw['button_padding'] ?? 11)));
        $clean['submit_alignment'] = in_array($raw['submit_alignment'] ?? '', array('left', 'center', 'right', 'full'), true) ? $raw['submit_alignment'] : 'center';
        $clean['submit_font_size'] = min(30, max(12, absint($raw['submit_font_size'] ?? 16)));
        $clean['submit_font_weight'] = in_array(absint($raw['submit_font_weight'] ?? 600), array(400, 500, 600, 700, 800), true) ? absint($raw['submit_font_weight'] ?? 600) : 600;
        $clean['submit_padding_x'] = min(60, max(10, absint($raw['submit_padding_x'] ?? 24)));
        $clean['submit_padding_y'] = min(30, max(6, absint($raw['submit_padding_y'] ?? 12)));
        $clean['submit_border_width'] = min(6, absint($raw['submit_border_width'] ?? 0));
        foreach (array('submit_background' => '#6c4bd4', 'submit_hover_background' => '#5235b1', 'submit_text_color' => '#ffffff', 'submit_border_color' => '#6c4bd4') as $key => $fallback) $clean[$key] = sanitize_hex_color($raw[$key] ?? '') ?: $fallback;
        $clean['custom_css'] = $this->sanitize_custom_css($raw['custom_css'] ?? '');
        return $clean;
    }

    public function field_custom_class($class, $field) {
        return sanitize_html_class($field['style']['css_class'] ?? '');
    }

    public function style_variables($variables, $settings) {
        foreach (array('heading_font_size' => array('heading-size', 26, 'px'), 'line_height' => array('line-height', 1.5, ''), 'form_padding' => array('form-padding', 24, 'px'), 'form_radius' => array('form-radius', 12, 'px'), 'control_padding' => array('control-padding', 12, 'px'), 'label_weight' => array('label-weight', 600, '')) as $key => $variable) $variables['--wf-' . $variable[0]] = floatval($settings[$key] ?? $variable[1]) . $variable[2];
        $palettes = $this->preset_palettes();
        $preset = sanitize_key($settings['style_preset'] ?? 'modern');
        if (empty($settings['font_family']) || $settings['font_family'] === 'inherit') {
            if (in_array($preset, array('elegant', 'classic', 'earthy', 'luxury', 'orbit-paper', 'orbit-editorial'), true)) $settings['font_family'] = 'Georgia,serif';
            if ($preset === 'orbit-terminal') $settings['font_family'] = 'monospace';
        }
        $palette = $palettes[$preset] ?? $palettes['modern'];
        $defaults = array('text_color' => '#1d2327', 'form_background' => '#ffffff', 'field_background' => '#ffffff', 'border_color' => '#dfe1e6', 'accent_color' => '#6c4bd4', 'button_text_color' => '#ffffff');
        foreach ($defaults as $key => $default) {
            if (empty($settings[$key]) || ($preset !== 'modern' && strtolower($settings[$key]) === strtolower($default))) $settings[$key] = $palette[$key];
        }
        $alignment = $settings['submit_alignment'] ?? 'center';
        return array_merge($variables, array('--wf-font' => $settings['font_family'] ?? 'inherit', '--wf-font-size' => absint($settings['base_font_size'] ?? 16) . 'px', '--wf-label-size' => absint($settings['label_font_size'] ?? 14) . 'px', '--wf-text' => $settings['text_color'], '--wf-form-bg' => $settings['form_background'], '--wf-field-bg' => $settings['field_background'], '--wf-border' => $settings['border_color'], '--wf-accent' => $settings['accent_color'], '--wf-button-text' => $settings['button_text_color'], '--wf-max-width' => absint($settings['form_max_width'] ?? 720) . 'px', '--wf-field-space' => absint($settings['field_spacing'] ?? 20) . 'px', '--wf-field-radius' => absint($settings['field_radius'] ?? 7) . 'px', '--wf-button-radius' => absint($settings['button_radius'] ?? 7) . 'px', '--wf-button-padding' => absint($settings['button_padding'] ?? 11) . 'px ' . max(12, absint($settings['button_padding'] ?? 11) * 2) . 'px', '--wf-submit-align' => $alignment === 'left' ? 'flex-start' : ($alignment === 'center' ? 'center' : 'flex-end'), '--wf-submit-width' => $alignment === 'full' ? '100%' : 'auto', '--wf-submit-bg' => $settings['submit_background'] ?? $settings['accent_color'], '--wf-submit-hover' => $settings['submit_hover_background'] ?? '#5235b1', '--wf-submit-text' => $settings['submit_text_color'] ?? '#ffffff', '--wf-submit-font-size' => absint($settings['submit_font_size'] ?? 16) . 'px', '--wf-submit-font-weight' => absint($settings['submit_font_weight'] ?? 600), '--wf-submit-padding' => absint($settings['submit_padding_y'] ?? 12) . 'px ' . absint($settings['submit_padding_x'] ?? 24) . 'px', '--wf-submit-border' => absint($settings['submit_border_width'] ?? 0) . 'px solid ' . ($settings['submit_border_color'] ?? '#6c4bd4')));
    }

    private function preset_palettes() {
        return array(
            'orbit-paper' => array('accent_color' => '#7b5831', 'button_text_color' => '#ffffff', 'text_color' => '#382e22', 'form_background' => '#faf4e6', 'field_background' => '#fffaf0', 'border_color' => '#c9bea7'),
            'orbit-night' => array('accent_color' => '#9cbcff', 'button_text_color' => '#101b35', 'text_color' => '#edf3ff', 'form_background' => '#101b35', 'field_background' => '#1d2b48', 'border_color' => '#53668c'),
            'orbit-terminal' => array('accent_color' => '#4ccb78', 'button_text_color' => '#071b12', 'text_color' => '#b9ffc8', 'form_background' => '#071b12', 'field_background' => '#102c1c', 'border_color' => '#4ccb78'),
            'orbit-editorial' => array('accent_color' => '#1c1c1c', 'button_text_color' => '#ffffff', 'text_color' => '#1c1c1c', 'form_background' => '#ffffff', 'field_background' => '#ffffff', 'border_color' => '#999999'),
            'orbit-ocean' => array('accent_color' => '#116986', 'button_text_color' => '#ffffff', 'text_color' => '#123d58', 'form_background' => '#dff8f8', 'field_background' => '#ffffff', 'border_color' => '#91bfce'),
            'orbit-brutalist' => array('accent_color' => '#151515', 'button_text_color' => '#ffef75', 'text_color' => '#151515', 'form_background' => '#ffef75', 'field_background' => '#ffffff', 'border_color' => '#151515'),
            'orbit-glass' => array('accent_color' => '#744391', 'button_text_color' => '#ffffff', 'text_color' => '#38234e', 'form_background' => '#eee8ff', 'field_background' => '#ffffff', 'border_color' => '#d8c7e8'),
            'modern' => array('accent_color' => '#6c4bd4', 'button_text_color' => '#ffffff', 'text_color' => '#1d2327', 'form_background' => '#ffffff', 'field_background' => '#ffffff', 'border_color' => '#dfe1e6'),
            'minimal' => array('accent_color' => '#222222', 'button_text_color' => '#ffffff', 'text_color' => '#242424', 'form_background' => '#ffffff', 'field_background' => '#ffffff', 'border_color' => '#b8b8b8'),
            'rounded' => array('accent_color' => '#6750c8', 'button_text_color' => '#ffffff', 'text_color' => '#282334', 'form_background' => '#fbfaff', 'field_background' => '#ffffff', 'border_color' => '#d9d2ed'),
            'dark' => array('accent_color' => '#8b72ef', 'button_text_color' => '#ffffff', 'text_color' => '#f6f6f8', 'form_background' => '#20202a', 'field_background' => '#2c2c38', 'border_color' => '#4a4a59'),
            'neon' => array('accent_color' => '#5fffe0', 'button_text_color' => '#101018', 'text_color' => '#f6f7ff', 'form_background' => '#12121a', 'field_background' => '#1c1c27', 'border_color' => '#5fffe0'),
            'luxury' => array('accent_color' => '#c3a35c', 'button_text_color' => '#171512', 'text_color' => '#f7eed8', 'form_background' => '#171512', 'field_background' => '#211f1b', 'border_color' => '#8d743e'),
            'elegant' => array('accent_color' => '#8a6438', 'button_text_color' => '#ffffff', 'text_color' => '#302820', 'form_background' => '#fffdf9', 'field_background' => '#ffffff', 'border_color' => '#dfd3c2'),
            'glass' => array('accent_color' => '#6651c9', 'button_text_color' => '#ffffff', 'text_color' => '#25203a', 'form_background' => '#f4f0ff', 'field_background' => '#ffffff', 'border_color' => '#d8cfef'),
            'corporate' => array('accent_color' => '#183b63', 'button_text_color' => '#ffffff', 'text_color' => '#1d3249', 'form_background' => '#f8fafc', 'field_background' => '#ffffff', 'border_color' => '#b9c8d8'),
            'editorial' => array('accent_color' => '#7d3728', 'button_text_color' => '#ffffff', 'text_color' => '#29231e', 'form_background' => '#fffdf8', 'field_background' => '#fffdf8', 'border_color' => '#8f8175'),
            'pastel' => array('accent_color' => '#a84f83', 'button_text_color' => '#ffffff', 'text_color' => '#493746', 'form_background' => '#fff7fb', 'field_background' => '#ffffff', 'border_color' => '#e8cadb'),
            'contrast' => array('accent_color' => '#000000', 'button_text_color' => '#ffffff', 'text_color' => '#000000', 'form_background' => '#ffffff', 'field_background' => '#ffffff', 'border_color' => '#111111'),
            'compact' => array('accent_color' => '#3858a6', 'button_text_color' => '#ffffff', 'text_color' => '#252a34', 'form_background' => '#ffffff', 'field_background' => '#ffffff', 'border_color' => '#d5d9e0'),
            'spacious' => array('accent_color' => '#4968b8', 'button_text_color' => '#ffffff', 'text_color' => '#293240', 'form_background' => '#ffffff', 'field_background' => '#ffffff', 'border_color' => '#d8dde6'),
            'earthy' => array('accent_color' => '#70613f', 'button_text_color' => '#ffffff', 'text_color' => '#3f392d', 'form_background' => '#f4efe5', 'field_background' => '#fffaf0', 'border_color' => '#b9a788'),
            'playful' => array('accent_color' => '#e14d72', 'button_text_color' => '#ffffff', 'text_color' => '#342e63', 'form_background' => '#fff9e8', 'field_background' => '#ffffff', 'border_color' => '#342e63'),
        );
    }

    public function custom_css($form_id, $settings) {
        $scope = '#webform-' . absint($form_id);
        $css = empty($settings['custom_css']) ? '' : $this->scope_custom_css($settings['custom_css'], $scope);
        foreach ((array) get_post_meta($form_id, '_webform_schema', true) as $stage) {
            foreach ((array) ($stage['fields'] ?? array()) as $field) {
                $field_id = sanitize_key($field['id'] ?? '');
                if (!$field_id) continue;
                $style = (array) ($field['style'] ?? array());
                $selector = $scope . ' [data-field-id="' . $field_id . '"]';
                if (($field['type'] ?? '') === 'divider') {
                    $divider_style = in_array($field['divider_style'] ?? 'solid', array('solid', 'dashed', 'dotted', 'double'), true) ? ($field['divider_style'] ?? 'solid') : 'solid';
                    $divider_alignment = in_array($field['divider_alignment'] ?? 'center', array('left', 'center', 'right'), true) ? ($field['divider_alignment'] ?? 'center') : 'center';
                    $divider_width = min(100, max(10, absint($field['divider_width'] ?? 100)));
                    $divider_thickness = min(12, max(1, absint($field['divider_thickness'] ?? 1)));
                    $divider_color = sanitize_hex_color($field['divider_color'] ?? '') ?: '#dfe1e6';
                    $divider_margin_top = min(100, absint($field['divider_margin_top'] ?? 10));
                    $divider_margin_bottom = min(100, absint($field['divider_margin_bottom'] ?? 10));
                    $divider_padding_top = min(80, absint($field['divider_padding_top'] ?? 0));
                    $divider_padding_bottom = min(80, absint($field['divider_padding_bottom'] ?? 0));
                    $css .= $selector . '{box-sizing:border-box;flex:0 0 100%;margin-top:' . $divider_margin_top . 'px;margin-bottom:' . $divider_margin_bottom . 'px;padding-top:' . $divider_padding_top . 'px;padding-bottom:' . $divider_padding_bottom . 'px;width:100%;}';
                    $css .= $selector . ' .webform-divider-rule{width:' . $divider_width . '%;}';
                    $css .= $selector . ' .webform-divider-rule hr{border-top:' . $divider_thickness . 'px ' . $divider_style . ' ' . $divider_color . ';}';
                    if ($divider_alignment === 'left') {
                        $css .= $selector . ' .webform-divider-rule{margin-left:0;margin-right:auto;}';
                    } elseif ($divider_alignment === 'right') {
                        $css .= $selector . ' .webform-divider-rule{margin-left:auto;margin-right:0;}';
                    } else {
                        $css .= $selector . ' .webform-divider-rule{margin-left:auto;margin-right:auto;}';
                    }
                    continue;
                }
                if (!$style) continue;
                $requested_width = (string) ($style['width'] ?? '100');
                $render_width = array('100' => '100%', '75' => '74%', '50' => '49%', '33' => '32%');
                $label_color = sanitize_hex_color($style['label_color'] ?? '') ?: 'inherit';
                $background_color = sanitize_hex_color($style['background_color'] ?? '') ?: 'var(--wf-field-bg)';
                $text_color = sanitize_hex_color($style['text_color'] ?? '') ?: 'inherit';
                $radius = min(40, absint($style['radius'] ?? 7));
                $css .= $requested_width === 'auto'
                    ? $selector . '{box-sizing:border-box;flex:1 1 260px;min-width:220px;width:auto;}'
                    : $selector . '{box-sizing:border-box;flex:0 0 ' . ($render_width[$requested_width] ?? '100%') . ';width:' . ($render_width[$requested_width] ?? '100%') . ';}';
                if (!empty($style['customized'])) {
                    $css .= $selector . '>label,' . $selector . '>legend,' . $selector . '>.webform-price-label{color:' . $label_color . ';}';
                    $css .= $selector . ' input,' . $selector . ' select,' . $selector . ' textarea,' . $selector . ' canvas{background-color:' . $background_color . ';color:' . $text_color . ';border-radius:' . $radius . 'px;}';
                    $css .= $selector . ' .webform-choices label,' . $selector . ' .webform-product-options label>span{background-color:' . $background_color . ';color:' . $text_color . ';border-radius:' . $radius . 'px;}';
                    if (($field['type'] ?? '') === 'heading') $css .= $selector . '{background-color:' . $background_color . ';border-radius:' . $radius . 'px;color:' . $label_color . ';padding:10px 12px;}';
                    if (($field['type'] ?? '') === 'html') $css .= $selector . '{background-color:' . $background_color . ';border-radius:' . $radius . 'px;color:' . $text_color . ';padding:10px 12px;}';
                    if (($field['type'] ?? '') === 'price') $css .= $selector . '{background-color:' . $background_color . ';border-radius:' . $radius . 'px;color:' . $text_color . ';}';
                    if (($field['type'] ?? '') === 'rich_text') {
                        $css .= $selector . ' .webform-rich-text-content{background-color:' . $background_color . ';border-radius:' . $radius . 'px;color:' . $text_color . ';}';
                        $css .= $selector . ' .webform-rich-text-content h1,' . $selector . ' .webform-rich-text-content h2,' . $selector . ' .webform-rich-text-content h3,' . $selector . ' .webform-rich-text-content h4,' . $selector . ' .webform-rich-text-content h5,' . $selector . ' .webform-rich-text-content h6{color:' . $text_color . ';}';
                    }
                    if (($field['type'] ?? '') === 'field_group') $css .= $selector . '{background-color:' . $background_color . ';border-radius:' . $radius . 'px;color:' . $text_color . ';}';
                    if (($field['type'] ?? '') === 'nps') $css .= $selector . ' .webform-nps-scale span{background-color:' . $background_color . ';border-radius:' . $radius . 'px;color:' . $text_color . ';}';
                    if (($field['type'] ?? '') === 'currency') $css .= $selector . ' .webform-currency-control>span{background-color:' . $background_color . ';color:' . $text_color . ';}';
                }
            }
        }
        if ($css) echo '<style id="webform-pro-css-' . esc_attr($form_id) . '">' . $css . '</style>';
    }

    public function templates($templates) {
        $premium = array(
            'pro-lead' => $this->template(__('Lead Qualification', 'formorbit'), __('Score and route qualified sales leads.', 'formorbit'), array($this->field('company', 'text', __('Company', 'formorbit'), true), $this->field('email', 'email', __('Business email', 'formorbit'), true), $this->field('budget', 'select', __('Budget', 'formorbit'), true, array('$1k–$5k', '$5k–$20k', '$20k+')))),
            'pro-booking' => $this->template(__('Appointment Booking', 'formorbit'), __('Collect appointment date, time, and details.', 'formorbit'), array($this->field('name', 'text', __('Name', 'formorbit'), true), $this->field('date', 'date', __('Date', 'formorbit'), true), $this->field('time', 'time', __('Time', 'formorbit'), true), $this->field('notes', 'textarea', __('Notes', 'formorbit')))),
            'pro-donation' => $this->template(__('Donation Form', 'formorbit'), __('Donation amount and hosted payment handoff.', 'formorbit'), array($this->field('donor', 'text', __('Donor name', 'formorbit'), true), $this->field('amount', 'number', __('Donation amount', 'formorbit'), true), $this->field('message', 'textarea', __('Message', 'formorbit')))),
            'pro-order' => $this->template(__('Product Order', 'formorbit'), __('Quantity, unit price, calculated total, and payment.', 'formorbit'), array($this->field('unit_price', 'number', __('Unit price', 'formorbit'), true), $this->field('quantity', 'number', __('Quantity', 'formorbit'), true), $this->field('total', 'calculation', __('Order total', 'formorbit'), false, array(), '{unit_price} * {quantity}'))),
            'pro-invoice' => $this->template(__('Invoice Request', 'formorbit'), __('Billing details and calculated service totals.', 'formorbit'), array($this->field('client', 'text', __('Client', 'formorbit'), true), $this->field('rate', 'number', __('Hourly rate', 'formorbit'), true), $this->field('hours', 'number', __('Hours', 'formorbit'), true), $this->field('total', 'calculation', __('Total', 'formorbit'), false, array(), '{rate} * {hours}'))),
            'pro-onboarding' => $this->template(__('Client Onboarding', 'formorbit'), __('Structured client and project onboarding.', 'formorbit'), array($this->group_field('group', __('Client details', 'formorbit'), array($this->field('name', 'text', __('Name', 'formorbit'), true), $this->field('email', 'email', __('Email', 'formorbit'), true))), $this->field('goals', 'textarea', __('Primary goals', 'formorbit'), true))),
            'pro-project' => $this->template(__('Project Brief', 'formorbit'), __('Goals, audience, scope, budget, and assets.', 'formorbit'), array($this->field('project', 'text', __('Project name', 'formorbit'), true), $this->field('audience', 'textarea', __('Target audience', 'formorbit')), $this->field('scope', 'textarea', __('Scope', 'formorbit'), true), $this->field('assets', 'file', __('Project assets', 'formorbit')))),
            'pro-real-estate' => $this->template(__('Real Estate Inquiry', 'formorbit'), __('Buyer requirements and property preferences.', 'formorbit'), array($this->field('name', 'text', __('Buyer name', 'formorbit'), true), $this->field('property', 'select', __('Property type', 'formorbit'), true, array('House', 'Condo', 'Land')), $this->field('budget', 'number', __('Maximum budget', 'formorbit')), $this->field('timeline', 'text', __('Move-in timeline', 'formorbit')))),
            'pro-mortgage' => $this->template(__('Mortgage Prequalification', 'formorbit'), __('Income, down payment, and requested amount.', 'formorbit'), array($this->field('income', 'number', __('Annual income', 'formorbit'), true), $this->field('down', 'number', __('Down payment', 'formorbit'), true), $this->field('loan', 'number', __('Requested loan', 'formorbit'), true))),
            'pro-insurance' => $this->template(__('Insurance Quote', 'formorbit'), __('Applicant and coverage requirements.', 'formorbit'), array($this->field('name', 'text', __('Applicant', 'formorbit'), true), $this->field('coverage', 'select', __('Coverage type', 'formorbit'), true, array('Auto', 'Home', 'Life', 'Business')), $this->field('details', 'textarea', __('Coverage details', 'formorbit')))),
            'pro-medical' => $this->template(__('Medical Intake', 'formorbit'), __('Patient history, consent, and signature.', 'formorbit'), array($this->field('patient', 'text', __('Patient name', 'formorbit'), true), $this->field('history', 'textarea', __('Medical history', 'formorbit'), true), $this->field('consent', 'consent', __('I consent to treatment.', 'formorbit'), true), $this->field('signature', 'signature', __('Patient signature', 'formorbit'), true))),
            'pro-course' => $this->template(__('Course Enrollment', 'formorbit'), __('Student details and course selection.', 'formorbit'), array($this->field('student', 'text', __('Student name', 'formorbit'), true), $this->field('email', 'email', __('Email', 'formorbit'), true), $this->field('course', 'select', __('Course', 'formorbit'), true, array('Foundation', 'Advanced', 'Masterclass')))),
            'pro-membership' => $this->template(__('Membership Application', 'formorbit'), __('Membership tier, profile, and agreement.', 'formorbit'), array($this->field('name', 'text', __('Name', 'formorbit'), true), $this->field('tier', 'radio', __('Membership tier', 'formorbit'), true, array('Standard', 'Professional', 'Premium')), $this->field('signature', 'signature', __('Signature', 'formorbit'), true))),
            'pro-restaurant' => $this->template(__('Restaurant Reservation', 'formorbit'), __('Reservation schedule, party size, and requests.', 'formorbit'), array($this->field('name', 'text', __('Guest name', 'formorbit'), true), $this->field('date', 'date', __('Date', 'formorbit'), true), $this->field('time', 'time', __('Time', 'formorbit'), true), $this->field('party', 'number', __('Party size', 'formorbit'), true))),
            'pro-return' => $this->template(__('Product Return', 'formorbit'), __('Order lookup, return reason, and evidence.', 'formorbit'), array($this->field('order', 'text', __('Order number', 'formorbit'), true), $this->field('reason', 'select', __('Return reason', 'formorbit'), true, array('Damaged', 'Incorrect', 'Changed mind')), $this->field('photo', 'file', __('Product photo', 'formorbit')))),
            'pro-warranty' => $this->template(__('Warranty Claim', 'formorbit'), __('Product details, proof, and claim description.', 'formorbit'), array($this->field('serial', 'text', __('Serial number', 'formorbit'), true), $this->field('purchase', 'date', __('Purchase date', 'formorbit'), true), $this->field('proof', 'file', __('Proof of purchase', 'formorbit'), true), $this->field('issue', 'textarea', __('Issue', 'formorbit'), true))),
            'pro-vendor' => $this->template(__('Vendor Application', 'formorbit'), __('Company profile, services, documents, and signature.', 'formorbit'), array($this->field('company', 'text', __('Company', 'formorbit'), true), $this->field('services', 'textarea', __('Services', 'formorbit'), true), $this->field('document', 'file', __('Company document', 'formorbit')), $this->field('signature', 'signature', __('Authorized signature', 'formorbit'), true))),
            'pro-employee' => $this->template(__('Employee Onboarding', 'formorbit'), __('New-hire information and policy acceptance.', 'formorbit'), array($this->field('employee', 'text', __('Employee name', 'formorbit'), true), $this->field('start', 'date', __('Start date', 'formorbit'), true), $this->field('department', 'text', __('Department', 'formorbit')), $this->field('signature', 'signature', __('Policy signature', 'formorbit'), true))),
            'pro-sponsor' => $this->template(__('Event Sponsorship', 'formorbit'), __('Sponsor profile, package, and commitment.', 'formorbit'), array($this->field('company', 'text', __('Sponsor company', 'formorbit'), true), $this->field('package', 'radio', __('Package', 'formorbit'), true, array('Silver', 'Gold', 'Platinum')), $this->field('signature', 'signature', __('Authorized signature', 'formorbit'), true))),
            'pro-agreement' => $this->template(__('Signed Agreement', 'formorbit'), __('Agreement acceptance with identity and signature.', 'formorbit'), array($this->field('name', 'text', __('Full legal name', 'formorbit'), true), $this->field('email', 'email', __('Email', 'formorbit'), true), $this->field('agreement', 'consent', __('I accept the agreement.', 'formorbit'), true), $this->field('signature', 'signature', __('Signature', 'formorbit'), true))),
            'pro-store-order' => $this->template(__('Store Product Order', 'formorbit'), __('Products, customer details, delivery address, payment, and consent.', 'formorbit'), array($this->field('product', 'product', __('Choose a product', 'formorbit'), true, array('Starter package|19.99', 'Business package|49.99', 'Premium package|99.99')), $this->field('name', 'text', __('Customer name', 'formorbit'), true), $this->field('email', 'email', __('Email', 'formorbit'), true), $this->field('address', 'address', __('Delivery address', 'formorbit'), true), $this->field('notes', 'textarea', __('Order notes', 'formorbit')), $this->field('consent', 'consent', __('I agree to the order terms.', 'formorbit'), true))),
            'pro-ticket-sale' => $this->template(__('Event Ticket Sale', 'formorbit'), __('Ticket selection, attendee profile, schedule, and payment.', 'formorbit'), array($this->field('ticket', 'product', __('Ticket', 'formorbit'), true, array('General admission|25.00', 'VIP admission|75.00', 'Team pass|150.00')), $this->field('attendee', 'text', __('Attendee name', 'formorbit'), true), $this->field('email', 'email', __('Email', 'formorbit'), true), $this->field('phone', 'phone', __('Phone', 'formorbit')), $this->field('requests', 'textarea', __('Accessibility or dietary requests', 'formorbit')))),
            'pro-paid-booking' => $this->template(__('Paid Appointment', 'formorbit'), __('Service selection, appointment time, customer details, and payment.', 'formorbit'), array($this->field('service', 'product', __('Select service', 'formorbit'), true, array('Consultation|49.00', 'Strategy session|99.00', 'Implementation session|199.00')), $this->field('appointment', 'appointment', __('Appointment date and time', 'formorbit'), true), $this->field('name', 'text', __('Name', 'formorbit'), true), $this->field('email', 'email', __('Email', 'formorbit'), true), $this->field('brief', 'textarea', __('What would you like to discuss?', 'formorbit')))),
            'pro-employee-review' => $this->template(__('Employee Performance Review', 'formorbit'), __('Goals, competency ratings, manager feedback, and acknowledgement.', 'formorbit'), array($this->field('employee', 'text', __('Employee name', 'formorbit'), true), $this->field('period', 'text', __('Review period', 'formorbit'), true), $this->field('performance', 'nps', __('Overall performance', 'formorbit'), true), $this->field('goals', 'textarea', __('Goals for the next period', 'formorbit')), $this->field('signature', 'signature', __('Employee acknowledgement', 'formorbit'), true))),
            'pro-travel-request' => $this->template(__('Business Travel Request', 'formorbit'), __('Trip itinerary, estimated costs, justification, and approval details.', 'formorbit'), array($this->field('traveler', 'text', __('Traveler name', 'formorbit'), true), $this->field('destination', 'address', __('Destination', 'formorbit'), true), $this->field('departure', 'date', __('Departure date', 'formorbit'), true), $this->field('return', 'date', __('Return date', 'formorbit'), true), $this->field('estimate', 'currency', __('Estimated cost', 'formorbit'), true), $this->field('reason', 'textarea', __('Business justification', 'formorbit'), true))),
            'pro-event-proposal' => $this->template(__('Event Proposal', 'formorbit'), __('Venue, budget, schedule, audience, and supporting documents.', 'formorbit'), array($this->field('event', 'text', __('Event name', 'formorbit'), true), $this->field('date', 'date', __('Proposed date', 'formorbit'), true), $this->field('venue', 'text', __('Venue', 'formorbit')), $this->field('budget', 'currency', __('Estimated budget', 'formorbit'), true), $this->field('audience', 'number', __('Expected attendance', 'formorbit')), $this->field('proposal', 'file', __('Proposal document', 'formorbit')))),
            'pro-content-submission' => $this->template(__('Guest Content Submission', 'formorbit'), __('Contributor profile, article content, media, and publishing agreement.', 'formorbit'), array($this->field('author', 'text', __('Author name', 'formorbit'), true), $this->field('email', 'email', __('Email', 'formorbit'), true), $this->field('title', 'post_title', __('Article title', 'formorbit'), true), $this->field('content', 'post_body', __('Article content', 'formorbit'), true), $this->field('image', 'file', __('Featured image', 'formorbit')), $this->field('agreement', 'consent', __('I confirm this is original content and accept the publishing terms.', 'formorbit'), true))),
        );
        $categories = array(
            __('Sales & commerce', 'formorbit') => array('pro-lead', 'pro-order', 'pro-invoice', 'pro-donation', 'pro-store-order', 'pro-ticket-sale'),
            __('Bookings & events', 'formorbit') => array('pro-booking', 'pro-restaurant', 'pro-sponsor', 'pro-paid-booking', 'pro-event-proposal'),
            __('Clients & professional services', 'formorbit') => array('pro-onboarding', 'pro-project', 'pro-real-estate', 'pro-mortgage', 'pro-insurance'),
            __('People & organizations', 'formorbit') => array('pro-medical', 'pro-course', 'pro-membership', 'pro-vendor', 'pro-employee', 'pro-employee-review', 'pro-travel-request'),
            __('Claims, agreements & publishing', 'formorbit') => array('pro-return', 'pro-warranty', 'pro-agreement', 'pro-content-submission'),
        );
        foreach ($categories as $category => $keys) foreach ($keys as $key) if (isset($premium[$key])) $premium[$key]['category'] = $category;
        $workflow_fields = array(
            __('Sales & commerce', 'formorbit') => array($this->field('contact_name', 'name', __('Contact name', 'formorbit'), true), $this->field('contact_email', 'email', __('Email address', 'formorbit'), true), $this->field('contact_phone', 'phone', __('Phone number', 'formorbit')), $this->field('requirements', 'textarea', __('Requirements and special instructions', 'formorbit')), $this->field('sales_consent', 'consent', __('I confirm the information and agree to be contacted about this request.', 'formorbit'), true)),
            __('Bookings & events', 'formorbit') => array($this->field('guest_name', 'name', __('Guest or attendee name', 'formorbit'), true), $this->field('guest_email', 'email', __('Confirmation email', 'formorbit'), true), $this->field('guest_phone', 'phone', __('Mobile number', 'formorbit')), $this->field('attendance_notes', 'textarea', __('Accessibility, dietary, or scheduling requests', 'formorbit')), $this->field('booking_consent', 'consent', __('I confirm the booking details and cancellation terms.', 'formorbit'), true)),
            __('Clients & professional services', 'formorbit') => array($this->field('client_name', 'name', __('Primary contact', 'formorbit'), true), $this->field('client_email', 'email', __('Business email', 'formorbit'), true), $this->field('client_phone', 'phone', __('Direct phone', 'formorbit')), $this->field('organization', 'text', __('Organization', 'formorbit')), $this->field('target_timeline', 'select', __('Target timeline', 'formorbit'), true, array(__('Immediately', 'formorbit'), __('Within 30 days', 'formorbit'), __('Within 90 days', 'formorbit'), __('Researching options', 'formorbit'))), $this->field('supporting_files', 'file', __('Supporting files', 'formorbit'))),
            __('People & organizations', 'formorbit') => array($this->field('person_name', 'name', __('Full name', 'formorbit'), true), $this->field('person_email', 'email', __('Email address', 'formorbit'), true), $this->field('person_phone', 'phone', __('Phone number', 'formorbit')), $this->field('person_address', 'address', __('Mailing address', 'formorbit')), $this->field('additional_context', 'textarea', __('Additional information', 'formorbit')), $this->field('privacy_consent', 'consent', __('I confirm these details are accurate and consent to their processing.', 'formorbit'), true)),
            __('Claims, agreements & publishing', 'formorbit') => array($this->field('submitter_name', 'name', __('Submitter name', 'formorbit'), true), $this->field('submitter_email', 'email', __('Email address', 'formorbit'), true), $this->field('reference_number', 'text', __('Reference or account number', 'formorbit')), $this->field('evidence', 'file', __('Supporting evidence', 'formorbit')), $this->field('declaration', 'consent', __('I declare that the supplied information is complete and accurate.', 'formorbit'), true), $this->field('authorization', 'signature', __('Authorized signature', 'formorbit'), true)),
        );
        $presets = array('elegant', 'glass', 'corporate', 'editorial', 'pastel', 'contrast', 'compact', 'spacious', 'earthy', 'luxury', 'playful');
        $accents = array('#6548c7', '#0f766e', '#1d4ed8', '#b45309', '#be185d', '#047857', '#7c3aed', '#0369a1');
        foreach ($premium as $key => &$template) {
            $category = $template['category'] ?? __('Premium templates', 'formorbit');
            $details = $workflow_fields[$category] ?? array();
            $original = (array) ($template['schema'][0]['fields'] ?? array());
            $template['schema'] = array(
                array('id' => sanitize_key($key . '_details'), 'title' => __('Request details', 'formorbit'), 'fields' => $original),
                array('id' => sanitize_key($key . '_contact'), 'title' => __('Contact, documents & confirmation', 'formorbit'), 'fields' => $details),
            );
            $index = abs(crc32($key));
            $template['settings'] = array('style_preset' => $presets[$index % count($presets)], 'accent_color' => $accents[$index % count($accents)], 'button_text_color' => '#ffffff', 'submit_label' => __('Submit request', 'formorbit'));
        }
        unset($template);
        return array_merge($templates, $premium);
    }

    private function template($name, $description, $fields) {
        $icons = array('dashicons-cart', 'dashicons-calendar-alt', 'dashicons-businessperson', 'dashicons-money-alt', 'dashicons-clipboard', 'dashicons-building', 'dashicons-heart', 'dashicons-welcome-learn-more');
        return array('name' => $name, 'description' => $description, 'icon' => $icons[abs(crc32($name)) % count($icons)], 'pro' => false, 'schema' => array(array('id' => 'pro_stage', 'title' => $name, 'fields' => $fields)));
    }

    private function field($id, $type, $label, $required = false, $options = array(), $formula = '') {
        return array('id' => sanitize_key($id), 'type' => $type, 'label' => $label, 'placeholder' => '', 'required' => $required, 'options' => $options, 'allowed_extensions' => 'jpg,jpeg,png,pdf,doc,docx', 'max_size' => 5, 'formula' => $formula, 'decimal_places' => 2, 'group_count' => 2, 'group_columns' => 2, 'condition' => array('enabled' => false, 'field_id' => '', 'operator' => 'equals', 'value' => ''));
    }

    private function group_field($id, $label, $children) {
        $group = $this->field($id, 'field_group', $label);
        $group['children'] = $children;
        return $group;
    }

    public function sanitize($clean, $raw) {
        $icon = sanitize_key($raw['icon'] ?? '');
        $clean['icon'] = isset($this->icons()[$icon]) ? $icon : '';
        if (($clean['type'] ?? '') === 'calculation') {
            $clean['formula'] = preg_replace('/[^a-zA-Z0-9_{}+\-*\/().,%^<>=!&|\s]/', '', $raw['formula'] ?? '');
            $clean['decimal_places'] = min(6, absint($raw['decimal_places'] ?? 2));
        } elseif (($clean['type'] ?? '') === 'field_group') {
            $clean['group_count'] = min(6, max(1, absint($raw['group_count'] ?? 2)));
            $clean['group_columns'] = min(4, max(1, absint($raw['group_columns'] ?? 2)));
            $clean['children'] = $this->sanitize_container_children($raw['children'] ?? array());
        } elseif (($clean['type'] ?? '') === 'repeater') {
            $clean['repeater_min'] = min(20, max(1, absint($raw['repeater_min'] ?? 1)));
            $clean['repeater_max'] = min(50, max($clean['repeater_min'], absint($raw['repeater_max'] ?? 10)));
            $clean['repeater_button'] = substr(sanitize_text_field($raw['repeater_button'] ?? __('Add another', 'formorbit')), 0, 80);
            $clean['children'] = $this->sanitize_container_children($raw['children'] ?? array());
        } elseif (($clean['type'] ?? '') === 'currency') {
            $clean['currency_symbol'] = substr(sanitize_text_field($raw['currency_symbol'] ?? '$'), 0, 5);
            $clean['min'] = floatval($raw['min'] ?? 0);
            $clean['max'] = floatval($raw['max'] ?? 999999999);
        } elseif (($clean['type'] ?? '') === 'appointment') {
            $clean['min_date'] = sanitize_text_field($raw['min_date'] ?? '');
            $clean['max_date'] = sanitize_text_field($raw['max_date'] ?? '');
        } elseif (in_array($clean['type'] ?? '', array('price', 'total'), true)) {
            $clean['price_amount'] = max(0, floatval($raw['price_amount'] ?? 0));
            $clean['currency_code'] = in_array(strtoupper($raw['currency_code'] ?? 'USD'), array('USD', 'EUR', 'GBP', 'CAD', 'AUD', 'BDT'), true) ? strtoupper($raw['currency_code'] ?? 'USD') : 'USD';
        } elseif (in_array($clean['type'] ?? '', array('quantity', 'donation'), true)) {
            $clean['min'] = max(0, floatval($raw['min'] ?? ($clean['type'] === 'quantity' ? 1 : 0)));
            $clean['max'] = max($clean['min'], floatval($raw['max'] ?? 999999));
            $clean['step'] = max(0.01, floatval($raw['step'] ?? 1));
        } elseif (($clean['type'] ?? '') === 'rich_text') {
            $clean['rich_content'] = wp_kses_post(substr((string) ($raw['rich_content'] ?? ''), 0, 100000));
        } elseif (($clean['type'] ?? '') === 'signature') {
            $clean['signature_mode'] = in_array($raw['signature_mode'] ?? 'both', array('draw', 'type', 'both'), true) ? ($raw['signature_mode'] ?? 'both') : 'both';
        } elseif (($clean['type'] ?? '') === 'address') {
            $clean['address_country_enabled'] = !isset($raw['address_country_enabled']) || !empty($raw['address_country_enabled']);
            $country = strtoupper(sanitize_key($raw['address_default_country'] ?? 'US'));
            $clean['address_default_country'] = isset(formorbit_phone_countries()[$country]) ? $country : 'US';
        } elseif (($clean['type'] ?? '') === 'divider') {
            $clean['divider_show_label'] = !empty($raw['divider_show_label']);
            $clean['divider_label_position'] = in_array($raw['divider_label_position'] ?? 'above', array('above', 'inline'), true) ? ($raw['divider_label_position'] ?? 'above') : 'above';
            $clean['divider_style'] = in_array($raw['divider_style'] ?? 'solid', array('solid', 'dashed', 'dotted', 'double'), true) ? ($raw['divider_style'] ?? 'solid') : 'solid';
            $clean['divider_alignment'] = in_array($raw['divider_alignment'] ?? 'center', array('left', 'center', 'right'), true) ? ($raw['divider_alignment'] ?? 'center') : 'center';
            $clean['divider_width'] = min(100, max(10, absint($raw['divider_width'] ?? 100)));
            $clean['divider_thickness'] = min(12, max(1, absint($raw['divider_thickness'] ?? 1)));
            $clean['divider_color'] = sanitize_hex_color($raw['divider_color'] ?? '') ?: '#dfe1e6';
            $clean['divider_margin_top'] = min(100, absint($raw['divider_margin_top'] ?? 10));
            $clean['divider_margin_bottom'] = min(100, absint($raw['divider_margin_bottom'] ?? 10));
            $clean['divider_padding_top'] = min(80, absint($raw['divider_padding_top'] ?? 0));
            $clean['divider_padding_bottom'] = min(80, absint($raw['divider_padding_bottom'] ?? 0));
        } elseif (in_array($clean['type'] ?? '', array('post_title', 'post_body', 'post_excerpt', 'post_tags', 'post_custom'), true)) {
            $clean['post_meta_key'] = sanitize_key($raw['post_meta_key'] ?? '');
            $clean['post_type'] = post_type_exists($raw['post_type'] ?? '') ? sanitize_key($raw['post_type']) : 'post';
            $clean['post_status'] = in_array($raw['post_status'] ?? '', array('draft', 'pending', 'publish'), true) ? sanitize_key($raw['post_status']) : 'draft';
        }
        $clean['style'] = array(
            'width' => in_array((string) ($raw['style']['width'] ?? '100'), array('auto', '100', '75', '50', '33'), true) ? (string) ($raw['style']['width'] ?? '100') : '100',
            'label_color' => sanitize_hex_color($raw['style']['label_color'] ?? '') ?: '',
            'background_color' => sanitize_hex_color($raw['style']['background_color'] ?? '') ?: '',
            'text_color' => sanitize_hex_color($raw['style']['text_color'] ?? '') ?: '',
            'radius' => min(40, absint($raw['style']['radius'] ?? 7)),
            'css_class' => sanitize_html_class($raw['style']['css_class'] ?? ''),
            'customized' => !empty($raw['style']['customized']),
        );
        $clean['row_start'] = !empty($raw['row_start']);
        if (($clean['type'] ?? '') === 'divider') {
            $clean['style']['width'] = '100';
            $clean['row_start'] = true;
        }
        return $clean;
    }

    public function render($html, $field, $id, $name) {
        $condition = ' data-field-id="' . esc_attr($field['id']) . '"' . (!empty($field['condition']['enabled']) ? ' data-condition="' . esc_attr(wp_json_encode($field['condition'])) . '"' : '');
        $custom_class = sanitize_html_class($field['style']['css_class'] ?? '');
        $label_html = wp_kses(
            apply_filters('webform_field_label_html', esc_html($field['label']), $field),
            array('span' => array('class' => true, 'aria-hidden' => true))
        );
        $label_class = !empty($field['hide_label']) ? ' class="screen-reader-text"' : '';
        if ($field['type'] === 'calculation') {
            return '<div class="webform-field webform-field-calculation ' . esc_attr($custom_class) . '"' . $condition . '><label for="' . esc_attr($id) . '"' . $label_class . '>' . $label_html . '</label><input id="' . esc_attr($id) . '" type="text" name="' . esc_attr($name) . '" readonly data-webform-calculation="' . esc_attr($field['formula'] ?? '') . '" data-decimals="' . esc_attr($field['decimal_places'] ?? 2) . '" value="0"></div>';
        }
        if ($field['type'] === 'signature') {
            $mode = in_array($field['signature_mode'] ?? 'both', array('draw', 'type', 'both'), true) ? ($field['signature_mode'] ?? 'both') : 'both';
            $chooser = $mode === 'both' ? '<div class="webform-signature-modes" role="group" aria-label="' . esc_attr__('Signing method', 'formorbit') . '"><button type="button" class="is-active" data-signature-method="draw">' . esc_html__('Draw', 'formorbit') . '</button><button type="button" data-signature-method="type">' . esc_html__('Type', 'formorbit') . '</button></div>' : '';
            $canvas = '<div class="webform-signature-draw"' . ($mode === 'type' ? ' hidden' : '') . '><canvas class="webform-signature-canvas" width="560" height="180" data-target="' . esc_attr($id) . '"></canvas><button type="button" class="webform-signature-clear">' . esc_html__('Clear signature', 'formorbit') . '</button></div>';
            $typed = '<div class="webform-signature-type"' . ($mode !== 'type' ? ' hidden' : '') . '><label for="' . esc_attr($id) . '-typed">' . esc_html__('Type your full name', 'formorbit') . '</label><input id="' . esc_attr($id) . '-typed" class="webform-signature-typed" type="text" maxlength="150" autocomplete="name" data-target="' . esc_attr($id) . '"><div class="webform-signature-typed-preview" aria-hidden="true"></div></div>';
            return '<fieldset class="webform-field webform-field-signature ' . esc_attr($custom_class) . '" data-signature-mode="' . esc_attr($mode) . '"' . $condition . '><legend' . $label_class . '>' . $label_html . '</legend>' . $chooser . $canvas . $typed . '<input id="' . esc_attr($id) . '" type="hidden" name="' . esc_attr($name) . '" value=""><span class="webform-error"></span></fieldset>';
        }
        if ($field['type'] === 'rich_text') {
            return '<div class="webform-rich-text-document ' . esc_attr($custom_class) . '"' . $condition . '><div class="webform-rich-text-content">' . wp_kses_post($field['rich_content'] ?? '') . '</div></div>';
        }
        if ($field['type'] === 'divider') {
            $divider_alignment = in_array($field['divider_alignment'] ?? 'center', array('left', 'center', 'right'), true) ? ($field['divider_alignment'] ?? 'center') : 'center';
            $label_position = ($field['divider_label_position'] ?? 'above') === 'inline' ? 'inline' : 'above';
            $show_label = !empty($field['divider_show_label']);
            $label = $show_label ? '<span class="webform-divider-label">' . $label_html . '</span>' : '';
            return '<div class="webform-divider webform-divider-align-' . esc_attr($divider_alignment) . ' webform-divider-label-' . esc_attr($label_position) . ' ' . esc_attr($custom_class) . '"' . $condition . '>' . ($label_position === 'above' ? $label : '') . '<div class="webform-divider-rule"><hr aria-hidden="true">' . ($label_position === 'inline' ? $label : '') . ($label_position === 'inline' && $show_label ? '<hr aria-hidden="true">' : '') . '</div></div>';
        }
        if ($field['type'] === 'field_group') {
            $children = (array) ($field['children'] ?? array());
            if ($children) {
                $child_html = '';
                foreach ($children as $child) {
                    $child_html .= $this->render_container_child($child, $field['id'], null);
                }
                $columns = min(4, max(1, absint($field['group_columns'] ?? 2)));
                return '<fieldset class="webform-field webform-pro-field-group webform-pro-nested-group webform-group-columns-' . esc_attr($columns) . ' ' . esc_attr($custom_class) . '"' . $condition . '><legend' . $label_class . '>' . $label_html . '</legend><div class="webform-container-grid">' . $child_html . '</div><span class="webform-error"></span></fieldset>';
            }
            return '<div class="webform-field-group-marker ' . esc_attr($custom_class) . '" data-field-id="' . esc_attr($field['id']) . '" data-group-count="' . esc_attr($field['group_count'] ?? 2) . '" data-group-columns="' . esc_attr($field['group_columns'] ?? 2) . '" data-group-label="' . esc_attr($field['label']) . '"></div>';
        }
        if ($field['type'] === 'address') {
            $required = !empty($field['required']) ? ' required' : '';
            $parts = array('street' => __('Street address', 'formorbit'), 'city' => __('City', 'formorbit'), 'state' => __('State / Province', 'formorbit'), 'postal' => __('Postal code', 'formorbit'));
            $inputs = '';
            foreach ($parts as $part => $label) {
                $autocomplete = array('street' => 'street-address', 'city' => 'address-level2', 'state' => 'address-level1', 'postal' => 'postal-code');
                $inputs .= '<label class="webform-address-' . esc_attr($part) . '"><span>' . esc_html($label) . '</span><input type="text" name="' . esc_attr($name) . '[' . esc_attr($part) . ']"' . ($part === 'street' ? $required : '') . ' autocomplete="' . esc_attr($autocomplete[$part]) . '"></label>';
            }
            if (($field['address_country_enabled'] ?? true) !== false) {
                $default_country = isset(formorbit_phone_countries()[$field['address_default_country'] ?? '']) ? $field['address_default_country'] : 'US';
                $options = '';
                foreach (formorbit_phone_countries() as $country => $prefix) $options .= '<option value="' . esc_attr($country) . '"' . selected($default_country, $country, false) . '>' . esc_html($country) . '</option>';
                $inputs .= '<label class="webform-address-country"><span>' . esc_html__('Country', 'formorbit') . '</span><select class="webform-country-select" name="' . esc_attr($name) . '[country]" autocomplete="country">' . $options . '</select></label>';
            }
            return '<fieldset class="webform-field webform-field-address ' . esc_attr($custom_class) . '"' . $condition . '><legend' . $label_class . '>' . $label_html . '</legend><div class="webform-address-grid">' . $inputs . '</div><span class="webform-error"></span></fieldset>';
        }
        if ($field['type'] === 'repeater') {
            $minimum = min(20, max(1, absint($field['repeater_min'] ?? 1)));
            $maximum = min(50, max($minimum, absint($field['repeater_max'] ?? 10)));
            $children = (array) ($field['children'] ?? array());
            if ($children) {
                $rows = '';
                for ($index = 0; $index < $minimum; $index++) {
                    $child_html = '';
                    foreach ($children as $child) {
                        $child_html .= $this->render_container_child($child, $field['id'], $index);
                    }
                    $rows .= '<div class="webform-repeater-row webform-repeater-multi-row" data-row-index="' . esc_attr($index) . '"><div class="webform-repeater-row-head"><strong>' . sprintf(esc_html__('Item %d', 'formorbit'), $index + 1) . '</strong><button type="button" class="webform-repeater-remove" aria-label="' . esc_attr__('Remove row', 'formorbit') . '">×</button></div><div class="webform-repeater-child-grid">' . $child_html . '</div></div>';
                }
                return '<fieldset class="webform-field webform-field-repeater webform-field-repeater-multi ' . esc_attr($custom_class) . '"' . $condition . ' data-min="' . esc_attr($minimum) . '" data-max="' . esc_attr($maximum) . '"><legend' . $label_class . '>' . $label_html . '</legend><div class="webform-repeater-rows">' . $rows . '</div><button type="button" class="webform-repeater-add">' . esc_html($field['repeater_button'] ?? __('Add another row', 'formorbit')) . '</button><span class="webform-error"></span></fieldset>';
            }
            $rows = '';
            for ($index = 0; $index < $minimum; $index++) $rows .= '<div class="webform-repeater-row"><input type="text" name="' . esc_attr($name) . '[]" placeholder="' . esc_attr($field['placeholder'] ?? '') . '"' . (!empty($field['required']) && $index === 0 ? ' required' : '') . '><button type="button" class="webform-repeater-remove" aria-label="' . esc_attr__('Remove row', 'formorbit') . '">×</button></div>';
            return '<div class="webform-field webform-field-repeater ' . esc_attr($custom_class) . '"' . $condition . ' data-min="' . esc_attr($minimum) . '" data-max="' . esc_attr($maximum) . '"><label' . $label_class . '>' . $label_html . '</label><div class="webform-repeater-rows">' . $rows . '</div><button type="button" class="webform-repeater-add">' . esc_html($field['repeater_button'] ?? __('Add another', 'formorbit')) . '</button><span class="webform-error"></span></div>';
        }
        if ($field['type'] === 'appointment') {
            return '<div class="webform-field webform-field-appointment ' . esc_attr($custom_class) . '"' . $condition . '><label for="' . esc_attr($id) . '"' . $label_class . '>' . $label_html . '</label><input id="' . esc_attr($id) . '" type="datetime-local" name="' . esc_attr($name) . '" min="' . esc_attr($field['min_date'] ?? '') . '" max="' . esc_attr($field['max_date'] ?? '') . '"' . (!empty($field['required']) ? ' required' : '') . '><span class="webform-error"></span></div>';
        }
        if ($field['type'] === 'nps') {
            $scores = '';
            for ($score = 0; $score <= 10; $score++) $scores .= '<label><input type="radio" name="' . esc_attr($name) . '" value="' . esc_attr($score) . '"' . (!empty($field['required']) && $score === 0 ? ' required' : '') . '><span>' . esc_html($score) . '</span></label>';
            return '<fieldset class="webform-field webform-field-nps ' . esc_attr($custom_class) . '"' . $condition . '><legend' . $label_class . '>' . $label_html . '</legend><div class="webform-nps-scale">' . $scores . '</div><div class="webform-nps-labels"><small>' . esc_html__('Not likely', 'formorbit') . '</small><small>' . esc_html__('Very likely', 'formorbit') . '</small></div><span class="webform-error"></span></fieldset>';
        }
        if ($field['type'] === 'currency') {
            return '<div class="webform-field webform-field-currency ' . esc_attr($custom_class) . '"' . $condition . '><label for="' . esc_attr($id) . '"' . $label_class . '>' . $label_html . '</label><div class="webform-currency-control"><span>' . esc_html($field['currency_symbol'] ?? '$') . '</span><input id="' . esc_attr($id) . '" type="number" step="0.01" min="' . esc_attr($field['min'] ?? 0) . '" max="' . esc_attr($field['max'] ?? 999999999) . '" name="' . esc_attr($name) . '" placeholder="' . esc_attr($field['placeholder'] ?? '0.00') . '"' . (!empty($field['required']) ? ' required' : '') . '></div><span class="webform-error"></span></div>';
        }
        if ($field['type'] === 'product') {
            $options = '';
            foreach ((array) ($field['options'] ?? array()) as $index => $option) {
                list($title, $amount) = array_pad(array_map('trim', explode('|', $option, 2)), 2, '0');
                $options .= '<label><input type="radio" name="' . esc_attr($name) . '" value="' . esc_attr($title . '|' . number_format((float) $amount, 2, '.', '')) . '"' . (!empty($field['required']) && $index === 0 ? ' required' : '') . '><span><strong>' . esc_html($title) . '</strong><em>' . esc_html(number_format((float) $amount, 2)) . '</em></span></label>';
            }
            return '<fieldset class="webform-field webform-field-product ' . esc_attr($custom_class) . '"' . $condition . '><legend' . $label_class . '>' . $label_html . '</legend><div class="webform-product-options">' . $options . '</div><span class="webform-error"></span></fieldset>';
        }
        if ($field['type'] === 'price') {
            $currency = esc_html($field['currency_code'] ?? 'USD');
            $amount = number_format((float) ($field['price_amount'] ?? 0), 2, '.', '');
            return '<div class="webform-field webform-field-price ' . esc_attr($custom_class) . '"' . $condition . '><span class="webform-price-label' . (!empty($field['hide_label']) ? ' screen-reader-text' : '') . '">' . $label_html . '</span><strong>' . $currency . ' ' . esc_html($amount) . '</strong><input type="hidden" name="' . esc_attr($name) . '" value="' . esc_attr($amount) . '"></div>';
        }
        if ($field['type'] === 'quantity' || $field['type'] === 'donation') {
            $minimum = $field['type'] === 'quantity' ? max(1, floatval($field['min'] ?? 1)) : max(0, floatval($field['min'] ?? 0));
            return '<div class="webform-field webform-field-' . esc_attr($field['type']) . ' ' . esc_attr($custom_class) . '"' . $condition . '><label for="' . esc_attr($id) . '"' . $label_class . '>' . $label_html . '</label><input id="' . esc_attr($id) . '" type="number" name="' . esc_attr($name) . '" min="' . esc_attr($minimum) . '" max="' . esc_attr($field['max'] ?? 999999) . '" step="' . esc_attr($field['step'] ?? ($field['type'] === 'quantity' ? 1 : .01)) . '" value="' . esc_attr($field['type'] === 'quantity' ? $minimum : '') . '"' . (!empty($field['required']) ? ' required' : '') . '><span class="webform-error"></span></div>';
        }
        if (in_array($field['type'], array('product_option', 'shipping'), true)) {
            $options = ''; foreach ((array) ($field['options'] ?? array()) as $index => $option) { list($title, $amount) = array_pad(array_map('trim', explode('|', $option, 2)), 2, '0'); $options .= '<option value="' . esc_attr($title . '|' . number_format((float) $amount, 2, '.', '')) . '">' . esc_html($title . ($amount !== '0' ? ' — ' . number_format((float) $amount, 2) : '')) . '</option>'; }
            return '<div class="webform-field webform-field-' . esc_attr($field['type']) . ' ' . esc_attr($custom_class) . '"' . $condition . '><label for="' . esc_attr($id) . '"' . $label_class . '>' . $label_html . '</label><select id="' . esc_attr($id) . '" name="' . esc_attr($name) . '"' . (!empty($field['required']) ? ' required' : '') . '>' . $options . '</select><span class="webform-error"></span></div>';
        }
        if ($field['type'] === 'total') {
            return '<div class="webform-field webform-field-total ' . esc_attr($custom_class) . '"' . $condition . '><span' . $label_class . '>' . $label_html . '</span><strong data-formorbit-total data-currency="' . esc_attr($field['currency_code'] ?? 'USD') . '">' . esc_html($field['currency_code'] ?? 'USD') . ' 0.00</strong></div>';
        }
        if (in_array($field['type'], array('post_title', 'post_body', 'post_excerpt', 'post_tags', 'post_custom'), true)) {
            $required = !empty($field['required']) ? ' required' : '';
            $textarea = in_array($field['type'], array('post_body', 'post_excerpt', 'post_custom'), true);
            $control = $textarea
                ? '<textarea id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" rows="' . esc_attr($field['type'] === 'post_body' ? 10 : 5) . '" placeholder="' . esc_attr($field['placeholder'] ?? '') . '"' . $required . '></textarea>'
                : '<input id="' . esc_attr($id) . '" type="text" name="' . esc_attr($name) . '" placeholder="' . esc_attr($field['placeholder'] ?? '') . '"' . $required . '>';
            return '<div class="webform-field webform-field-' . esc_attr($field['type']) . ' ' . esc_attr($custom_class) . '"' . $condition . '><label for="' . esc_attr($id) . '"' . $label_class . '>' . $label_html . '</label>' . $control . '<span class="webform-error"></span></div>';
        }
        return $html;
    }

    public function create_post_from_submission($entry_id, $form_id, $data, $form_settings) {
        $schema = (array) get_post_meta($form_id, '_webform_schema', true);
        $postarr = array('post_title' => '', 'post_content' => '', 'post_excerpt' => '', 'post_status' => 'draft', 'post_type' => 'post');
        $tags = array();
        $meta = array();
        $found = false;
        foreach ($schema as $stage) {
            foreach ((array) ($stage['fields'] ?? array()) as $field) {
                $type = $field['type'] ?? '';
                if (!in_array($type, array('post_title', 'post_body', 'post_excerpt', 'post_tags', 'post_custom'), true)) continue;
                $found = true;
                $value = $data[$field['id']]['value'] ?? '';
                if (is_array($value)) $value = implode(', ', array_map('sanitize_text_field', $value));
                if ($type === 'post_title') {
                    $postarr['post_title'] = sanitize_text_field($value);
                    $postarr['post_type'] = post_type_exists($field['post_type'] ?? '') ? sanitize_key($field['post_type']) : 'post';
                    $postarr['post_status'] = in_array($field['post_status'] ?? '', array('draft', 'pending', 'publish'), true) ? $field['post_status'] : 'draft';
                } elseif ($type === 'post_body') $postarr['post_content'] = wp_kses_post($value);
                elseif ($type === 'post_excerpt') $postarr['post_excerpt'] = sanitize_textarea_field($value);
                elseif ($type === 'post_tags') $tags = array_values(array_filter(array_map('sanitize_text_field', array_map('trim', explode(',', (string) $value)))));
                elseif ($type === 'post_custom' && !empty($field['post_meta_key'])) $meta[sanitize_key($field['post_meta_key'])] = sanitize_textarea_field($value);
            }
        }
        if (!$found) return;
        if (!$postarr['post_title']) $postarr['post_title'] = sprintf(__('Form submission #%d', 'formorbit'), $entry_id);
        $user_id = absint(get_post_meta($entry_id, '_webform_user_id', true));
        if ($user_id) $postarr['post_author'] = $user_id;
        $post_id = wp_insert_post(wp_slash($postarr), true);
        if (is_wp_error($post_id)) {
            update_post_meta($entry_id, '_formorbit_post_error', sanitize_text_field($post_id->get_error_message()));
            return;
        }
        if ($tags && is_object_in_taxonomy($postarr['post_type'], 'post_tag')) wp_set_post_tags($post_id, $tags, false);
        foreach ($meta as $key => $value) update_post_meta($post_id, $key, $value);
        update_post_meta($entry_id, '_formorbit_created_post_id', $post_id);
    }

    public function value($value, $field, $form_id, $posted) {
        if ($field['type'] === 'calculation') return $this->calculate($field['formula'] ?? '', $posted, absint($field['decimal_places'] ?? 2));
        if ($field['type'] === 'signature') return $this->save_signature($value);
        if (in_array($field['type'] ?? '', array('field_group', 'repeater'), true) && !empty($field['children'])) {
            return $this->format_container_value($value, $field);
        }
        return $value;
    }

    public function process($process, $field) {
        if (($field['type'] ?? '') === 'field_group') return !empty($field['children']) ? $process : false;
        return in_array($field['type'] ?? '', array('rich_text', 'divider'), true) ? false : $process;
    }

    public function allowed_html($allowed, $field) {
        $allowed['div'] = array_merge($allowed['div'] ?? array(), array('class' => true, 'data-*' => true));
        $allowed['fieldset'] = array_merge($allowed['fieldset'] ?? array(), array('class' => true, 'style' => true, 'data-*' => true));
        $allowed['legend'] = array_merge($allowed['legend'] ?? array(), array('class' => true));
        $allowed['label'] = array_merge($allowed['label'] ?? array(), array('class' => true, 'for' => true));
        $allowed['input'] = array_merge($allowed['input'] ?? array(), array(
            'id' => true,
            'class' => true,
            'type' => true,
            'name' => true,
            'value' => true,
            'readonly' => true,
            'required' => true,
            'checked' => true,
            'disabled' => true,
            'min' => true,
            'max' => true,
            'step' => true,
            'placeholder' => true,
            'autocomplete' => true,
            'aria-describedby' => true,
            'data-*' => true,
        ));
        $allowed['button'] = array_merge($allowed['button'] ?? array(), array(
            'type' => true,
            'class' => true,
            'disabled' => true,
            'aria-label' => true,
        ));
        $allowed['span'] = array_merge($allowed['span'] ?? array(), array(
            'class' => true,
            'id' => true,
            'aria-hidden' => true,
        ));
        $allowed['textarea'] = array_merge($allowed['textarea'] ?? array(), array(
            'id' => true,
            'class' => true,
            'name' => true,
            'rows' => true,
            'placeholder' => true,
            'required' => true,
            'aria-describedby' => true,
        ));
        $allowed['select'] = array_merge($allowed['select'] ?? array(), array(
            'id' => true,
            'class' => true,
            'name' => true,
            'required' => true,
            'aria-describedby' => true,
        ));
        $allowed['option'] = array_merge($allowed['option'] ?? array(), array(
            'value' => true,
            'selected' => true,
        ));
        $allowed['canvas'] = array('class' => true, 'width' => true, 'height' => true, 'data-target' => true);
        $allowed['hr'] = array_merge($allowed['hr'] ?? array(), array('class' => true, 'aria-hidden' => true));
        $allowed['h3'] = array_merge($allowed['h3'] ?? array(), array('class' => true));
        $allowed['b'] = array_merge($allowed['b'] ?? array(), array('class' => true));
        if (($field['type'] ?? '') === 'divider') {
            return $allowed;
        }
        $contains_rich_child = in_array($field['type'] ?? '', array('field_group', 'repeater'), true) && array_filter((array) ($field['children'] ?? array()), function ($child) {
            return in_array($child['type'] ?? '', array('html', 'rich_text'), true);
        });
        if (($field['type'] ?? '') !== 'rich_text' && !$contains_rich_child) return $allowed;
        $post_html = wp_kses_allowed_html('post');
        foreach ($post_html as $tag => $attributes) {
            $allowed[$tag] = isset($allowed[$tag]) ? array_merge($allowed[$tag], $attributes) : $attributes;
        }
        return $allowed;
    }

    public function validate($error, $value, $field, $form_id = 0, $posted = array()) {
        if (in_array($field['type'] ?? '', array('field_group', 'repeater'), true) && !empty($field['children'])) {
            $container_error = $this->validate_container_value($posted[$field['id']] ?? array(), $field);
            if ($container_error) return $container_error;
        }
        if (($field['type'] ?? '') === 'product' && $value && !in_array($value, (array) ($field['options'] ?? array()), true)) return __('Select a valid product.', 'formorbit');
        if (($field['type'] ?? '') === 'nps' && $value !== '' && (!is_numeric($value) || $value < 0 || $value > 10)) return __('Select a valid score.', 'formorbit');
        return $error;
    }

    public function public_assets() {
        wp_enqueue_style('dashicons');
        wp_enqueue_style('webform-pro-public', WEBFORM_URL . 'assets/advanced/public.css', array('webform-public'), WEBFORM_VERSION);
        wp_enqueue_style('webform-pro-field-styles', WEBFORM_URL . 'assets/advanced/field-styles.css', array('webform-pro-public'), WEBFORM_VERSION);
        wp_enqueue_script('webform-pro-public', WEBFORM_URL . 'assets/advanced/public.js', array('webform-public'), WEBFORM_VERSION, true);
    }

    public function field_label_html($html, $field) {
        $icon = sanitize_key($field['icon'] ?? '');
        $icons = $this->icons();
        if (!$icon || !isset($icons[$icon])) return $html;
        return '<span class="dashicons ' . esc_attr($icons[$icon]) . ' webform-field-icon" aria-hidden="true"></span>' . $html;
    }

    private function icons() {
        return array(
            '' => '',
            'user' => 'dashicons-admin-users',
            'groups' => 'dashicons-groups',
            'email' => 'dashicons-email-alt',
            'phone' => 'dashicons-phone',
            'location' => 'dashicons-location-alt',
            'home' => 'dashicons-admin-home',
            'business' => 'dashicons-building',
            'calendar' => 'dashicons-calendar-alt',
            'clock' => 'dashicons-clock',
            'link' => 'dashicons-admin-links',
            'cart' => 'dashicons-cart',
            'money' => 'dashicons-money-alt',
            'portfolio' => 'dashicons-portfolio',
            'clipboard' => 'dashicons-clipboard',
            'document' => 'dashicons-media-document',
            'book' => 'dashicons-book',
            'edit' => 'dashicons-edit',
            'upload' => 'dashicons-upload',
            'download' => 'dashicons-download',
            'image' => 'dashicons-format-image',
            'camera' => 'dashicons-camera',
            'video' => 'dashicons-video-alt3',
            'audio' => 'dashicons-format-audio',
            'chart' => 'dashicons-chart-bar',
            'trend' => 'dashicons-chart-line',
            'ticket' => 'dashicons-tickets-alt',
            'heart' => 'dashicons-heart',
            'star' => 'dashicons-star-filled',
            'shield' => 'dashicons-shield',
            'info' => 'dashicons-info-outline',
            'warning' => 'dashicons-warning',
            'check' => 'dashicons-yes-alt',
            'website' => 'dashicons-admin-site-alt3',
            'network' => 'dashicons-admin-multisite',
            'feedback' => 'dashicons-feedback',
            'food' => 'dashicons-food',
            'pets' => 'dashicons-pets',
            'travel' => 'dashicons-airplane',
            'contact' => 'dashicons-id-alt',
            'store' => 'dashicons-store',
            'tag' => 'dashicons-tag',
            'calculator' => 'dashicons-calculator',
            'analytics' => 'dashicons-analytics',
            'pie' => 'dashicons-chart-pie',
            'announcement' => 'dashicons-megaphone',
            'chat' => 'dashicons-format-chat',
            'quote' => 'dashicons-editor-quote',
            'help' => 'dashicons-editor-help',
            'idea' => 'dashicons-lightbulb',
            'attachment' => 'dashicons-paperclip',
            'folder' => 'dashicons-open-folder',
            'database' => 'dashicons-database',
            'cloud' => 'dashicons-cloud',
            'desktop' => 'dashicons-desktop',
            'laptop' => 'dashicons-laptop',
            'tablet' => 'dashicons-tablet',
            'mobile' => 'dashicons-smartphone',
            'settings' => 'dashicons-admin-settings',
            'tools' => 'dashicons-admin-tools',
            'lock' => 'dashicons-lock',
            'coffee' => 'dashicons-coffee',
            'award' => 'dashicons-awards',
            'thumbs_up' => 'dashicons-thumbs-up',
            'flag' => 'dashicons-flag',
            'search' => 'dashicons-search',
            'forms' => 'dashicons-forms',
            'bell' => 'dashicons-bell',
            'comments' => 'dashicons-admin-comments',
            'archive' => 'dashicons-archive',
            'category' => 'dashicons-category',
            'car' => 'dashicons-car',
            'code' => 'dashicons-editor-code',
            'ordered_list' => 'dashicons-editor-ol',
            'table' => 'dashicons-editor-table',
            'list' => 'dashicons-editor-ul',
            'external' => 'dashicons-external',
            'marker' => 'dashicons-marker',
            'spreadsheet' => 'dashicons-media-spreadsheet',
            'products' => 'dashicons-products',
            'api' => 'dashicons-rest-api',
            'schedule' => 'dashicons-schedule',
            'share' => 'dashicons-share',
            'support' => 'dashicons-sos',
            'language' => 'dashicons-translation',
            'accessibility' => 'dashicons-universal-access',
            'visible' => 'dashicons-visibility',
            'whatsapp' => 'dashicons-whatsapp',
            'youtube' => 'dashicons-youtube',
            'community' => 'dashicons-buddicons-community',
            'activity' => 'dashicons-buddicons-activity',
            'private_message' => 'dashicons-buddicons-pm',
        );
    }

    public function google_font() {
        if (!is_singular()) return;
        global $post;
        if (!$post || !preg_match('/\[(?:formorbit|webform)\b/i', (string) $post->post_content)) return;
        preg_match_all('/\[(?:formorbit|webform)[^\]]*id=["\']?(\d+)/i', (string) $post->post_content, $matches);
        $families = array();
        $map = array('"Inter",sans-serif' => 'Inter:wght@400;500;600;700', '"Roboto",sans-serif' => 'Roboto:wght@400;500;700', '"Open Sans",sans-serif' => 'Open+Sans:wght@400;600;700', '"Poppins",sans-serif' => 'Poppins:wght@400;500;600;700', '"Montserrat",sans-serif' => 'Montserrat:wght@400;500;600;700', '"Lato",sans-serif' => 'Lato:wght@400;700', '"Playfair Display",serif' => 'Playfair+Display:wght@400;600;700');
        foreach ((array) ($matches[1] ?? array()) as $form_id) {
            $settings = (array) get_post_meta(absint($form_id), '_webform_settings', true);
            if (isset($map[$settings['font_family'] ?? ''])) $families[] = $map[$settings['font_family']];
        }
        if ($families) wp_enqueue_style('webform-pro-google-fonts', 'https://fonts.googleapis.com/css2?family=' . implode('&family=', array_unique($families)) . '&display=swap', array(), null);
    }

    private function sanitize_container_children($children) {
        // Every leaf field can be used in a container. Containers and page breaks
        // are intentionally excluded to prevent recursive schemas and invalid stages.
        $allowed_types = array('name', 'text', 'email', 'textarea', 'select', 'radio', 'checkbox', 'number', 'date', 'time', 'phone', 'url', 'file', 'consent', 'poll', 'quiz', 'rating', 'slider', 'hidden', 'html', 'heading', 'rich_text', 'divider', 'signature', 'address', 'appointment', 'nps', 'currency', 'product', 'price', 'quantity', 'product_option', 'shipping', 'donation', 'total', 'calculation', 'post_title', 'post_body', 'post_excerpt', 'post_tags', 'post_custom');
        $clean = array();
        $used_ids = array();
        foreach (array_slice((array) $children, 0, 100) as $index => $child) {
            if (!is_array($child)) continue;
            $type = sanitize_key($child['type'] ?? 'text');
            if (!in_array($type, $allowed_types, true)) $type = 'text';
            $id = sanitize_key($child['id'] ?? '');
            if (!$id || isset($used_ids[$id])) $id = 'child_' . ($index + 1) . '_' . substr(md5(wp_json_encode($child)), 0, 6);
            $used_ids[$id] = true;
            $options = array();
            foreach (array_slice((array) ($child['options'] ?? array()), 0, 50) as $option) {
                if (is_array($option)) continue;
                $option = substr(sanitize_text_field($option), 0, 200);
                if ($option !== '') $options[] = $option;
            }
            $clean[] = array(
                'id' => $id,
                'type' => $type,
                'label' => substr(sanitize_text_field($child['label'] ?? ucfirst($type)), 0, 200),
                'placeholder' => substr(sanitize_text_field($child['placeholder'] ?? ''), 0, 300),
                'required' => !empty($child['required']),
                'options' => $options,
                'choice_columns' => min(4, max(1, absint($child['choice_columns'] ?? 1))),
                'child_width' => in_array((string) ($child['child_width'] ?? '100'), array('25', '33', '50', '66', '75', '100'), true) ? (string) ($child['child_width'] ?? '100') : '100',
                'rows' => min(20, max(2, absint($child['rows'] ?? 4))),
                'min' => is_numeric($child['min'] ?? null) ? (string) floatval($child['min']) : '',
                'max' => is_numeric($child['max'] ?? null) ? (string) floatval($child['max']) : '',
                'step' => is_numeric($child['step'] ?? null) && floatval($child['step']) > 0 ? (string) floatval($child['step']) : '1',
                'default_value' => substr(sanitize_text_field($child['default_value'] ?? ''), 0, 500),
                'html' => wp_kses_post(substr((string) ($child['html'] ?? ''), 0, 50000)),
                'rich_content' => wp_kses_post(substr((string) ($child['rich_content'] ?? ''), 0, 100000)),
                'currency_symbol' => substr(sanitize_text_field($child['currency_symbol'] ?? '$'), 0, 5),
                'currency_code' => in_array(strtoupper($child['currency_code'] ?? 'USD'), array('USD', 'EUR', 'GBP', 'CAD', 'AUD', 'BDT'), true) ? strtoupper($child['currency_code'] ?? 'USD') : 'USD',
                'price_amount' => max(0, floatval($child['price_amount'] ?? 0)),
                'formula' => preg_replace('/[^a-zA-Z0-9_{}+\-*\/().,%^<>=!&|\s]/', '', $child['formula'] ?? ''),
            );
        }
        return $clean;
    }

    private function render_container_child($child, $parent_id, $row_index = null) {
        $child_id = sanitize_key($child['id'] ?? '');
        if (!$child_id) return '';
        $type = sanitize_key($child['type'] ?? 'text');
        $dom_id = 'webform-' . sanitize_key($parent_id) . '-' . ($row_index === null ? 'group' : 'row-' . absint($row_index)) . '-' . $child_id;
        $base_name = 'fields[' . sanitize_key($parent_id) . ']' . ($row_index === null ? '' : '[' . absint($row_index) . ']') . '[' . $child_id . ']';
        $label = esc_html($child['label'] ?? ucfirst($type));
        $required = !empty($child['required']);
        $required_attr = $required ? ' required' : '';
        $required_label = $required ? ' <em>*</em>' : '';
        $placeholder = esc_attr($child['placeholder'] ?? '');
        $class = 'webform-container-child-field webform-container-child-' . sanitize_html_class($type);
        $child_width = in_array((string) ($child['child_width'] ?? '100'), array('25', '33', '50', '66', '75', '100'), true) ? (string) $child['child_width'] : '100';
        $class .= ' webform-container-child-width-' . $child_width;
        if ($type === 'product') $class .= ' webform-field-product';

        if ($type === 'heading') {
            return '<div class="' . esc_attr($class) . '"><h3>' . $label . '</h3></div>';
        }
        if ($type === 'html') {
            return '<div class="' . esc_attr($class) . '">' . wp_kses_post($child['html'] ?? '') . '</div>';
        }
        if ($type === 'rich_text') {
            return '<div class="' . esc_attr($class) . ' webform-rich-text-content">' . wp_kses_post($child['rich_content'] ?? '') . '</div>';
        }
        if ($type === 'divider') {
            return '<div class="' . esc_attr($class) . '"><hr></div>';
        }
        if ($type === 'hidden') {
            return '<input id="' . esc_attr($dom_id) . '" type="hidden" name="' . esc_attr($base_name) . '" value="' . esc_attr($child['default_value'] ?? '') . '">';
        }
        if (in_array($type, array('textarea', 'post_body', 'post_excerpt'), true)) {
            return '<label class="' . esc_attr($class) . '" for="' . esc_attr($dom_id) . '"><span>' . $label . $required_label . '</span><textarea id="' . esc_attr($dom_id) . '" name="' . esc_attr($base_name) . '" rows="' . esc_attr(min(20, max(2, absint($child['rows'] ?? 4)))) . '" placeholder="' . $placeholder . '"' . $required_attr . '></textarea></label>';
        }
        if ($type === 'select') {
            $options = '<option value="">' . esc_html__('Select an option', 'formorbit') . '</option>';
            foreach ((array) ($child['options'] ?? array()) as $option) {
                $options .= '<option value="' . esc_attr($option) . '">' . esc_html($option) . '</option>';
            }
            return '<label class="' . esc_attr($class) . '" for="' . esc_attr($dom_id) . '"><span>' . $label . $required_label . '</span><select id="' . esc_attr($dom_id) . '" name="' . esc_attr($base_name) . '"' . $required_attr . '>' . $options . '</select></label>';
        }
        if (in_array($type, array('radio', 'checkbox', 'poll', 'quiz', 'product', 'product_option', 'shipping'), true)) {
            $choices = '';
            foreach ((array) ($child['options'] ?? array()) as $index => $option) {
                $choice_id = $dom_id . '-' . $index;
                $input_type = $type === 'checkbox' ? 'checkbox' : 'radio';
                $choice_name = $base_name . ($type === 'checkbox' ? '[]' : '');
                $display = in_array($type, array('product', 'product_option', 'shipping'), true) ? str_replace('|', ' — ', $option) : $option;
                $choices .= '<label for="' . esc_attr($choice_id) . '"><input id="' . esc_attr($choice_id) . '" type="' . esc_attr($input_type) . '" name="' . esc_attr($choice_name) . '" value="' . esc_attr($option) . '"' . ($required && $index === 0 ? ' required' : '') . '><span>' . esc_html($display) . '</span></label>';
            }
            return '<fieldset class="' . esc_attr($class) . '"><legend>' . $label . $required_label . '</legend><div class="webform-container-choices webform-choice-columns-' . esc_attr(min(4, max(1, absint($child['choice_columns'] ?? 1)))) . '">' . $choices . '</div></fieldset>';
        }
        if ($type === 'consent') {
            return '<label class="' . esc_attr($class) . ' webform-container-consent" for="' . esc_attr($dom_id) . '"><input id="' . esc_attr($dom_id) . '" type="checkbox" name="' . esc_attr($base_name) . '" value="1"' . $required_attr . '><span>' . $label . $required_label . '</span></label>';
        }
        if (in_array($type, array('rating', 'nps'), true)) {
            $maximum = $type === 'rating' ? 5 : 10;
            $choices = '';
            for ($value = $type === 'rating' ? 1 : 0; $value <= $maximum; $value++) {
                $choice_id = $dom_id . '-' . $value;
                $choices .= '<label for="' . esc_attr($choice_id) . '"><input id="' . esc_attr($choice_id) . '" type="radio" name="' . esc_attr($base_name) . '" value="' . esc_attr($value) . '"' . ($required && $value === ($type === 'rating' ? 1 : 0) ? ' required' : '') . '><span>' . esc_html($type === 'rating' ? '★' : $value) . '</span></label>';
            }
            return '<fieldset class="' . esc_attr($class) . '"><legend>' . $label . $required_label . '</legend><div class="webform-container-choices webform-container-scale">' . $choices . '</div></fieldset>';
        }
        if ($type === 'signature') {
            return '<fieldset class="' . esc_attr($class) . ' webform-field-signature"><legend>' . $label . $required_label . '</legend><canvas class="webform-signature-canvas" width="560" height="180" data-target="' . esc_attr($dom_id) . '"></canvas><input id="' . esc_attr($dom_id) . '" type="hidden" name="' . esc_attr($base_name) . '" value=""><button type="button" class="webform-signature-clear">' . esc_html__('Clear signature', 'formorbit') . '</button></fieldset>';
        }
        if ($type === 'price') {
            $amount = number_format((float) ($child['price_amount'] ?? 0), 2, '.', '');
            return '<div class="' . esc_attr($class) . ' webform-field-price"><span>' . $label . '</span><strong>' . esc_html($child['currency_code'] ?? 'USD') . ' ' . esc_html($amount) . '</strong><input type="hidden" name="' . esc_attr($base_name) . '" value="' . esc_attr($amount) . '"></div>';
        }
        if (in_array($type, array('calculation', 'total'), true)) {
            return '<label class="' . esc_attr($class) . '" for="' . esc_attr($dom_id) . '"><span>' . $label . '</span><input id="' . esc_attr($dom_id) . '" type="number" name="' . esc_attr($base_name) . '" value="0" readonly></label>';
        }

        if ($type === 'file') {
            return '<label class="' . esc_attr($class) . '" for="' . esc_attr($dom_id) . '"><span>' . $label . $required_label . '</span><input id="' . esc_attr($dom_id) . '" type="file" name="' . esc_attr($base_name) . '"' . $required_attr . '></label>';
        }

        $input_type = array('email' => 'email', 'number' => 'number', 'quantity' => 'number', 'date' => 'date', 'time' => 'time', 'phone' => 'tel', 'url' => 'url', 'slider' => 'range', 'appointment' => 'datetime-local', 'currency' => 'number', 'donation' => 'number')[$type] ?? 'text';
        $number_attributes = '';
        if (in_array($type, array('number', 'quantity', 'slider', 'currency', 'donation'), true)) {
            if (($child['min'] ?? '') !== '') $number_attributes .= ' min="' . esc_attr($child['min']) . '"';
            if (($child['max'] ?? '') !== '') $number_attributes .= ' max="' . esc_attr($child['max']) . '"';
            $number_attributes .= ' step="' . esc_attr($child['step'] ?? '1') . '"';
        }
        $prefix = in_array($type, array('currency', 'donation'), true) ? '<b class="webform-container-currency-prefix">' . esc_html($child['currency_symbol'] ?? '$') . '</b>' : '';
        return '<label class="' . esc_attr($class) . '" for="' . esc_attr($dom_id) . '"><span>' . $label . $required_label . '</span><span class="webform-container-input-wrap">' . $prefix . '<input id="' . esc_attr($dom_id) . '" type="' . esc_attr($input_type) . '" name="' . esc_attr($base_name) . '" placeholder="' . $placeholder . '"' . $number_attributes . $required_attr . '></span></label>';
    }

    private function format_container_value($value, $field) {
        $children = (array) ($field['children'] ?? array());
        $rows = ($field['type'] ?? '') === 'repeater' ? (array) $value : array((array) $value);
        $formatted_rows = array();
        foreach ($rows as $row_index => $row) {
            if (!is_array($row)) continue;
            $parts = array();
            foreach ($children as $child) {
                $child_id = sanitize_key($child['id'] ?? '');
                if (!$child_id) continue;
                $child_value = $this->sanitize_container_child_value($row[$child_id] ?? '', $child);
                if (is_array($child_value)) $child_value = implode(', ', $child_value);
                if ($child_value === '') continue;
                $parts[] = sanitize_text_field($child['label'] ?? ucfirst($child['type'] ?? 'field')) . ': ' . $child_value;
            }
            if ($parts) {
                $formatted_rows[] = ($field['type'] ?? '') === 'repeater'
                    ? sprintf(__('Item %d — %s', 'formorbit'), absint($row_index) + 1, implode('; ', $parts))
                    : implode('; ', $parts);
            }
        }
        return implode("\n", $formatted_rows);
    }

    private function sanitize_container_child_value($value, $child) {
        $type = $child['type'] ?? 'text';
        if (in_array($type, array('heading', 'html', 'rich_text', 'divider'), true)) return '';
        if ($type === 'signature') return is_string($value) ? $this->save_signature($value) : '';
        if ($type === 'checkbox') {
            $allowed = (array) ($child['options'] ?? array());
            $clean = array();
            foreach ((array) $value as $item) {
                if (is_array($item)) continue;
                $item = sanitize_text_field($item);
                if (in_array($item, $allowed, true)) $clean[] = $item;
            }
            return array_values($clean);
        }
        if (is_array($value)) return '';
        if ($type === 'email') return sanitize_email($value);
        if ($type === 'url') return esc_url_raw($value);
        if ($type === 'textarea') return sanitize_textarea_field($value);
        if (in_array($type, array('number', 'slider', 'currency', 'rating', 'nps', 'price', 'calculation'), true)) return is_numeric($value) ? (string) floatval($value) : '';
        if ($type === 'consent') return !empty($value) ? __('Yes', 'formorbit') : '';
        return sanitize_text_field($value);
    }

    private function validate_container_value($value, $field) {
        $children = (array) ($field['children'] ?? array());
        $rows = ($field['type'] ?? '') === 'repeater' ? (array) $value : array((array) $value);
        $maximum = min(50, max(1, absint($field['repeater_max'] ?? 10)));
        if (($field['type'] ?? '') === 'repeater' && count($rows) > $maximum) return __('This repeater contains too many rows.', 'formorbit');
        foreach ($rows as $row) {
            $row = is_array($row) ? $row : array();
            foreach ($children as $child) {
                $child_value = $row[$child['id']] ?? '';
                $empty = is_array($child_value)
                    ? !array_filter($child_value, function ($item) { return !is_array($item) && trim((string) $item) !== ''; })
                    : trim((string) $child_value) === '';
                $label = sanitize_text_field($child['label'] ?? __('Field', 'formorbit'));
                if (!empty($child['required']) && $empty) return sprintf(__('%s is required.', 'formorbit'), $label);
                if ($empty) continue;
                if (is_array($child_value) && ($child['type'] ?? '') !== 'checkbox') return sprintf(__('%s contains an invalid value.', 'formorbit'), $label);
                if (($child['type'] ?? '') === 'email' && !is_email($child_value)) return sprintf(__('%s must contain a valid email address.', 'formorbit'), $label);
                if (($child['type'] ?? '') === 'url' && !filter_var($child_value, FILTER_VALIDATE_URL)) return sprintf(__('%s must contain a valid URL.', 'formorbit'), $label);
                if (in_array($child['type'] ?? '', array('number', 'slider', 'currency', 'rating', 'nps', 'price', 'calculation'), true) && !is_numeric($child_value)) return sprintf(__('%s must contain a number.', 'formorbit'), $label);
                if (in_array($child['type'] ?? '', array('select', 'radio', 'poll', 'quiz', 'product'), true) && !in_array($child_value, (array) ($child['options'] ?? array()), true)) return sprintf(__('Select a valid option for %s.', 'formorbit'), $label);
                if (($child['type'] ?? '') === 'checkbox') {
                    foreach ((array) $child_value as $selected) {
                        if (is_array($selected) || !in_array($selected, (array) ($child['options'] ?? array()), true)) return sprintf(__('Select valid options for %s.', 'formorbit'), $label);
                    }
                }
            }
        }
        return '';
    }

    private function calculate($formula, $posted, $decimals) {
        $expression = preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function ($match) use ($posted) {
            $value = $posted[$match[1]] ?? 0;
            return (string) $this->numeric_value($value);
        }, $formula);
        if (!preg_match('/^[a-zA-Z0-9_+\-*\/().,%^<>=!&|\s]+$/', $expression)) return '0';
        return number_format($this->evaluate($expression), min(6, $decimals), '.', '');
    }

    private function numeric_value($value) {
        if (is_array($value)) {
            return array_sum(array_map(array($this, 'numeric_value'), $value));
        }
        $value = (string) $value;
        if (strpos($value, '|') !== false) $value = substr($value, strrpos($value, '|') + 1);
        return is_numeric($value) ? floatval($value) : 0.0;
    }

    private function evaluate($expression) {
        preg_match_all('/\d*\.?\d+(?:e[+\-]?\d+)?|[a-z_][a-z0-9_]*|>=|<=|==|!=|&&|\|\||[()+\-*\/%^,<>=!]/i', $expression, $matches);
        $tokens = $matches[0];
        $position = 0;
        $peek = function () use (&$tokens, &$position) {
            return $tokens[$position] ?? null;
        };
        $take = function ($value) use (&$tokens, &$position) {
            if (($tokens[$position] ?? null) !== $value) return false;
            $position++;
            return true;
        };
        $comparison = null;
        $primary = $unary = $power = $term = $addition = null;
        $primary = function () use (&$primary, &$comparison, &$tokens, &$position, $peek, $take) {
            if ($take('(')) {
                $value = $comparison();
                $take(')');
                return $value;
            }
            $token = $tokens[$position++] ?? '0';
            if (is_numeric($token)) return floatval($token);
            if (preg_match('/^[a-z_]/i', $token) && $take('(')) {
                $args = array();
                if ($peek() !== ')') {
                    do {
                        $args[] = $comparison();
                    } while ($take(','));
                }
                $take(')');
                $name = strtolower($token);
                if ($name === 'sum') return array_sum($args);
                if ($name === 'avg') return $args ? array_sum($args) / count($args) : 0;
                if ($name === 'min') return $args ? min($args) : 0;
                if ($name === 'max') return $args ? max($args) : 0;
                if ($name === 'round') return round($args[0] ?? 0, min(8, max(0, absint($args[1] ?? 0))));
                if ($name === 'ceil') return ceil($args[0] ?? 0);
                if ($name === 'floor') return floor($args[0] ?? 0);
                if ($name === 'abs') return abs($args[0] ?? 0);
                if ($name === 'sqrt') return sqrt(max(0, $args[0] ?? 0));
                if ($name === 'pow') return pow($args[0] ?? 0, $args[1] ?? 0);
                if ($name === 'clamp') return min($args[2] ?? 0, max($args[1] ?? 0, $args[0] ?? 0));
                if ($name === 'if') return !empty($args[0]) ? ($args[1] ?? 0) : ($args[2] ?? 0);
            }
            return 0;
        };
        $unary = function () use (&$unary, &$primary, $take) {
            if ($take('-')) return -$unary();
            if ($take('+')) return $unary();
            if ($take('!')) return $unary() ? 0 : 1;
            return $primary();
        };
        $power = function () use (&$power, &$unary, $take) {
            $left = $unary();
            return $take('^') ? pow($left, $power()) : $left;
        };
        $term = function () use (&$power, &$tokens, &$position, $peek) {
            $value = $power();
            while (in_array($peek(), array('*', '/', '%'), true)) {
                $operator = $tokens[$position++];
                $right = $power();
                if ($operator === '*') $value *= $right;
                elseif ($operator === '/') $value = $right == 0 ? 0 : $value / $right;
                else $value = $right == 0 ? 0 : fmod($value, $right);
            }
            return $value;
        };
        $addition = function () use (&$term, &$tokens, &$position, $peek) {
            $value = $term();
            while (in_array($peek(), array('+', '-'), true)) {
                $operator = $tokens[$position++];
                $right = $term();
                $value = $operator === '+' ? $value + $right : $value - $right;
            }
            return $value;
        };
        $comparison = function () use (&$addition, &$tokens, &$position, $peek) {
            $value = $addition();
            while (in_array($peek(), array('>', '<', '>=', '<=', '==', '!=', '&&', '||'), true)) {
                $operator = $tokens[$position++];
                $right = $addition();
                if ($operator === '>') $value = $value > $right ? 1 : 0;
                elseif ($operator === '<') $value = $value < $right ? 1 : 0;
                elseif ($operator === '>=') $value = $value >= $right ? 1 : 0;
                elseif ($operator === '<=') $value = $value <= $right ? 1 : 0;
                elseif ($operator === '==') $value = $value == $right ? 1 : 0;
                elseif ($operator === '!=') $value = $value != $right ? 1 : 0;
                elseif ($operator === '&&') $value = $value && $right ? 1 : 0;
                else $value = $value || $right ? 1 : 0;
            }
            return is_finite((float) $value) ? (float) $value : 0;
        };
        return $comparison();
    }

    private function sanitize_custom_css($css) {
        $css = substr(wp_strip_all_tags((string) $css), 0, 10000);
        $css = preg_replace('#/\*.*?\*/#s', '', $css);
        $css = preg_replace('/@(?:import|charset|namespace)[^;]*;?/i', '', $css);
        $css = preg_replace('/(?:expression|javascript|vbscript|behavior|-moz-binding)\s*[:(]/i', '', $css);
        $css = preg_replace('/url\s*\([^)]*\)/i', '', $css);
        $css = preg_replace('/(?:https?:|data:|\/\/)/i', '', $css);
        return str_replace(array('<', '>'), '', $css);
    }

    private function scope_custom_css($css, $scope) {
        $safe = '';
        foreach (explode('}', $this->sanitize_custom_css($css)) as $rule) {
            if (strpos($rule, '{') === false) continue;
            list($selectors, $declarations) = array_map('trim', explode('{', $rule, 2));
            if (!$selectors || !$declarations || strpos($selectors, '@') !== false) continue;
            $scoped = array();
            foreach (explode(',', $selectors) as $selector) {
                $selector = trim($selector);
                if (!$selector || preg_match('/(^|\s)(html|body|:root)(\s|$|[.#:\[])/i', $selector)) continue;
                $scoped[] = strpos($selector, $scope) === 0 ? $selector : $scope . ' ' . $selector;
            }
            if ($scoped) $safe .= implode(',', $scoped) . '{' . $declarations . '}';
        }
        return $safe;
    }

    private function save_signature($data) {
        if (!$data || strpos($data, 'data:image/png;base64,') !== 0) return '';
        $binary = base64_decode(substr($data, 22), true);
        if (!$binary || strlen($binary) > 2 * MB_IN_BYTES || substr($binary, 1, 3) !== 'PNG') return '';
        $upload = wp_upload_bits('webform-signature-' . wp_generate_uuid4() . '.png', null, $binary);
        return empty($upload['error']) ? esc_url_raw($upload['url']) : '';
    }
}
