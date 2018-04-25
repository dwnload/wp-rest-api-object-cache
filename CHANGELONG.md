# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## 1.2.0 - 2018-04-25
### Added
- Added new method `RestDispatch::queryParamContextIsEdit`
- Added new method `RestDispatch::isUserAuthenticated`.

### Updated
- `RestDispatch::isUserAuthenticated` uses a new filter `RestDispatch::FILTER_CACHE_VALIDATE_AUTH` to re-check requests containing `?context=edit` to avoid race conditions where a non-auth request returns results from cache.

## 1.1.1 - 2018-04-23
### Updated
- Version bump for packagist.

## 1.1.0 - 2018-04-23
### Updated
- The admin settings page now works.
- wp_cache_set expire from settings is used (if available).
- Be sure to clean the URL queries after calls to avoid caching delete requests.
- Make sure the cache flush button in the settings is invoked to show the URL.

## 1.0.4 - 2018-04-19
### Updated
- `RestDispatch::preDispatch` should set the $request_uri from `CacheApiTrait::getRequestUri` and not use
`WP_REST_Request::get_route` to avoid query parameters getting stripped out of the cache request.
- `CacheApiTrait::getRequestUri` to sanitize the REQUEST_URI

## 1.0.3 - 2018-04-18
### Updated
- Bumped [thefrosty/wp-utilities](https://github.com/thefrosty/wp-utilities/) to version 1.1.3

## 1.0.2 - 2018-04-18
### Updated
- Bumped [thefrosty/wp-utilities](https://github.com/thefrosty/wp-utilities/) to version 1.1.2
which fixes `addOnHook` not executing when omitting a priority parameter less than 10.

## 1.0.1 - 2018-04-18
### Fixed
- `addOnHook` expects a string value, not an object.

## 1.0.0 - 2018-04-09
### Updated
- Bumped [thefrosty/wp-utilities](https://github.com/thefrosty/wp-utilities/) to version 1.1.
- Updated the plugin to use the new PluginFactory.

## 0.2.1 - 2018-02-15
### Updated
- Update conditional checks on `*_update_plugins` filters.

## 0.2 - 2018-02-14
### Updated
- Global functions outside of namespace are now prefixed with a backslash.
- Update README with new GitHub location URL.

## 0.1 - 2018-02-02
### Added
- Forked [thefrosty/wp-rest-api-cache](https://github.com/thefrosty/wp-rest-api-cache/) which is a fork of 
[airesvsg/wp-rest-api-cache](https://github.com/airesvsg/wp-rest-api-cache/).
- This CHANGELOG file.