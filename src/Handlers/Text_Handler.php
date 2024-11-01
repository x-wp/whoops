<?php //phpcs:disable Squiz.Commenting.FunctionComment.Missing
/**
 * Text_Handler class file.
 *
 * @package eXtended WordPress
 * @subpackage Whoops
 */

namespace XWP\Whoops\Handlers;

use Automattic\Jetpack\Constants;
use Whoops\Handler\PlainTextHandler;
use Whoops\Util\Misc;
use XWP\Whoops\Interfaces\Error_Handler;

/**
 * Displays the error message as plain text.
 *
 * @since 1.1.0
 */
class Text_Handler extends PlainTextHandler implements Error_Handler {
    public function can_handle(): bool {
        return Misc::isCommandLine() || Constants::is_true( 'WP_CLI' );
    }

    public function handle() {
        return $this->can_handle()
            ? parent::handle()
            : static::DONE;
    }
}
