<?php

namespace Dwnload\WpRestApi;

use Dwnload\WpRestApi\RestApi\RestDispatch;
use TheFrosty\WP\Utils\Init;
use TheFrosty\WP\Utils\WpHooksInterface;

/**
 * Class WpRestApiCache
 * A Factory.
 * @package Dwnload\WpRestApi
 */
final class WpRestApiCache {

    const FILTER_PREFIX = 'wp_rest_api_cache/';
    const ID = 'wp-rest-api-cache';
    const WP_HOOKS = [
        RestDispatch::class,
    ];

    /**
     * Init object instance.
     * @var Init
     */
    private $init;

    /**
     * WpRestApiCache constructor.
     * @param Init $init
     */
    public function __construct( Init $init = null ) {
        $this->init = $init;
    }

    /**
     * Gets an instance of the RestDispatch object.
     *
     * @return RestDispatch
     */
    public function getRestDispatch() : RestDispatch {
        static $dispatch;

        if ( $dispatch === null || ! ( $dispatch instanceof RestDispatch ) ) {
            $dispatch = new RestDispatch();
        }

        return $dispatch;
    }

    /**
     * Get one of the `WpHooksInterface` objects by class name.
     *
     * @param string $class Fully qualified class name.
     * @return WpHooksInterface
     */
    public function getWpHook( string $class ) : WpHooksInterface {
        $key = array_search( $class, array_column( $this->init->plugin_components, null ) );
        return $this->init->plugin_components[ $key ];
    }

    /**
     * Initiate the classes instances and hooks.
     *
     * @return WpRestApiCache
     */
    public function initiateWpHooks() : WpRestApiCache {
        $wp_hooks = self::WP_HOOKS;

        array_walk( $wp_hooks, function( $class ) {
            $this->init->add( new $class() );
        } );

        return $this;
    }

    /**
     * Gets an instance of the Init object.
     *
     * @return Init
     */
    public function getInit() : Init {
        return $this->init;
    }
}
