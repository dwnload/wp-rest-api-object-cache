<?php declare( strict_types=1 );

namespace Dwnload\WpRestApi\RestApi;

use Dwnload\WpRestApi\WpRestApiCache;
use TheFrosty\WP\Utils\WpHooksInterface;
use WP_Error;
use WP_Http;
use WP_HTTP_Response;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class RestDispatch
 * @package Dwnload\WpRestApi\RestApi
 */
class RestDispatch implements WpHooksInterface {

    use CacheApi;

    const ACTION_CACHE_SKIPPED = WpRestApiCache::FILTER_PREFIX . 'skipped';
    const CACHE_GROUP = 'rest_api';
    const CACHE_HEADER = 'X-WP-API-Cache';
    const CACHE_HEADER_DELETE = 'X-WP-API-Cache-Delete';
    const FILTER_API_GROUP = WpRestApiCache::FILTER_PREFIX . 'group';
    const FILTER_API_KEY = WpRestApiCache::FILTER_PREFIX . 'key';
    const FILTER_ALLOWED_CACHE_STATUS = WpRestApiCache::FILTER_PREFIX . 'allowed_cache_status';
    const FILTER_CACHE_CONTROL_HEADERS = WpRestApiCache::FILTER_PREFIX . 'cache_control_headers';
    const FILTER_CACHE_EXPIRE = WpRestApiCache::FILTER_PREFIX . 'expire';
    const FILTER_CACHE_HEADERS = WpRestApiCache::FILTER_PREFIX . 'headers';
    const FILTER_CACHE_SKIP = WpRestApiCache::FILTER_PREFIX . 'skip';
    const HEADER_CACHE_CONTROL = 'Cache-Control';
    const QUERY_CACHE_DELETE = 'rest_cache_delete';
    const QUERY_CACHE_FORCE_DELETE = 'rest_force_delete';
    const QUERY_CACHE_REFRESH = 'rest_cache_refresh';

    const VERSION = '2.0.4';

    /**
     * Add class hooks.
     */
    public function addHooks() {
        add_filter( 'rest_pre_dispatch', [ $this, 'preDispatch' ], 10, 3 );
        add_filter( 'rest_post_dispatch', [ $this, 'postDispatch' ], 10, 3 );
    }

    /**
     * Filters the pre-calculated result of a REST dispatch request.
     *
     * @param mixed $result Response to replace the requested version with. Can be anything
     *                                 a normal endpoint can return, or null to not hijack the request.
     * @param WP_REST_Server $server Server instance.
     * @param WP_REST_Request $request Request used to generate the response.
     *
     * @return mixed Response
     */
    public function preDispatch( $result, WP_REST_Server $server, WP_REST_Request $request ) {
        $request_uri = $request->get_route() ?? $this->getRequestUri();
        $group = $this->getCacheGroup();
        $key = $this->getCacheKey( $request_uri, $server, $request );

        // Don't cache non-readable (GET) methods.
        if ( $request->get_method() !== WP_REST_Server::READABLE ) {
            return $result;
        }

        $this->sendHeaders( $request_uri, $server, $request );

        // Delete the cache.
        if ( $this->validateQueryParam( $request, self::QUERY_CACHE_DELETE ) ) {
            // Force delete
            if ( $this->validateQueryParam( $request, self::QUERY_CACHE_FORCE_DELETE ) ) {
                if ( $this->wpCacheDeleteByKey( $key ) ) {
                    $server->send_header( self::CACHE_HEADER_DELETE, 'true' );

                    return $this->getCachedResult( $server, $request, $key, $group, true );
                }
            } else {
                $server->send_header( self::CACHE_HEADER_DELETE, 'soft' );
                $this->dispatchShutdownAction( $key );

                return $this->getCachedResult( $server, $request, $key, $group );
            }
        }

        // Cache is refreshed (cached below).
        $refresh = $this->filter_var(
            $request->get_param( self::QUERY_CACHE_REFRESH ),
            FILTER_VALIDATE_BOOLEAN
        );
        if ( $refresh ) {
            $server->send_header(
                self::CACHE_HEADER,
                esc_attr_x(
                    'refreshed',
                    'When the wp-api cache is skipped. This is the header value.',
                    'wp-rest-api-cache'
                )
            );

            return $result;
        } else {
            $server->send_header(
                self::CACHE_HEADER,
                esc_attr_x(
                    'cached',
                    'When rest_cache is cached. This is the header value.',
                    'wp-rest-api-cache'
                )
            );
        }

        $skip = $this->filter_var(
            apply_filters( self::FILTER_CACHE_SKIP, WP_DEBUG, $request_uri, $server, $request ),
            FILTER_VALIDATE_BOOLEAN
        );
        if ( $skip ) {
            $server->send_header(
                self::CACHE_HEADER,
                esc_attr_x(
                    'skipped',
                    'When rest_cache is skipped. This is the header value.',
                    'wp-rest-api-cache'
                )
            );
            /**
             * Action hook when the cache is skipped.
             *
             * @param mixed $result Response to replace the requested version with. Can be anything
             *                                 a normal endpoint can return, or null to not hijack the request.
             * @param WP_REST_Server $server Server instance.
             * @param WP_REST_Request $request Request used to generate the response.
             */
            do_action( self::ACTION_CACHE_SKIPPED, $result, $server, $request );

            return $result;
        }

        return $this->getCachedResult( $server, $request, $key, $group );
    }

