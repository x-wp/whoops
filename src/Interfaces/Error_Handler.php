<?php
/**
 * Error_Handler interface file.
 *
 * @package eXtended WordPress
 * @subpackage Whoops
 */

namespace XWP\Whoops\Interfaces;

/**
 * Defines the interface for error handlers.
 */
interface Error_Handler {
    /**
     * Check if the handler can handle the error.
     *
     * @return bool
     */
    public function can_handle(): bool;

    /**
     * Handle the error.
     *
     * @return int
     */
    public function handle();
}
