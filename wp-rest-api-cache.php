<?php
/**
 * Plugin Name: REST API Object Cache
 * Description: Enable object caching for WordPress' REST API. Aids in increased response times of your applications endpoints.
 * Author: Austin Passy
 * Author URI: http://github.com/thefrosty
 * Version: 1.1.0
 * Requires at least: 4.9
 * Tested up to: 4.9
 * Requires PHP: 7.0
 * Plugin URI: https://github.com/dwnload/wp-rest-api-object-cache
 */

defined('ABSPATH') || exit;

use Dwnload\WpRestApi\RestApi\RestDispatch;
use Dwnload\WpRestApi\WpAdmin\Admin;
use TheFrosty\WpUtilities\Plugin\PluginFactory;

PluginFactory::create('rest-api-object-cache')
    ->addOnHook(RestDispatch::class)
	->addOnHook(Admin::class)
    ->initialize();

call_user_func_array(
    function ($filter) {
        add_filter($filter, function ($value) use ($filter) {
            if (! empty($value->response) && array_key_exists(plugin_basename(__FILE__), $value->response)) {
                unset($value->response[plugin_basename(__FILE__)]);
            }

            return $value;
        });
    },
    ['pre_site_transient_update_plugins', 'site_transient_update_plugins']
);
