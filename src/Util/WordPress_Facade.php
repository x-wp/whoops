<?php
/**
 * WordPress_Facade class file.
 *
 * @package eXtended WordPress
 * @subpackage Whoops
 */

namespace XWP\Whoops\Util;

use Automattic\Jetpack\Constants;
use Whoops\Util\SystemFacade;

/**
 * Base facade class for configuring Whoops for WordPress.
 *
 * You can extend this class to provide custom configuration for Whoops.
 *
 * @since 1.1.0
 */
class WordPress_Facade extends SystemFacade {
    /**
     * Get the error reporting level.
     *
     * @return int
     */
    public function getErrorReportingLevel() {
        return Constants::get_constant( 'XWP_WHOOPS_ERROR_LEVEL' )
            ??
            \E_ERROR | \E_PARSE | \E_COMPILE_ERROR | \E_CORE_ERROR;
    }
}
