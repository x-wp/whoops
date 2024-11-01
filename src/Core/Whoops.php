<?php //phpcs:disable Universal.Operators.DisallowShortTernary.Found
/**
 * XWP_Whoops class file.
 *
 * @package eXtended WordPress
 * @subpackage Whoops
 */

use Automattic\Jetpack\Constants;
use Whoops\Handler\HandlerInterface;
use Whoops\Run as Whoops_Run;
use Whoops\Util\SystemFacade;
use XWP\Whoops\Handlers;
use XWP\Whoops\Util\WordPress_Facade;

/**
 * Whoops error handler class for WordPress.
 *
 * @template TFc of SystemFacade
 */
class XWP_Whoops {
    /**
     * The Whoops error handler instance.
     *
     * @var Whoops_Run
     */
    private Whoops_Run $whoops;

    /**
     * Whether the error handler is registered.
     *
     * @var bool
     */
    private bool $registered = false;

    /**
     * The default handlers to use.
     *
     * @var array<callable|class-string<HandlerInterface>|HandlerInterface>
     */
    private array $default_handlers = array(
        Handlers\WP_Handler::class,
        Handlers\Pretty_Page_Handler::class,
        Handlers\Ajax_Handler::class,
        Handlers\Text_Handler::class,
    );

    /**
     * Check if Whoops can be auto-loaded.
     *
     * @return bool
     */
    public static function can_autoload(): bool {
        $autoload = Constants::get_constant( 'XWP_WHOOPS_AUTOLOAD' ) ?? true;

        /**
         * Allow to disable Whoops auto-initialization.
         *
         * @param  bool $auto_init Whether to auto-initialize Whoops. Default is true.
         * @return bool
         *
         * @since 1.0.0
         */
        return \apply_filters( 'xwp_whoops_autoload', $autoload );
    }

    /**
     * Check if the error handler can be registered.
     *
     * @return bool
     */
    public static function can_register(): bool {
        $enabled = Constants::get_constant( 'XWP_WHOOPS_ENABLED' ) ?? true;
        $enabled = $enabled || 'production' !== \wp_get_environment_type() || Constants::is_true( 'WP_DEBUG' );

        /**
         * Filter whether the error handler can be registered.
         *
         * @param  bool $enabled Whether the error handler can be registered.
         * @return bool
         *
         * @since 1.0.0
         */

        return \apply_filters( 'xwp_whoops_enabled', $enabled );
    }

    /**
     * Constructor.
     *
     * @param null|class-string<TFc>|TFc                                      $facade   The facade class to use.
     * @param array<callable|class-string<HandlerInterface>|HandlerInterface> $handlers The handlers to use.
     */
    public function __construct( null|string|object $facade = null, array $handlers = array() ) {
        $this
            ->load( $facade )
            ->with_handlers( ...$handlers );
    }

    /**
     *
     * Load the Whoops error handler.
     *
     * @param null|class-string<TFc>|TFc $facade The facade class to use.
     */
    protected function load( null|string|object $facade ): static {
        $this->whoops = new Whoops_Run( $this->resolve_facade( $facade ) );

        return $this;
    }

    /**
     * Set the facade class to use.
     *
     * @param  null|class-string<TFc>|TFc $facade The facade class to use.
     * @return static
     */
    public function with_facade( null|string|object $facade = null ): static {
        $handlers = $this->whoops->getHandlers();
        $regged   = $this->registered;

        $this
            ->unload()
            ->load( $facade )
            ->with_handlers( ...$handlers );

        return $regged ? $this->register() : $this;
    }

    /**
     * Add a handler to the whoops error handler.
     *
     * @template THd of HandlerInterface
     * @param  callable|class-string<THd>|THd ...$handlers The handlers to add.
     * @return static
     */
    public function with_handlers( string|callable|HandlerInterface ...$handlers ): static {
        $handlers = $handlers ?: $this->default_handlers;
        $handlers = array_map( array( $this, 'resolve_handler' ), $handlers );

        /**
         * Filter the handlers to use.
         *
         * @param  array<callable|HandlerInterface> $handlers The handlers to use.
         * @return array<callable|HandlerInterface>
         *
         * @since 1.0.0
         */
        $handlers = apply_filters( 'xwp_whoops_handlers', $handlers );

        foreach ( $handlers as $handler ) {
            $this->whoops->pushHandler( $handler );
        }

        return $this;
    }

