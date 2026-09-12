<?php
/**
 * Shared appearance controls for FormOrbit.
 *
 * @package FormOrbit
 */
if (!defined('ABSPATH')) exit;
add_action('wp_enqueue_scripts', function () { wp_enqueue_style('formorbit-free-presets', WEBFORM_URL . 'assets/css/free-presets.css', array(), WEBFORM_VERSION); });
add_action('admin_enqueue_scripts', function () {
    if (wp_script_is('webform-admin', 'enqueued')) wp_enqueue_style('formorbit-appearance', WEBFORM_URL . 'assets/css/appearance.css', array('webform-admin'), WEBFORM_VERSION);
    if (wp_script_is('webform-admin', 'enqueued')) wp_enqueue_style('formorbit-free-presets', WEBFORM_URL . 'assets/css/free-presets.css', array('formorbit-appearance'), WEBFORM_VERSION);
});
add_action('webform_confirmation_message_editor', function ($settings) {
    if (defined('WEBFORM_PRO_PLUGIN_VERSION')) return;
    wp_editor($settings['success_message'] ?? __('Thanks! Your response has been submitted.', 'formorbit'), 'webform-success-message', array('textarea_name' => 'success_message', 'textarea_rows' => 8, 'media_buttons' => true));
});

add_filter('webform_style_presets', function ($presets) {
    return array_merge($presets, array('orbit-paper' => __('Paper', 'formorbit'), 'orbit-night' => __('Night sky', 'formorbit'), 'orbit-terminal' => __('Terminal', 'formorbit'), 'orbit-editorial' => __('Editorial', 'formorbit'), 'orbit-ocean' => __('Ocean', 'formorbit'), 'orbit-brutalist' => __('Bold blocks', 'formorbit'), 'orbit-glass' => __('Glass', 'formorbit')));
});
add_action('webform_builder_style_controls', function ($settings) {
    if (!defined('WEBFORM_PRO_PLUGIN_VERSION')) {
        echo '<label>' . esc_html__('Button alignment', 'formorbit') . '<select id="webform-submit-alignment">';
        foreach (array('left' => __('Left', 'formorbit'), 'center' => __('Center', 'formorbit'), 'right' => __('Right', 'formorbit')) as $value => $label) echo '<option value="' . esc_attr($value) . '" ' . selected($settings['submit_alignment'] ?? 'center', $value, false) . '>' . esc_html($label) . '</option>';
        echo '</select></label><label>' . esc_html__('Custom CSS', 'formorbit') . '<textarea id="webform-custom-css" rows="6">' . esc_textarea($settings['custom_css'] ?? '') . '</textarea><small>' . esc_html__('Selectors are scoped to this form. External resources and at-rules are not supported.', 'formorbit') . '</small></label>';
    }
    echo '<label>' . esc_html__('Stage heading size (px)', 'formorbit') . '<input id="formorbit-stage-font-size" type="number" min="12" max="72" value="' . esc_attr($settings['stage_font_size'] ?? 24) . '"></label><label><input type="checkbox" id="formorbit-hide-stage-name" ' . checked(!empty($settings['hide_stage_name']), true, false) . '> ' . esc_html__('Hide stage names on public form', 'formorbit') . '</label>';
});
add_filter('webform_sanitize_form_settings', function ($clean, $settings) {
    $clean['stage_font_size'] = max(12, min(72, absint($settings['stage_font_size'] ?? 24)));
    $clean['hide_stage_name'] = !empty($settings['hide_stage_name']);
    $alignment = $settings['submit_alignment'] ?? 'center';
    $clean['submit_alignment'] = in_array($alignment, array('left', 'center', 'right', 'full'), true) ? $alignment : 'center';
    $clean['custom_css'] = substr(wp_strip_all_tags($settings['custom_css'] ?? ''), 0, 10000);
    return $clean;
}, 99, 2);
add_action('webform_before_form_markup', function ($form_id, $settings) {
    $scope = '#webform-' . absint($form_id);
    $align = array('left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end', 'full' => 'stretch');
    $css = $scope . ' .webform-actions{justify-content:' . ($align[$settings['submit_alignment'] ?? 'center'] ?? 'center') . ';}';
    $css .= $scope . ' .webform-stage>h2{font-size:' . max(12, min(72, absint($settings['stage_font_size'] ?? 24))) . 'px;}';
    if (!empty($settings['hide_stage_name'])) $css .= $scope . ' .webform-stage>h2,' . $scope . ' .webform-steps{display:none;}';
    if (!defined('WEBFORM_PRO_PLUGIN_VERSION')) {
        $source = preg_replace('#/\\*.*?\\*/#s', '', wp_strip_all_tags($settings['custom_css'] ?? ''));
        foreach (explode('}', $source) as $rule) {
            if (substr_count($rule, '{') !== 1 || strpos($rule, '@') !== false) continue;
            list($selectors, $declarations) = explode('{', $rule, 2);
            if (preg_match('/url\\s*\\(|expression|javascript|behavior|-moz-binding|[<>]/i', $rule)) continue;
            $scoped = array();
            foreach (explode(',', $selectors) as $selector) if (trim($selector)) $scoped[] = $scope . ' ' . trim($selector);
            if ($scoped) $css .= implode(',', $scoped) . '{' . $declarations . '}';
        }
    }
    echo '<style>' . str_replace(array('<', '>'), '', $css) . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Generated scoped CSS with bounded values and stripped HTML.
}, 99, 2);
