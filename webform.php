<?php
/**
 * Legacy FormOrbit loader.
 *
 * Keeps sites activated when upgrading from the historical webform.php entry
 * file, then migrates WordPress to the branded formorbit.php entry file.
 */

defined('ABSPATH') || exit;

$formorbit_legacy_plugin = plugin_basename(__FILE__);
$formorbit_branded_plugin = dirname($formorbit_legacy_plugin) . '/formorbit.php';

$formorbit_active_plugins = (array) get_option('active_plugins', array());
$formorbit_active_index = array_search($formorbit_legacy_plugin, $formorbit_active_plugins, true);
if ($formorbit_active_index !== false) {
    $formorbit_active_plugins[$formorbit_active_index] = $formorbit_branded_plugin;
    update_option('active_plugins', array_values(array_unique($formorbit_active_plugins)));
}

if (is_multisite()) {
    $formorbit_network_plugins = (array) get_site_option('active_sitewide_plugins', array());
    if (isset($formorbit_network_plugins[$formorbit_legacy_plugin])) {
        $formorbit_activated_at = $formorbit_network_plugins[$formorbit_legacy_plugin];
        unset($formorbit_network_plugins[$formorbit_legacy_plugin]);
        $formorbit_network_plugins[$formorbit_branded_plugin] = $formorbit_activated_at;
        update_site_option('active_sitewide_plugins', $formorbit_network_plugins);
    }
}

require_once __DIR__ . '/formorbit.php';