    /**
     * Filters the post-calculated result of a REST dispatch request.
     *
     * @param WP_Error|WP_HTTP_Response|WP_REST_Response $response
     * @param WP_REST_Server $server
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function postDispatch( $response, WP_REST_Server $server, WP_REST_Request $request ) : WP_REST_Response {
        $request_uri = $this->getRequestUri();
        $key = $this->getCacheKey( $request_uri, $server, $request );

        // Don't cache WP_Error objects.
        if ( $response instanceof WP_Error ) {
            $this->wpCacheDeleteByKey( $key );

            return rest_ensure_response( $response );
        }

        $allowed_cache_status = apply_filters( self::FILTER_ALLOWED_CACHE_STATUS, [ WP_Http::OK ] );
        if ( ! in_array( $response->get_status(), $allowed_cache_status, true ) ) {
            $server->send_header(
                self::CACHE_HEADER,
                esc_attr_x(
                    'incorrect-status',
                    'When rest_cache is skipped. This is the header value.',
                    'wp-rest-api-cache'
                )
            );
            $this->wpCacheDeleteByKey( $key );
        }

        return rest_ensure_response( $response );
    }

    /**
     * Get the result from cache.
     *
     * @param WP_REST_Server $server
     * @param WP_REST_Request $request
     * @param string $key
     * @param string $group
     * @param bool $force
     *
     * @return bool|mixed|WP_REST_Response
     */
    protected function getCachedResult(
        WP_REST_Server $server,
        WP_REST_Request $request,
        string $key,
        string $group,
        bool $force = false
    ) {
        $result = wp_cache_get( $key, $group, $force );
        if ( $result === false ) {
            $result = $this->dispatchRequest( $server, $request );
            $expire = absint( apply_filters( self::FILTER_CACHE_EXPIRE, ( MINUTE_IN_SECONDS * 10 ) ) );
            wp_cache_set( $key, $result, $group, $expire );

            return $result;
        }

        return $result;
    }

    /**
     * Send server headers if we have headers to send.
     *
     * @param string $request_uri
     * @param WP_REST_Server $server
     * @param WP_REST_Request $request
     * @param null|WP_REST_Response $response
     */
    private function sendHeaders(
        string $request_uri,
        WP_REST_Server $server,
        WP_REST_Request $request,
        WP_REST_Response $response = null
    ) {
        $headers = apply_filters(
            self::FILTER_CACHE_HEADERS,
            [],
            $request_uri,
            $server,
            $request,
            $response
        );
        if ( ! empty( $headers ) ) {
            $server->send_headers( $headers );
        }
    }

    /**
     * Dispatch the REST request.
     *
     * @param WP_REST_Server $server
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    private function dispatchRequest( WP_REST_Server $server, WP_REST_Request $request ) : WP_REST_Response {
        $request->set_param( self::QUERY_CACHE_REFRESH, true );

        // Don't filter the request of the dispatch, since we're in the filter right now.
        remove_filter( 'rest_pre_dispatch', [ $this, 'preDispatch' ], 10 );
        $results = $server->dispatch( $request );
        add_filter( 'rest_pre_dispatch', [ $this, 'preDispatch' ], 10, 3 );

        return $results;
    }

    /**
     * Dispatch a function hooked to WordPress' `shutdown` action to clear the cache by key if it exists.
     *
     * @param string $key
     */
    private function dispatchShutdownAction( string $key ) {
        add_action( 'shutdown', function() use ( $key ) {
            call_user_func( [ $this, 'wpCacheDeleteByKey' ], $key );
        } );
    }

    /**
     * Validate
     * @param WP_REST_Request $request
     * @param string $key
     * @return bool
     */
    private function validateQueryParam( WP_REST_Request $request, string $key ) : bool {
        return array_key_exists( $key, $request->get_query_params() ) &&
            $this->filter_var( $request->get_query_params()[ $key ], FILTER_VALIDATE_INT ) === 1;
    }
}
