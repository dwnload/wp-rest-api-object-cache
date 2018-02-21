<?php declare( strict_types=1 );

namespace Dwnload\WpRestApi\WpAdmin;

use function Dwnload\WpRestApi\Helpers\filter_var_int;
use Dwnload\WpRestApi\RestApi\CacheApi;
use Dwnload\WpRestApi\RestApi\RestDispatch;
use Dwnload\WpRestApi\WpRestApiCache;
use TheFrosty\WP\Utils\WpHooksInterface;
use WP_Admin_Bar;

class Admin implements WpHooksInterface {

    use CacheApi;

    const ACTION_REQUEST_FLUSH_CACHE = WpRestApiCache::FILTER_PREFIX . 'request_flush_cache';
    const ADMIN_ACTION = WpRestApiCache::FILTER_PREFIX . 'flush';
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
    public function __construct() {
        $this->settings = new Settings( [
            Settings::LENGTH => 1,
            Settings::PERIOD => WEEK_IN_SECONDS,
        ] );
    }

    /**
     * Add class hooks.
     */
    public function addHooks() {
        if ( \apply_filters( self::FILTER_SHOW_ADMIN, true ) ) {
            if ( \apply_filters( self::FILTER_SHOW_ADMIN_MENU, true ) ) {
                \add_action( 'admin_menu', [ $this, 'admin_menu' ] );
            } else {
                \add_action( 'admin_action_' . self::ADMIN_ACTION, [ $this, 'admin_action' ] );
                \add_action( 'admin_notices', [ $this, 'admin_notices' ] );
            }

            if ( \apply_filters( self::FILTER_SHOW_ADMIN_BAR_MENU, true ) ) {
                \add_action( 'admin_bar_menu', [ $this, 'admin_bar_menu' ], 999 );
            }
        }
    }

    /**
     * Hook into the WordPress admin menu.
     */
    public function admin_menu() {
        \add_submenu_page(
            'options-general.php',
            \esc_html__( 'WP REST API Cache', 'wp-rest-api-cache' ),
            \esc_html__( 'REST API Cache', 'wp-rest-api-cache' ),
            'manage_options',
            self::MENU_SLUG,
            [ $this, 'renderPage' ]
        );
    }

    /**
     * Hook into the WordPress admin bar.
     *
     * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar object.
     */
    public function admin_bar_menu( WP_Admin_Bar $wp_admin_bar ) {
        $args = [
            'id' => WpRestApiCache::ID,
            'title' => \esc_html__( 'REST API Cache', 'wp-rest-api-cache' ),
        ];

        $wp_admin_bar->add_node( $args );
        $wp_admin_bar->add_menu( [
            'parent' => WpRestApiCache::ID,
            'id' => self::MENU_ID,
            'title' => \esc_html__( 'Empty all cache', 'wp-rest-api-cache' ),
            'href' => \esc_url( $this->getEmptyCacheUrl() ),
        ] );
    }

    /**
     * Helper to check the request action.
     */
    public function admin_action() {
        $this->requestCallback();

        $url = \wp_nonce_url(
            \add_query_arg(
                [ self::NOTICE => 1 ],
                \remove_query_arg( [ RestDispatch::QUERY_CACHE_DELETE, RestDispatch::QUERY_CACHE_REFRESH ], \wp_get_referer() )
            ),
            self::NONCE_ACTION,
            self::NONCE_NAME
        );
        \wp_safe_redirect( $url );
        exit;
    }

    /**
     * Maybe add an admin notice.
     */
    public function admin_notices() {
        if ( ! empty( $_REQUEST[ self::NONCE_NAME ] ) &&
            \wp_verify_nonce( $_REQUEST[ self::NONCE_NAME ], self::NONCE_ACTION ) &&
            ! empty( $_GET[ self::NOTICE ] ) &&
            filter_var_int( $_GET[ self::NOTICE ] ) === 1
        ) {
            $message = \esc_html__( 'The cache has been successfully cleared.', 'wp-rest-api-cache' );
            echo "<div class='notice updated is-dismissible'><p>{$message}</p></div>"; // PHPCS: XSS OK.
        }
    }

    /**
     * Render the admin settings page.
     */
    public function renderPage() {
        $this->requestCallback();
        require_once \dirname( __FILE__ ) . '../../views/settings.php';
    }

    /**
     * Get an option from our options array.
     *
     * @param null|string $key Option key value.
     * @return mixed
     */
    public function getOptions( $key = null ) {
        $options = \apply_filters( self::FILTER_CACHE_OPTIONS, \get_option( self::OPTION_KEY, $this->settings->getExpiration() ) );

        if ( \is_string( $key ) && \array_key_exists( $key, $options ) ) {
            return $options[ $key ];
        }

        return $options;
    }

    /**
     * Helper to check the request action.
     */
    private function requestCallback() {
        $type = 'warning';
        $message = \esc_html__( 'Nothing to see here.', 'wp-rest-api-cache' );

        if ( ! empty( $_REQUEST[ self::NONCE_NAME ] ) &&
            \wp_verify_nonce( $_REQUEST[ self::NONCE_NAME ], 'rest_cache_options' )
        ) {
            if ( ! empty( $_GET['rest_cache_empty'] ) &&
                filter_var_int( $_GET['rest_cache_empty'] ) === 1
            ) {
                if ( $this->wpCacheFlush() ) {
                    $type = 'updated';
                    $message = \esc_html__( 'The cache has been successfully cleared', 'wp-rest-api-cache' );
                } else {
                    $type = 'error';
                    $message = \esc_html__( 'The cache is already empty', 'wp-rest-api-cache' );
                }
                /**
                 * Action hook when the cache is flushed.
                 *
                 * @param string $message The message set.
                 * @param string $type The settings error code.
                 * @param \WP_User The current user.
                 */
                \do_action( self::ACTION_REQUEST_FLUSH_CACHE, $message, $type, \wp_get_current_user() );
            } elseif ( isset( $_POST['rest_cache_options'] ) && ! empty( $_POST['rest_cache_options'] ) ) {
                if ( $this->updateOptions( $_POST['rest_cache_options'] ) ) {
                    $type = 'updated';
                    $message = \esc_html__( 'The cache time has been updated', 'wp-rest-api-cache' );
                } else {
                    $type = 'error';
                    $message = \esc_html__( 'The cache time has not been updated', 'wp-rest-api-cache' );
                }
            }
            \add_settings_error( 'wp-rest-api-notice', \esc_attr( 'settings_updated' ), $message, $type );
        }
    }

    /**
     * Update the option settings.
     *
     * @param array $options Incoming POST array.
     * @return bool
     */
    private function updateOptions( array $options ) : bool {
        $options = \array_map(
            'sanitize_text_field',
            \apply_filters( self::FILTER_CACHE_UPDATE_OPTIONS, $options )
        );

        return \update_option( self::OPTION_KEY, $options, 'yes' );
    }

    /**
     * Build a clear cache URL query string.
     *
     * @return string
     */
    private function getEmptyCacheUrl() : string {
        if ( \apply_filters( self::FILTER_SHOW_ADMIN_MENU, true ) ) {
            return \wp_nonce_url(
                \add_query_arg(
                    [
                        'page' => self::MENU_SLUG,
                        'rest_cache_empty' => '1',
                    ],
                    \admin_url( 'options-general.php' )
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
                \admin_url( 'admin.php' )
            ),
            'rest_cache_options',
            self::NONCE_NAME
        );
    }
}
