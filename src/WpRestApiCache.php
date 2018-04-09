<?php declare(strict_types=1);

namespace Dwnload\WpRestApi;

use Dwnload\WpRestApi\RestApi\RestDispatch;

/**
 * Class WpRestApiCache
 * A Factory.
 * @package Dwnload\WpRestApi
 */
final class WpRestApiCache
{

    const FILTER_PREFIX = 'wp_rest_api_cache/';
    const ID = 'wp-rest-api-cache';

    /**
     * Gets an instance of the RestDispatch object.
     *
     * @return RestDispatch
     */
    public static function getRestDispatch() : RestDispatch
    {
        static $dispatch;

        if ($dispatch === null || ! ($dispatch instanceof RestDispatch)) {
            $dispatch = new RestDispatch();
        }

        return $dispatch;
    }
}
