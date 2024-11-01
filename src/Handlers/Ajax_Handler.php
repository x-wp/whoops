<?php //phpcs:disable Squiz.Commenting.FunctionComment.Missing
/**
 * Ajax_Handler class file.
 *
 * @package eXtended WordPress
 * @subpackage Whoops
 */

namespace XWP\Whoops\Handlers;

use Automattic\Jetpack\Constants;
use Whoops\Handler\JsonResponseHandler;
use XWP\Whoops\Interfaces\Error_Handler;

/**
 * Used to handle errors when doing AJAX requests.
 *
 * @since 1.1.0
 */
class Ajax_Handler extends JsonResponseHandler implements Error_Handler {
    public function can_handle(): bool {
        return Constants::is_true( 'DOING_AJAX' );
    }

    public function handle() {
        return $this->can_handle()
            ? parent::handle()
            : static::DONE;
    }
}
