<?php
/**
 * Plugin Name: Bettenboerse
 * Version: 1.0.0
 * Plugin URI: http://www.hughlashbrooke.com/
 * Description: This is your starter template for your next WordPress plugin.
 * Author: Hugh Lashbrooke
 * Author URI: http://www.hughlashbrooke.com/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: bettenboerse
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Hugh Lashbrooke
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load plugin class files.
require_once 'includes/class-bettenboerse.php';
require_once 'includes/class-bettenboerse-settings.php';

// Load plugin libraries.
require_once 'includes/lib/class-bettenboerse-admin-api.php';
require_once 'includes/lib/class-bettenboerse-post-type.php';
require_once 'includes/lib/class-bettenboerse-taxonomy.php';

/**
 * Returns the main instance of Bettenboerse to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Bettenboerse
 */
function bettenboerse() {
	$instance = Bettenboerse::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = Bettenboerse_Settings::instance( $instance );
	}

	return $instance;
}

bettenboerse();
