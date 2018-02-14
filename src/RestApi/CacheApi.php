<?php declare( strict_types=1 );

namespace Dwnload\WpRestApi\RestApi;

use WP_REST_Request;
use WP_REST_Server;

/**
 * Trait CacheApi
 * @package Dwnload\WpRestApi\RestApi
 */
trait CacheApi {

    /**
     * Get the cache key value.
     *
     * It uses the URL, query parameters, and certain headers as the key.
     * The headers to use in the key are specified with the Vary header; for instance, we include the user’s
     * locale header in the cache key to make sure cached results have the correct language and currency.
     *
     * @param string $request_uri The REQUEST_URI
     * @param WP_REST_Server|null $server An instance of WP_REST_Server
     * @param WP_REST_Request|null $request An instance of WP_REST_Request
     * @param string|null $url Full URL to pass to WP_REST_Request
     *
     * @return string
     */
    protected function getCacheKey(
        string $request_uri,
        WP_REST_Server $server = null,
        WP_REST_Request $request = null,
        string $url = null
    ) : string {
        static $key;

        if ( \is_string( $key ) ) {
            return $key;
        }

        if ( ! ( $server instanceof WP_REST_Server ) ) {
            $server = \rest_get_server();
        }

        if ( ! ( $request instanceof WP_REST_Request ) ) {
            if ( \is_string( $url ) ) {
                $request = WP_REST_Request::from_url( $url );
            } else {
                $request = new WP_REST_Request();
            }
        }

        // Be sure to remove our added cache refresh & cache delete queries.
        $uri = \remove_query_arg( [ RestDispatch::QUERY_CACHE_DELETE, RestDispatch::QUERY_CACHE_REFRESH ], $request_uri );

        $key = \filter_var(
            \apply_filters(
                RestDispatch::FILTER_API_KEY,
                $uri,
                $server,
                $request
            ),
            FILTER_SANITIZE_STRING
        );

        return $key;
    }

    /**
     * Get the cache group value.
     *
     * @return string
     */
    protected function getCacheGroup() : string {
        return \filter_var(
            \apply_filters(
                RestDispatch::FILTER_API_GROUP,
                RestDispatch::CACHE_GROUP
            ),
            FILTER_SANITIZE_STRING
        );
    }

    /**
     * Empty all cache.
     *
     * @uses wp_cache_flush()
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    protected function wpCacheFlush() : bool {
        return \wp_cache_flush();
    }

    /**
     * Empty all cache.
     *
     * @uses wp_cache_delete()
     *
     * @param string $key The key under which to store the value.
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    protected function wpCacheDeleteByKey( string $key ) : bool {
        return \wp_cache_delete( $key, $this->getCacheGroup() );
    }

    /**
     * Return the current REQUEST_URI from the global server variable.
     * Don't use `FILTER_SANITIZE_URL` since it will return false when 'http' isn't present.
     *
     * @param string|null $route The request route.
     * @return string
     */
    protected function getRequestUri( string $route = null ) : string {
        return \filter_var( $route ?? $_SERVER['REQUEST_URI'], FILTER_SANITIZE_STRING );
    }
}
