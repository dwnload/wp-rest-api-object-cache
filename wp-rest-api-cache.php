<?php
/**
 * Plugin Name: REST API Object Cache
 * Description: Enable object caching for WordPress' REST API. Aids in increased response times of your applications endpoints.
 * Author: Austin Passy
 * Author URI: http://github.com/thefrosty
 * Version: 0.2
 * Requires at least: 4.9
 * Tested up to: 4.9
 * Requires PHP: 7.0
 * Plugin URI: https://github.com/dwnload/wp-rest-api-object-cache
 */

defined( 'ABSPATH' ) || exit;

use TheFrosty\WP\Utils\Init;
use Dwnload\WpRestApi\WpRestApiCache;

add_action( 'init', function() {
    ( new WpRestApiCache( new Init() ) )->initiateWpHooks()->getInit()->initialize();
} );

call_user_func_array(
    function( $filter ) {
        add_filter( $filter, function( $value ) use ( $filter ) {
            if ( isset( $value ) && is_object( $value ) ) {
                unset( $value->response[ plugin_basename( __FILE__ ) ] );
            }

            return $value;
        } );
    },
    [ 'pre_site_transient_update_plugins', 'site_transient_update_plugins' ]
);
