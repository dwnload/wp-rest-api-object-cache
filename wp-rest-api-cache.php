<?php
/**
 * Plugin Name: WP REST API Cache
 * Description: Enable object caching for WordPress' REST API. Aids in increased response times of your applications endpoints.
 * Author: Austin Passy
 * Author URI: http://github.com/thefrosty
 * Version: 0.1
 * Requires at least: 4.9
 * Tested up to: 4.9
 * Requires PHP: 7.0
 * Plugin URI: https://github.com/thefrosty/wp-rest-api-cache
 */

defined( 'ABSPATH' ) || exit;

use TheFrosty\WP\Utils\Init;
use Dwnload\WpRestApi\WpRestApiCache;

add_action( 'init', function() {
    ( new WpRestApiCache( new Init() ) )->initiateWpHooksInterfaces()->getInit()->initialize();
} );

register_uninstall_hook( __FILE__, static function() {
    call_user_func( [ ( new WpRestApiCache() )->getRestDispatch(), 'wpCacheFlush' ] );
} );
