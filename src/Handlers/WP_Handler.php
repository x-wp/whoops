<?php //phpcs:disable Squiz.Commenting.FunctionComment.Missing
/**
 * WP_Handler class file.
 *
 * @package eXtended WordPress
 * @subpackage Whoops
 */

namespace XWP\Whoops\Handlers;

use Automattic\Jetpack\Constants;
use Whoops\Handler\PlainTextHandler;
use XWP\Whoops\Interfaces\Error_Handler;

/**
 * Displays the error message in the browser - similar to the default WordPress error page.
 */
class WP_Handler extends PlainTextHandler implements Error_Handler {
    public function can_handle(): bool {
        return ! Constants::is_true( 'WP_SANDBOX_SCRAPING' ) && ! \wp_is_maintenance_mode();
    }

    public function handle() {
        if ( ! $this->can_handle() ) {
            return static::DONE;
        }

        $response = $this->generateResponse();

        if ( $this->getLogger() ) {
            $this->getLogger()->error( $response );
        }

        if ( $this->loggerOnly() ) {
            return static::DONE;
        }

        \_default_wp_die_handler(
            \nl2br( $response ),
            'Whoops! There was an error.',
            array( 'exit' => false ),
        );

        return static::QUIT;
    }

    /**
     * Get the content type for the error message.
     *
     * @return string
     */
    public function contentType() {
        return 'text/html';
    }
}
