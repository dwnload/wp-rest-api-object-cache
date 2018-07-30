<?php declare(strict_types=1);

namespace Dwnload\WpRestApi\RestApi;

use WP_REST_Request;
use WP_REST_Server;

/**
 * Trait CacheApi
 * @package Dwnload\WpRestApi\RestApi
 */
trait CacheApiTrait
{

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
     * @return string
     */
    protected function getCacheKey(
        string $request_uri,
        WP_REST_Server $server = null,
        WP_REST_Request $request = null,
        string $url = null
    ) : string {
        if (! ($server instanceof WP_REST_Server)) {
            $server = \rest_get_server();
        }

        if (! ($request instanceof WP_REST_Request)) {
            if (\is_string($url)) {
                $request = WP_REST_Request::from_url($url);
            } else {
                $request = new WP_REST_Request();
            }
        }

        return $this->sanitize(\apply_filters(RestDispatch::FILTER_API_KEY, $request_uri, $server, $request));
    }

    /**
     * Get the cache group value.
     *
     * @return string
     */
    protected function getCacheGroup() : string
    {
        return $this->sanitize(\apply_filters(RestDispatch::FILTER_API_GROUP, RestDispatch::CACHE_GROUP));
    }

    /**
     * Empty all cache.
     *
     * @uses wp_cache_flush()
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    protected function wpCacheFlush() : bool
    {
        return \wp_cache_flush();
    }

    /**
     * Empty all cache.
     *
     * @uses wp_cache_replace()
     * @param string $key The key under which the value is stored.
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    protected function wpCacheReplace(string $key) : bool
    {
        return \wp_cache_replace($this->cleanKey($key), false, $this->getCacheGroup(), -1);
    }

    /**
     * Empty all cache.
     *
     * @uses wp_cache_delete()
     * @param string $key The key under which the value is stored.
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    protected function wpCacheDeleteByKey(string $key) : bool
    {
        return \wp_cache_delete($this->cleanKey($key), $this->getCacheGroup());
    }

    /**
     * Clean the cache of query params that shouldn't be part of the string; like delete params.
     *
     * @param string $key
     * @return string
     */
    protected function cleanKey(string $key) : string
    {
        $query_args = [
            RestDispatch::QUERY_CACHE_DELETE,
            RestDispatch::QUERY_CACHE_FORCE_DELETE,
            RestDispatch::QUERY_CACHE_REFRESH,
        ];
        return \remove_query_arg($query_args, $key);
    }

    /**
     * Return the current REQUEST_URI from the global server variable.
     * Don't use `FILTER_SANITIZE_URL` since it will return false when 'http' isn't present.
     *
     * @return string
     */
    protected function getRequestUri() : string
    {
        return $this->sanitize(\wp_unslash($_SERVER['REQUEST_URI']));
    }

    /**
     * Sanitize incoming variables as a string value.
     * @param mixed $variable
     * @return string|false
     */
    private function sanitize($variable)
    {
        return \filter_var($variable, FILTER_SANITIZE_STRING);
    }
}
