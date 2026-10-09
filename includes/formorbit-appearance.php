<?php
/**
 * Shared appearance controls for FormOrbit.
 *
 * @package FormOrbit
 */
if (!defined('ABSPATH')) exit;
add_action('wp_enqueue_scripts', function () { wp_enqueue_style('formorbit-free-presets', WEBFORM_URL . 'assets/css/free-presets.css', array(), WEBFORM_VERSION); });
add_action('admin_enqueue_scripts', function () {
    if (wp_script_is('webform-admin', 'enqueued')) wp_enqueue_style('formorbit-appearance', WEBFORM_URL . 'assets/css/appearance.css', array('webform-admin', 'formorbit-advanced-admin'), WEBFORM_VERSION);
    if (wp_script_is('webform-admin', 'enqueued')) wp_enqueue_style('formorbit-free-presets', WEBFORM_URL . 'assets/css/free-presets.css', array('formorbit-appearance'), WEBFORM_VERSION);
}, 20);

add_filter('webform_style_presets', function ($presets) {
    return array_merge($presets, array('orbit-paper' => __('Paper', 'formorbit'), 'orbit-night' => __('Night sky', 'formorbit'), 'orbit-terminal' => __('Terminal', 'formorbit'), 'orbit-editorial' => __('Editorial', 'formorbit'), 'orbit-ocean' => __('Ocean', 'formorbit'), 'orbit-brutalist' => __('Bold blocks', 'formorbit'), 'orbit-glass' => __('Glass', 'formorbit')));
});
add_action('webform_builder_style_controls', function ($settings) {
    echo '<label>' . esc_html__('Stage heading size (px)', 'formorbit') . '<input id="formorbit-stage-font-size" type="number" min="12" max="72" value="' . esc_attr($settings['stage_font_size'] ?? 24) . '"></label><label><input type="checkbox" id="formorbit-hide-stage-name" ' . checked(!empty($settings['hide_stage_name']), true, false) . '> ' . esc_html__('Hide stage names on public form', 'formorbit') . '</label>';
});
add_filter('webform_sanitize_form_settings', function ($clean, $settings) {
    $clean['stage_font_size'] = max(12, min(72, absint($settings['stage_font_size'] ?? 24)));
    $clean['hide_stage_name'] = !empty($settings['hide_stage_name']);
    $alignment = $settings['submit_alignment'] ?? 'center';
    $clean['submit_alignment'] = in_array($alignment, array('left', 'center', 'right', 'full'), true) ? $alignment : 'center';
    return $clean;
}, 99, 2);
add_action('webform_before_form_markup', function ($form_id, $settings) {
    $scope = '#webform-' . absint($form_id);
    $align = array('left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end', 'full' => 'stretch');
    $css = $scope . ' .webform-actions{justify-content:' . ($align[$settings['submit_alignment'] ?? 'center'] ?? 'center') . ';}';
    $css .= $scope . '{padding:var(--wf-form-padding,24px);border-radius:var(--wf-form-radius,12px);line-height:var(--wf-line-height,1.5);font-size:var(--wf-font-size,16px);font-family:var(--wf-font,inherit);}';
    $css .= $scope . ' .webform-field>label,' . $scope . ' .webform-field>legend{font-size:var(--wf-label-size,14px);font-weight:var(--wf-label-weight,600);}';
    $css .= $scope . ' .webform-heading{font-size:var(--wf-heading-size,26px);}';
    $css .= $scope . ' input:not([type=checkbox]):not([type=radio]):not([type=range]):not([type=hidden]),' . $scope . ' textarea,' . $scope . ' select{padding:var(--wf-control-padding,12px);font-size:var(--wf-font-size,16px);line-height:var(--wf-line-height,1.5);font-family:inherit;box-sizing:border-box;max-width:100%;}';
    $css .= '@media(max-width:600px){' . $scope . '{padding:min(var(--wf-form-padding,24px),16px);}' . $scope . ' .webform-stage>.webform-field{min-width:0!important;flex-basis:100%!important;width:100%!important;max-width:100%!important;}}';
    $css .= $scope . ' .webform-stage>h2{font-size:' . max(12, min(72, absint($settings['stage_font_size'] ?? 24))) . 'px;}';
    if (!empty($settings['hide_stage_name'])) $css .= $scope . ' .webform-stage>h2,' . $scope . ' .webform-steps{display:none;}';
    echo '<style>' . str_replace(array('<', '>'), '', $css) . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Generated scoped CSS with bounded values and stripped HTML.
}, 99, 2);
