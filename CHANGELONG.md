# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## 1.3.0 - 2018-07-27
### Updated
- Removed the `helper.php` file.
- Updated all the functions that were using the helper functions.
- Update [thefrosty/wp-utilities](https://github.com/thefrosty/wp-utilities) to 1.2.2.
- Fix save settings on admin page, (POST array key was incorrect).
- Add confirm to clear all cache button on settings page.
- Only load the Admin class in the admin.

### Changed
- Added a new capability (`manage_wp_rest_api_cache`) to view the settings page and/or admin bar which
is (mapped to `delete_users`).
- The `Dwnload\WpRestApi\RestApi\RestDispatch::FILTER_CACHE_EXPIRE` filters expire sanitize function was changed from
`absint` to `inval` function to allow for zero and negative numbers.
- Pass `is_admin_bar_showing()` into FILTER_SHOW_ADMIN_BAR_MENU.

### Added
- Added `wpCacheReplace()` to the `CacheApiTrait`.

## 1.2.3 - 2018-05-30
### Updated
- Added permission check (`delete_users`) before adding admin bar node.
- Change permission check on settings page from `manage_options` to `delete_users`.
- Removed nonce check after successful cache flush for admin notice.

### Added
- PHP 7.2 to the Travis build.

## 1.2.2 - 2018-04-30
### Fixed
- When endpoints have multiple posts, the request bubbles up and appends the results which leads to a body size X's the
requests. In other words, it's bad. This adds static property cache to break out of the possible loop.

## 1.2.1 - 2018-04-30
### Updated
- Fixes PHP Warning: call_user_func_array() expects parameter 1 to be a valid callback , cannot access protected method Dwnload\WpRestApi\WpAdmin\Admin::renderPage(). 

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