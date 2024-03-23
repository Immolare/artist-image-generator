<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://pierrevieville.fr
 * @since             1.0.0
 * @package           Artist_Image_Generator
 *
 * @wordpress-plugin
 * Plugin Name:       Artist Image Generator
 * Plugin URI:        https://artist-image-generator.com/
 * Description:       Illustrate posts with Ai images (DALL·E). Text-to-Image, variation, editing. Public image generator & Ai avatars by topics.
 * Version:           1.1.2
 * Author:            Pierre Viéville
 * Author URI:        https://www.pierrevieville.fr
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       artist-image-generator
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'ARTIST_IMAGE_GENERATOR_VERSION', '1.1.2' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-artist-image-generator-activator.php
 */
function activate_artist_image_generator() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-artist-image-generator-activator.php';
	Artist_Image_Generator_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-artist-image-generator-deactivator.php
 */
function deactivate_artist_image_generator() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-artist-image-generator-deactivator.php';
	Artist_Image_Generator_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_artist_image_generator' );
register_deactivation_hook( __FILE__, 'deactivate_artist_image_generator' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-artist-image-generator.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_artist_image_generator() {

	$plugin = new Artist_Image_Generator();
	$plugin->run();

}
run_artist_image_generator();
