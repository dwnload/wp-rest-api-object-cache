# WordPress REST API Object Cache

[![PHP from Packagist](https://img.shields.io/packagist/php-v/dwnload/wp-rest-api-object-cache.svg)]()
[![Latest Stable Version](https://img.shields.io/packagist/v/dwnload/wp-rest-api-object-cache.svg)](https://packagist.org/packages/dwnload/wp-rest-api-object-cache)
[![Total Downloads](https://img.shields.io/packagist/dt/dwnload/wp-rest-api-object-cache.svg)](https://packagist.org/packages/dwnload/wp-rest-api-object-cache)
[![License](https://img.shields.io/packagist/l/dwnload/wp-rest-api-object-cache.svg)](https://packagist.org/packages/dwnload/wp-rest-api-object-cache)
[![Build Status](https://travis-ci.org/dwnload/wp-rest-api-object-cache.svg?branch=master)](https://travis-ci.org/dwnload/wp-rest-api-object-cache)

Enable object caching for WordPress' REST API. Aids in increased response times of your applications endpoints.

## Package Installation (via Composer)

To install this package, edit your `composer.json` file:

```js
{
    "require": {
        "dwnload/wp-rest-api-object-cache": "^1.2.0"
    }
}
```

Now run:

`$ composer install dwnload/wp-rest-api-object-cache`

-----

- [Actions](#actions)
- [How to use actions](#how-to-use-actions)
- [Filters](#filters)
- [How to use filters](#how-to-use-filters)


Actions
====
| Action    | Argument(s) |
|-----------|-----------|
| Dwnload\WpRestApi\RestApi\RestDispatch::ACTION_CACHE_SKIPPED | mixed **$result**<br>WP_REST_Server **$server**<br>WP_REST_Request **$request** |
| Dwnload\WpRestApi\WpAdmin\Admin::ACTION_REQUEST_FLUSH_CACHE | string **$message**<br>string **$type**<br>WP_User **$user** |

How to use actions
----

```php
use Dwnload\WpRestApi\RestApi\RestDispatch;
add_action( RestDispatch::ACTION_CACHE_SKIPPED, function( $result, \WP_REST_Server $server, \WP_REST_Request $request ) {
	// Do something here, like create a log entry using Wonolog.
}, 10, 3 );
```

```php
use Dwnload\WpRestApi\WpAdmin\Admin;
add_action( Admin::ACTION_REQUEST_FLUSH_CACHE, function( $message, $type, WP_User $user ) {
	// Do something here, like create a log entry using Wonolog.
}, 10, 3 );
```

Filters
====
| Filter    | Argument(s) |
|-----------|-----------|
| Dwnload\WpRestApi\RestApi\RestDispatch::FILTER_CACHE_HEADERS | array **$headers**<br>string **$request_uri**<br>WP_REST_Server **$server**<br>WP_REST_Request **$request**<br>WP_REST_Response **$response (`rest_pre_dispatch` only)** |
| Dwnload\WpRestApi\RestApi\RestDispatch::FILTER_CACHE_SKIP | boolean **$skip** ( default: WP_DEBUG )<br>string **$request_uri**<br>WP_REST_Server **$server**<br>WP_REST_Request **$request** |
| Dwnload\WpRestApi\RestApi\RestDispatch::FILTER_API_KEY | string **$request_uri**<br>WP_REST_Server **$server**<br>WP_REST_Request **$request** |
| Dwnload\WpRestApi\RestApi\RestDispatch::FILTER_API_GROUP | string **$cache_group** |
| Dwnload\WpRestApi\RestApi\RestDispatch::FILTER_CACHE_EXPIRE | int **$expires** |
| Dwnload\WpRestApi\WpAdmin\Admin::FILTER_CACHE_UPDATE_OPTIONS | array **$options** |
| Dwnload\WpRestApi\WpAdmin\Admin::FILTER_CACHE_OPTIONS | array **$options** |
| Dwnload\WpRestApi\WpAdmin\Admin::FILTER_SHOW_ADMIN | boolean **$show** |
| Dwnload\WpRestApi\WpAdmin\Admin::FILTER_SHOW_ADMIN_MENU | boolean **$show** |
| Dwnload\WpRestApi\WpAdmin\Admin::FILTER_SHOW_ADMIN_BAR_MENU | boolean **$show** |
| Dwnload\WpRestApi\RestApi\RestDispatch::FILTER_ALLOWED_CACHE_STATUS | array **$status** HTTP Header statuses (defaults to `array( 200 )` |
| Dwnload\WpRestApi\RestApi\RestDispatch::FILTER_CACHE_VALIDATE_AUTH | boolean **$authenticated**<br>WP_REST_Request $request |

How to use filters
----
**Sending headers.**

```php
use Dwnload\WpRestApi\RestApi\RestDispatch;
add_filter( RestDispatch::FILTER_CACHE_HEADERS, function( array $headers ) : array {
	$headers['Cache-Control'] = 'public, max-age=3600';
	
	return $headers;
} );
```

**Changing the cache expire time.**

```php
use Dwnload\WpRestApi\RestApi\RestDispatch;
add_filter( RestDispatch::FILTER_CACHE_EXPIRE, function() : int {
	// https://codex.wordpress.org/Transients_API#Using_Time_Constants
	return ( HOUR_IN_SECONDS * 5 );
} );
```

```php
use Dwnload\WpRestApi\WpAdmin\Admin;
add_filter( Admin::FILTER_CACHE_OPTIONS, function( array $options ) : array {
	if ( ! isset( $options['timeout'] ) ) {
		$options['timeout'] = array();
	}

	// https://codex.wordpress.org/Transients_API#Using_Time_Constants
	$options['timeout']['length'] = 15;
	$options['timeout']['period'] = DAY_IN_SECONDS;
	
	return $options;
} );
```

**Validating user auth when `?context=edit`**

```php
use Dwnload\WpRestApi\RestApi\RestDispatch;
add_filter( RestDispatch::FILTER_CACHE_VALIDATE_AUTH, function( bool $auth, WP_REST_Request $request ) : bool {
	// If you are running the Basic Auth plugin.
	if ( $GLOBALS['wp_json_basic_auth_error'] === true ) {
        $authorized = true;
    }
    // Otherwise, maybe do some additional logic on the request for current user...

    return $authorized;
}, 10, 2 );
```

**Skipping cache**

```php
use Dwnload\WpRestApi\RestApi\RestDispatch;
add_filter( RestDispatch::FILTER_CACHE_SKIP, function( bool $skip, string $request_uri ) : bool {
	if ( ! $skip && stripos( 'wp-json/dwnload/v2', $request_uri ) !== false ) {
		return true;
	}

	return $skip;
}, 10, 2 );
```

**Deleting cache**

*Soft delete:*
Append `RestDispatch::QUERY_CACHE_DELETE` to your query param: `add_query_arg( [ RestDispatch::QUERY_CACHE_DELETE, '1' ], '<url>' )`.  
_soft delete will delete the cache after the current request completes (on WordPress shutdown)._ 

*Hard delete:* Append `RestDispatch::QUERY_CACHE_DELETE` && `RestDispatch::QUERY_CACHE_FORCE_DELETE` to your query param:
`add_query_arg( [ RestDispatch::QUERY_CACHE_DELETE, '1', RestDispatch::QUERY_CACHE_FORCE_DELETE, '1' ], '<url>' )`.  
_hard delete will delete the cache before the request, forcing it to repopulate._


**empty ALL cache on post-save** _this is not ideal_

You can use the WordPress filter `save_post` if you would like to empty **ALL** cache on post save.

```php
use Dwnload\WpRestApi\RestApi\RestDispatch;
add_action( 'save_post', function( $post_id ) {
  if ( class_exists( RestDispatch::class ) ) {
    call_user_func( [ ( WpRestApiCache::getRestDispatch(), 'wpCacheFlush' ] );
  }
} );
```

**Maybe better to use `transition_post_status`**

```php
add_action( 'transition_post_status', function(  string $new_status, string $old_status, \WP_Post $post ) {
  if ( 'publish' === $new_status || 'publish' === $old_status ) {
    \wp_cache_flush();
  }
}, 99, 3 );
```
