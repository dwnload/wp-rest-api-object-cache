<?php
/**
 * Plugin Name: REST API Object Cache
 * Description: Enable object caching for WordPress' REST API. Aids in increased response times of your applications endpoints.
 * Author: Austin Passy
 * Author URI: http://github.com/thefrosty
 * Version: 1.3.1
 * Requires at least: 4.9
 * Tested up to: 4.9
 * Requires PHP: 7.0
 * Plugin URI: https://github.com/dwnload/wp-rest-api-object-cache
 */

defined('ABSPATH') || exit;

use Dwnload\WpRestApi\RestApi\RestDispatch;
use Dwnload\WpRestApi\WpAdmin\Admin;
use TheFrosty\WpUtilities\Plugin\PluginFactory;

$plugin = PluginFactory::create('rest-api-object-cache');
$plugin->addOnHook(RestDispatch::class, 'rest_api_init')->initialize();
add_action('after_setup_theme', function () use ($plugin) {
    if (is_admin()) {
        $plugin->add(new Admin())->initialize();
    }
});

add_filter('site_transient_update_plugins', function ($value) {
    if (isset($value) && is_object($value) && (! empty($value->response) && is_array($value->response))) {
        unset($value->response[@plugin_basename(__FILE__)]);
    }

    return $value;
});
