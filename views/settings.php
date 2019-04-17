<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Dwnload\WpRestApi\WpAdmin\Admin;
use Dwnload\WpRestApi\WpAdmin\Settings;

/** @var $this Admin */
if ( ! ( $this instanceof Admin ) ) {
    wp_die( sprintf( 'Please don\'t load this file outside of <code>%s.</code>', Admin::class ) );
}

$reflection = new ReflectionObject( $this );
$cache_url = $reflection->getMethod( 'getEmptyCacheUrl' );
$cache_url->setAccessible( true );
$options = $this->getOptions();

settings_errors(); ?>
<div class="wrap">
    <h1><?php esc_html_e( 'WP REST API Cache', 'wp-rest-api-cache' ); ?></h1>
    <form action="" method="POST">
        <?php wp_nonce_field( 'rest_cache_options', Admin::NONCE_NAME ); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e( 'Empty all cache', 'wp-rest-api-cache' ); ?></th>
                <td><a href="<?php echo esc_url( $cache_url->invoke( $this ) ); ?>"
                       onclick="return confirm('This will clear ALL cache, continue?')"
                       class="button button-primary"><?php esc_html_e( 'empty cache', 'wp-rest-api-cache' ); ?></a></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e( 'Cache time', 'wp-rest-api-cache' ); ?></th>
                <td>
                    <input type="number" min="1" style="width: 70px;"
                           name="<?php printf( '%s[%s][%s]', Admin::OPTION_KEY, Settings::EXPIRATION, Settings::LENGTH ); ?>"
                           value="<?php echo absint( $options[ Settings::EXPIRATION ][ Settings::LENGTH ] ); ?>">
                    <?php $period = absint( $options[ Settings::EXPIRATION ][ Settings::PERIOD ] ); ?>
                    <select name="<?php printf( '%s[%s][%s]', Admin::OPTION_KEY, Settings::EXPIRATION, Settings::PERIOD ); ?>">
                        <option value="<?php echo absint( MINUTE_IN_SECONDS ); ?>"<?php selected( $period, MINUTE_IN_SECONDS ); ?>>
                            <?php esc_html_e( 'Minute(s)', 'wp-rest-api-cache' ); ?>
                        </option>
                        <option value="<?php echo absint( HOUR_IN_SECONDS ); ?>"<?php selected( $period, HOUR_IN_SECONDS ); ?>>
                            <?php esc_html_e( 'Hour(s)', 'wp-rest-api-cache' ); ?>
                        </option>
                        <option value="<?php echo absint( DAY_IN_SECONDS ); ?>"<?php selected( $period, DAY_IN_SECONDS ); ?>>
                            <?php esc_html_e( 'Day(s)', 'wp-rest-api-cache' ); ?>
                        </option>
                        <option value="<?php echo absint( WEEK_IN_SECONDS ); ?>"<?php selected( $period, WEEK_IN_SECONDS ); ?>>
                            <?php esc_html_e( 'Week(s)', 'wp-rest-api-cache' ); ?>
                        </option>
                        <option value="<?php echo absint( YEAR_IN_SECONDS ); ?>"<?php selected( $period, YEAR_IN_SECONDS ); ?>>
                            <?php esc_html_e( 'Year(s)', 'wp-rest-api-cache' ); ?>
                        </option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e( 'Disable REST Cache', 'wp-rest-api-cache' ); ?></th>
                <td>
                    <input type="checkbox"
                           name="<?php printf( '%s[%s]', Admin::OPTION_KEY, Settings::BYPASS ); ?>"
                           value="on"<?php checked($options[Settings::BYPASS], 'on'); ?>>

                </td>
            </tr>
            <tr>
                <th scope="row">&nbsp;</th>
                <td><input type="submit" class="button button-primary"
                           value="<?php esc_attr_e( 'save changes', 'wp-rest-api-cache' ); ?>"></td>
            </tr>
        </table>
    </form>
</div>
