<?php declare(strict_types=1);

namespace Dwnload\WpRestApi\WpAdmin;

use Dwnload\WpRestApi\RestApi\CacheApiTrait;
use Dwnload\WpRestApi\RestApi\RestDispatch;
use Dwnload\WpRestApi\WpRestApiCache;
use TheFrosty\WpUtilities\Plugin\HooksTrait;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;
use WP_Admin_Bar;

/**
 * Class Admin
 * @package Dwnload\WpRestApi\WpAdmin
 */
class Admin implements WpHooksInterface
{

    use CacheApiTrait, HooksTrait;

    const ACTION_REQUEST_FLUSH_CACHE = WpRestApiCache::FILTER_PREFIX . 'request_flush_cache';
    const ADMIN_ACTION = WpRestApiCache::FILTER_PREFIX . 'flush';
    const CAPABILITY = 'manage_wp_rest_api_cache';
    const FILTER_SHOW_ADMIN = WpRestApiCache::FILTER_PREFIX . 'show_admin';
    const FILTER_SHOW_ADMIN_BAR_MENU = WpRestApiCache::FILTER_PREFIX . 'show_admin_bar_menu';
    const FILTER_SHOW_ADMIN_MENU = WpRestApiCache::FILTER_PREFIX . 'show_admin_menu';
    const FILTER_CACHE_OPTIONS = WpRestApiCache::FILTER_PREFIX . 'options';
    const FILTER_CACHE_UPDATE_OPTIONS = WpRestApiCache::FILTER_PREFIX . 'update_options';
    const MENU_ID = WpRestApiCache::ID . '-empty';
    const MENU_SLUG = 'wp-rest-api-cache';
    const NONCE_ACTION = WpRestApiCache::FILTER_PREFIX . 'redirect';
    const NONCE_NAME = WpRestApiCache::FILTER_PREFIX . 'nonce';
    const NOTICE = 'rest_flush';
    const OPTION_KEY = 'wp_rest_api_cache';

    /** @var Settings $settings */
    protected $settings;

    /**
     * Admin constructor.
     */
    public function __construct()
    {
        $this->settings = new Settings([
            Settings::LENGTH => 10,
            Settings::PERIOD => MINUTE_IN_SECONDS,
        ]);
    }

    /**
     * Add class hooks.
     */
    public function addHooks()
    {
        if ($this->showAdmin()) {
            if ($this->showAdminMenu()) {
                $this->addAction('admin_menu', [$this, 'adminMenu']);
            } else {
                $this->addAction('admin_action_' . self::ADMIN_ACTION, [$this, 'adminAction']);
                $this->addAction('admin_notices', [$this, 'adminNotices']);
            }
            if ($this->showAdminMenuBar()) {
                $this->addAction('admin_bar_menu', [$this, 'adminBarMenu'], 999);
            }
            if ($this->showAdminMenu() || $this->showAdminMenuBar()) {
                $this->addFilter('map_meta_cap', [$this, 'mapMetaCap'], 10, 2);
            }
        }
    }


    /**
     * Map `self::CAPABILITY` capability.
     *
     * @param array $caps Returns the user's actual capabilities.
     * @param string $cap Capability name.
     * @return array
     */
    protected function mapMetaCap(array $caps, string $cap) : array
    {
        // Map single-site cap check to 'manage_options'
        if ($cap === self::CAPABILITY) {
            if (! \is_multisite()) {
                $caps = ['delete_users'];
            }
        }

        return $caps;
    }

    /**
     * Hook into the WordPress admin menu.
     */
    protected function adminMenu()
    {
        \add_submenu_page(
            'options-general.php',
            \esc_html__('WP REST API Cache', 'wp-rest-api-cache'),
            \esc_html__('REST API Cache', 'wp-rest-api-cache'),
            self::CAPABILITY,
            self::MENU_SLUG,
            function () {
                $this->renderPage();
            }
        );
    }

    /**
     * Hook into the WordPress admin bar.
     *
     * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar object.
     */
    protected function adminBarMenu(WP_Admin_Bar $wp_admin_bar)
    {
        if (! \is_user_logged_in() || ! \current_user_can(self::CAPABILITY) || ! \is_admin_bar_showing()) {
            return;
        }

        $wp_admin_bar->add_node([
            'id' => WpRestApiCache::ID,
            'title' => \sprintf(
                '<span class="ab-icon dashicons dashicons-shield" title="%s"></span><span class="ab-label">%s</span>',
                \esc_attr__('REST API Cache', 'wp-rest-api-cache'),
                \esc_html__('REST Cache', 'wp-rest-api-cache')
            )
        ]);
        $wp_admin_bar->add_menu([
            'parent' => WpRestApiCache::ID,
            'id' => self::MENU_ID,
            'title' => \esc_html__('Empty all cache', 'wp-rest-api-cache'),
            'href' => \esc_url($this->getEmptyCacheUrl()),
            'meta' => [
                'onclick' => \sprintf(
                    'return confirm("%s")',
                    \esc_attr__('This will clear ALL cache, continue?', 'wp-rest-api-cache')
                )
            ]
        ]);
    }