    /**
     * Get the facade class to use.
     *
     * @template TFc of SystemFacade
     * @param null|class-string<TFc>|TFc $facade The facade class to use.
     * @return TFc
     */
    private function resolve_facade( null|object|string $facade ): SystemFacade {
        $facade ??= Constants::get_constant( 'XWP_WHOOPS_FACADE' ) ?? WordPress_Facade::class;

        /**
         * Filter the facade class to use.
         *
         * @param  class-string<TFc>|TFc $facade The facade class to use.
         * @return class-string<TFc>|TFc
         *
         * @since 1.0.0
         */
        $facade = apply_filters( 'xwp_whoops_facade', $facade );

        if ( ! is_object( $facade ) ) {
            $facade = new $facade();
        }

        return $facade;
    }

    /**
     * Add a handler to the whoops error handler.
     *
     * @template THd of HandlerInterface
     * @param  callable|class-string<THd>|THd $handler The handler to add.
     * @return callable|THd
     */
    private function resolve_handler( string|callable|HandlerInterface $handler ): callable|HandlerInterface {
        if ( is_string( $handler ) && class_exists( $handler ) ) {
            $handler = new $handler();
        }

        return $handler;
    }

    /**
     * Register the error handler.
     *
     * @return static
     */
    public function register(): static {
        if ( ! $this->registered ) {
            add_filter( 'wp_should_handle_php_error', array( $this, 'disable_wp_handler' ), PHP_INT_MAX, 0 );
            add_action( 'wp_login', array( $this, 'generate_token' ), 0, 2 );
            add_action( 'wp_logout', array( $this, 'delete_token' ) );

            $this->registered = true;
        }

        if ( ! $this->whoops->getHandlers() ) {
            $this->with_handlers( ...$this->default_handlers );
        }

        $this->whoops->register();

        return $this;
    }

    /**
     * Unregister the error handler.
     *
     * @return static
     */
    public function unregister(): static {
        if ( $this->registered ) {
			remove_filter( 'wp_should_handle_php_error', array( $this, 'disable_wp_handler' ), PHP_INT_MAX );
			remove_action( 'wp_login', array( $this, 'generate_token' ), 0 );
			remove_action( 'wp_logout', array( $this, 'delete_token' ) );

            $this->registered = false;
        }

        $this->whoops->unregister();

        return $this;
    }

    /**
     * Unload the error handler.
     *
     * @return static
     */
    public function unload(): static {
        $this->unregister();

        unset( $this->whoops );

        return $this;
    }

    /**
     * Generate a token for the current user.
     *
     * @param string  $login The user's login.
     * @param WP_User $user  The user object.
     */
    public function generate_token( string $login, WP_User $user ): void {
        if ( '' !== xwp_fetch_cookie_var( 'xwp_whoops_token' ) || ! $user->allcaps['manage_options'] ) {
            return;
        }
        $cypher = 'aes-256-cbc';
        $iv_key = random_bytes( openssl_cipher_iv_length( $cypher ) );
        $params = wp_json_encode( array( 'id' => $user->ID ) );
        $string = bin2hex( $iv_key ) . '--' . openssl_encrypt( $params, $cypher, AUTH_KEY, 0, $iv_key );

        $r = setcookie( 'xwp_whoops_token', $string, time() + 3600, '/', '', true );
    }

    /**
     * Delete the token for the current user.
     */
    public function delete_token(): void {
        if ( ! xwp_fetch_cookie_var( 'xwp_whoops_token' ) ) {
            return;
        }

        setcookie( 'xwp_whoops_token', '', time() - 3600, '/', '', true );
    }

    /**
     * Filter callback to disable the default WordPress error handler.
     *
     * @return false
     */
    public function disable_wp_handler(): bool {
        return false;
    }
}
