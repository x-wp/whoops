<?php
/**
 * Error_Handler class file.
 *
 * @package eXtended WordPress
 * @subpackage Error
 */

namespace XWP\Error;

use Automattic\Jetpack\Constants;

/**
 * Error handler.
 */
class Error_Handler {
    /**
     * Whoops instance.
     *
     * @var \Whoops\Run
     */
    protected \Whoops\Run $whoops;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->whoops = new \Whoops\Run( new Error_Facade() );
    }

    /**
     * Check if the current IP is allowed to see the error page.
     *
     * @return bool
     */
    protected function check_ip(): bool {
        /**
         * Filter the allowed IPs to see the error page.
         *
         * @param  array<int,string> $allowed_ips The allowed IPs.
         * @return array<int,string>
         *
         * @since 1.0.0
         */
        $allowed_ips = \apply_filters( 'xwp_whoops_admin_ips', array() );
        $ip          = \xwp_fetch_server_var( 'REMOTE_ADDR', '' );

        return ! $allowed_ips || \in_array( $ip, $allowed_ips, true );
    }

    /**
     * Register the error handler.
     */
    public function register() {
        if ( Constants::is_true( 'WP_CLI' ) ) {
            $this->whoops->appendHandler( new \Whoops\Handler\PlainTextHandler() );
        }

        if ( Constants::is_true( 'WP_DEBUG' ) || $this->check_ip() ) {
            $this->whoops->prependHandler( new \Whoops\Handler\PrettyPageHandler() );
        }

        if ( \wp_doing_ajax() ) {
            $this->whoops->prependHandler( new \Whoops\Handler\JsonResponseHandler() );
        }

        $this->whoops->register();
    }
}