    /**
     * Helper to check the request action.
     */
    protected function adminAction()
    {
        $this->requestCallback();

        $url = \add_query_arg(
            [self::NOTICE => 1],
            \remove_query_arg(
                [RestDispatch::QUERY_CACHE_DELETE, RestDispatch::QUERY_CACHE_REFRESH],
                \wp_get_referer()
            )
        );
        \wp_safe_redirect($url);
        exit;
    }

    /**
     * Maybe add an admin notice.
     */
    protected function adminNotices()
    {
        if (! empty($_GET[self::NOTICE]) &&
            \filter_var($_GET[self::NOTICE], FILTER_VALIDATE_INT) === 1
        ) {
            $message = \esc_html__('The cache has been successfully cleared.', 'wp-rest-api-cache');
            echo "<div class='notice updated is-dismissible'><p>{$message}</p></div>"; // PHPCS: XSS OK.
        }
    }

    /**
     * Render the admin settings page.
     */
    protected function renderPage()
    {
        $this->requestCallback();
        require_once \dirname(__FILE__) . '/../../views/settings.php';
    }

    /**
     * Get an option from our options array.
     *
     * @param null|string $key Option key value.
     * @return mixed
     */
    protected function getOptions($key = null)
    {
        $options = \apply_filters(
            self::FILTER_CACHE_OPTIONS,
            \get_option(self::OPTION_KEY, $this->settings->getExpiration())
        );

        if (\is_string($key) && \array_key_exists($key, $options)) {
            return $options[$key];
        }

        return $options;
    }

    /**
     * Helper to check the request action.
     */
    private function requestCallback()
    {
        $type = 'warning';
        $message = \esc_html__('Nothing to see here.', 'wp-rest-api-cache');

        if (! empty($_REQUEST[self::NONCE_NAME]) &&
            \wp_verify_nonce($_REQUEST[self::NONCE_NAME], 'rest_cache_options') !== false
        ) {
            if (! empty($_GET['rest_cache_empty']) &&
                \filter_var($_GET['rest_cache_empty'], FILTER_VALIDATE_INT) === 1
            ) {
                if ($this->wpCacheFlush()) {
                    $type = 'updated';
                    $message = \esc_html__('The cache has been successfully cleared', 'wp-rest-api-cache');
                } else {
                    $type = 'error';
                    $message = \esc_html__('The cache is already empty', 'wp-rest-api-cache');
                }
                /**
                 * Action hook when the cache is flushed.
                 *
                 * @param string $message The message set.
                 * @param string $type The settings error code.
                 * @param \WP_User The current user.
                 */
                \do_action(self::ACTION_REQUEST_FLUSH_CACHE, $message, $type, \wp_get_current_user());
            } elseif (! empty($_POST[self::OPTION_KEY])) {
                if ($this->updateOptions($_POST[self::OPTION_KEY])) {
                    $type = 'updated';
                    $message = \esc_html__('The cache time has been updated', 'wp-rest-api-cache');
                } else {
                    $type = 'error';
                    $message = \esc_html__('The cache time has not been updated', 'wp-rest-api-cache');
                }
            }
            \add_settings_error('wp-rest-api-notice', \esc_attr('settings_updated'), $message, $type);
        }
    }

    /**
     * Update the option settings.
     *
     * @param array $options Incoming POST array.
     * @return bool
     */
    private function updateOptions(array $options) : bool
    {
        $this->settings->setLength(absint($options[Settings::EXPIRATION][Settings::LENGTH]));
        $this->settings->setPeriod(absint($options[Settings::EXPIRATION][Settings::PERIOD]));

        return \update_option(self::OPTION_KEY, $this->settings->getExpiration(), 'yes');
    }

    /**
     * Build a clear cache URL query string.
     *
     * @return string
     */
    private function getEmptyCacheUrl() : string
    {
        if ($this->showAdminMenu()) {
            return \wp_nonce_url(
                \add_query_arg(
                    [
                        'page' => self::MENU_SLUG,
                        'rest_cache_empty' => '1',
                    ],
                    \admin_url('options-general.php')
                ),
                'rest_cache_options',
                self::NONCE_NAME
            );
        }

        return \wp_nonce_url(
            \add_query_arg(
                [
                    'action' => self::ADMIN_ACTION,
                    'rest_cache_empty' => '1',
                ],
                \admin_url('admin.php')
            ),
            'rest_cache_options',
            self::NONCE_NAME
        );
    }

    /**
     * Should the admin functions be shown?
     * @return bool
     */
    private function showAdmin() : bool
    {
        return \apply_filters(self::FILTER_SHOW_ADMIN, true) === true;
    }

    /**
     * Show the admin menu be shown?
     * @return bool
     */
    private function showAdminMenu() : bool
    {
        return \apply_filters(self::FILTER_SHOW_ADMIN_MENU, true) === true;
    }

    /**
     * Show the admin menu bar be shown?
     * @return bool
     */
    private function showAdminMenuBar() : bool
    {
        return \apply_filters(self::FILTER_SHOW_ADMIN_BAR_MENU, \is_admin_bar_showing()) === true;
    }
}
