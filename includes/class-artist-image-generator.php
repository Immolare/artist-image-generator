<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://pierrevieville.fr
 * @since      1.0.0
 *
 * @package    Artist_Image_Generator
 * @subpackage Artist_Image_Generator/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Artist_Image_Generator
 * @subpackage Artist_Image_Generator/includes
 * @author     Pierre Viéville <contact@pierrevieville.fr>
 */
class Artist_Image_Generator {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Artist_Image_Generator_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'ARTIST_IMAGE_GENERATOR_VERSION' ) ) {
			$this->version = ARTIST_IMAGE_GENERATOR_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'artist-image-generator';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
        // Initialize the license SDK
        $this->initialize_license_sdk();
	}

    /**
	 * Initialize the license SDK.
	 *
	 * Create an instance of the license SDK and set the necessary configuration.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function initialize_license_sdk() {
		// Set the license server URL, customer key, customer secret, and product IDs
		$license_server = 'https://developpeur-web.site'; // Replace with your license server URL
		$customer_key = 'ck_cd59905ed7072a7f07ff8a028031743ec657661c'; // Replace with your customer key
		$customer_secret = 'cs_52543ce45eb75c518aa939a480539e9a226026e1'; // Replace with your customer secret
		$product_ids = [26733]; // Replace with your product IDs

		// Create an instance of the license SDK
		$license_sdk = new LMFW\SDK\License(
			'Artist Image Generator (Pro)', // Replace with the name of your plugin
			$license_server,
			$customer_key,
			$customer_secret,
			$product_ids,
			'plugin_license', // Replace with a unique value to avoid conflicts with other plugins
			'plugin-is-valid', // Replace with a unique value to avoid conflicts with other plugins
			5 // Replace with the number of days before checking the license validity
		);

		// Validate the license status
		$valid_status = $license_sdk->validate_status();
		if ($valid_status['is_valid']) {
			// The license is valid, perform actions accordingly
		} else {
			// The license is not valid, handle it as needed
		}
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Artist_Image_Generator_Loader. Orchestrates the hooks of the plugin.
	 * - Artist_Image_Generator_i18n. Defines internationalization functionality.
	 * - Artist_Image_Generator_Admin. Defines all hooks for the admin area.
	 * - Artist_Image_Generator_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

        /**
         * Load the vendor/autoload.php
         */
        require plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';

        /**
		 * The class responsible for orchestrating the licence keys
		 */
        require plugin_dir_path( dirname( __FILE__ ) ) . 'libraries/license-sdk/License.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-artist-image-generator-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-artist-image-generator-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-artist-image-generator-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-artist-image-generator-public.php';

		$this->loader = new Artist_Image_Generator_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Artist_Image_Generator_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Artist_Image_Generator_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Artist_Image_Generator_Admin( $this->get_plugin_name(), $this->get_version() );

        // Styles and Scripts
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        // Admin Settings Page
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'admin_init' );
        $this->loader->add_action( 'wp_ajax_nopriv_admin_page', $plugin_admin, 'admin_page' );
        $this->loader->add_action( 'wp_ajax_admin_page', $plugin_admin, 'admin_page' );
        $this->loader->add_action( 'wp_ajax_nopriv_add_to_media', $plugin_admin, 'add_to_media' );
        $this->loader->add_action( 'wp_ajax_add_to_media', $plugin_admin, 'add_to_media' );
        # Add meta links to plugin page
        $this->loader->add_filter( 'plugin_row_meta', $plugin_admin, 'plugin_row_meta', 10, 2 );
        # Add link to plugin settings
        $this->loader->add_filter( 'plugin_action_links', $plugin_admin, 'plugin_action_links', 10, 2 );
        # Add underscore.js's templates
        $this->loader->add_action( 'admin_footer', $plugin_admin, 'print_tabs_templates' );
        $this->loader->add_action( 'customize_controls_print_footer_scripts', $plugin_admin, 'print_tabs_templates' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Artist_Image_Generator_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Artist_Image_Generator_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
