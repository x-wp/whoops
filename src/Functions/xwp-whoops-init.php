<?php
/**
 * Whoops auto-initialization.
 *
 * @package eXtended WordPress
 * @subpackage Whoops
 */

use XWP\Whoops\Util\Whoops_Loader;

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

if ( ! function_exists( 'xwp_whoops_init' ) && function_exists( 'add_action' ) ) :

    /**
     * Initialize Whoops.
     *
     * @return void
     */
    function xwp_whoops_init() {
        if ( ! XWP_Whoops::can_autoload() || ! XWP_Whoops::can_register() ) {
            return;
        }

        global $whoops;

        $whoops ??= ( new XWP_Whoops() )->register();
    }

    ! did_action( 'plugins_loaded' ) && ! doing_action( 'plugins_loaded' )
        ? add_action( 'plugins_loaded', 'xwp_whoops_init', PHP_INT_MIN )
        : xwp_whoops_init();

endif;
