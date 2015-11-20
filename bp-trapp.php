<?php
/**
 * BP WP TRAPP Plugin
 *
 * Plugin Name:       BP WP TRAPP
 * Plugin URI:        https://github.com/BenjaminMedia/wp-trapp
 * Description:       Send content to the TRAPP translation service.
 * Version:           0.1
 * Text Domain:       bp-trapp
 * Domain Path:       /languages
 */

namespace Bonnier\WP\Trapp;

// Do not access this file directly
if (!defined( 'ABSPATH')) {
	exit;
}

class Plugin {
	/**
	 * Text domain for translators
	 */
	const TEXT_DOMAIN = 'bp-trapp';

	/**
	 * @var object Instance of this class.
	 */
	private static $instance;

	/**
	 * @var string Filename of this class.
	 */
	public $file;

	/**
	 * @var string Basename of this class.
	 */
	public $basename;

	/**
	 * @var string Plugins directory for this plugin.
	 */
	public $plugin_dir;

	/**
	 * @var string Plugins url for this plugin.
	 */
	public $plugin_url;

	/**
	 * Do not load this more than once.
	 */
	private function __construct() {}

	/**
	 * Returns the instance of this class.
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
			self::$instance->bootstrap();

			/**
			 * Run after the plugin has been loaded.
			 */
			do_action( 'bp_trapp_loaded' );
		}

		return self::$instance;
	}

	/**
	 * Plugin boostrap.
	 */
	private function bootstrap() {
		// Set plugin file variables
		$this->file       = __FILE__;
		$this->basename   = plugin_basename( $this->file );
		$this->plugin_dir = plugin_dir_path( $this->file );
		$this->plugin_url = plugin_dir_url( $this->file );

		// Load textdomain
		load_plugin_textdomain( self::TEXT_DOMAIN, false, dirname( $this->basename ) . '/languages' );

		// Autoload classes if not already autoloaded
		if (file_exists($this->plugin_dir . 'vendor/autoload.php')) {
			require( $this->plugin_dir . 'vendor/autoload.php' );
		}

		// Bootstrap
		$bootstrap = new Core\Bootstrap;
		$bootstrap->bootstrap();
	}
}

function plugin() {
	return Plugin::instance();
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\plugin' );
