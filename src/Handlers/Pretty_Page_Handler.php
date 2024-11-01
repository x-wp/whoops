<?php //phpcs:disable Squiz.Commenting.FunctionComment.Missing
/**
 * Pretty_Page_Handler class file.
 *
 * @package eXtended WordPress
 * @subpackage Whoops
 */

namespace XWP\Whoops\Handlers;

use Automattic\Jetpack\Constants;
use Whoops\Handler\PrettyPageHandler;
use XWP\Whoops\Interfaces\Error_Handler;

/**
 * Displays the whoops pretty error page.
 *
 * @since 1.1.0
 */
class Pretty_Page_Handler extends PrettyPageHandler implements Error_Handler {
    public function __construct() {
        parent::__construct();

        foreach ( $this->getExtraTables() as $name => $generator ) {
            $this->addDataTableCallback( $name, $generator );
        }
    }

    /**
     * Get the extra tables to display on the error page.
     *
     * Adds the $post, $wp, and $wp_query global variables to the error page.
     *
     * @return array<string,callable>
     */
    protected function getExtraTables(): array {
        //phpcs:disable SlevomatCodingStandard.Arrays.AlphabeticallySortedByKeys.IncorrectKeyOrder
        $tables = array(
            'WP_Query' => array( $this, 'query_var_cb' ),
            'WP_Post'  => array( $this, 'post_var_cb' ),
		);
        //phpcs:enable SlevomatCodingStandard.Arrays.AlphabeticallySortedByKeys.IncorrectKeyOrder

        /**
         * Filter the extra tables to display on the error page.
         *
         * @param  array<string,callable> $tables The extra tables to display.
         * @return array<string,callable>
         *
         * @since 1.1.0
         */
        return \apply_filters( 'xwp_whoops_extra_tables', $tables );
    }

    public function post_var_cb() {
        $post = \get_post();

        if ( ! $post instanceof \WP_Post ) {
            return array();
        }

        return \get_object_vars( $post );
    }

    public function query_var_cb() {
        global $wp_query;

        if ( ! $wp_query instanceof \WP_Query ) {
            return array();
        }

        $output               = \get_object_vars( $wp_query );
        $output['query_vars'] = \array_filter( $output['query_vars'] );
        unset( $output['posts'] );
        unset( $output['post'] );

        foreach ( $output as &$val ) {
            if ( ! \is_string( $val ) ) {
                continue;
            }
            $val = \str_replace( "\t", ' ', $val );
        }

        return \array_filter( $output );
    }

    /**
     * Check if the user has the capability to manage options.
     *
     * @return bool
     */
    private function check_token(): bool {
        [ $iv, $data ] = \explode( '--', \xwp_fetch_cookie_var( 'xwp_whoops_token', '--' ) );

        if ( ! $iv || ! $data ) {
            return false;
        }

        $data = \openssl_decrypt( $data, 'aes-256-cbc', AUTH_KEY, 0, \hex2bin( $iv ) );
        $data = \json_decode( $data, true ) ?? array( 'id' => 0 );

        return $data['id'] && \user_can( $data['id'], 'manage_options' );
    }

    private function check_ip(): bool {
        $ips = Constants::get_constant( 'XWP_WHOOPS_ADMIN_IPS' ) ?? array();
        $req = \xwp_fetch_server_var( 'REMOTE_ADDR', '' );

        return ( '*' === $ips ) || ( $ips && \in_array( $req, $ips, true ) );
    }

    public function can_handle(): bool {
        return $this->check_token() || $this->check_ip();
    }

    public function handle() {
        return $this->can_handle()
            ? parent::handle()
            : static::DONE;
    }
}
