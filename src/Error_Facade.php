<?php
/**
 * Error_Facade class
 *
 * @package eXtended WordPress
 * @subpackage Error
 */

namespace XWP\Error;

use Whoops\Util\SystemFacade;

/**
 * Extends the SystemFacade class to provide custom error reporting level.
 */
class Error_Facade extends SystemFacade {
    /**
     * Get the error reporting level.
     *
     * @return int
     */
    public function getErrorReportingLevel() {
        return E_ALL & ~\E_DEPRECATED & ~\E_USER_DEPRECATED & ~\E_STRICT & ~\E_NOTICE & ~\E_USER_NOTICE;
    }
}
