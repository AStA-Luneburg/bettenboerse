<?php
namespace AStA\Bettenboerse;

/**
 * Plugin Name: Bettenboerse
 * Version: 1.0.0
 * Plugin URI: https://github.com/AStA-Luneburg/bettenboerse
 * Description: Funktionen für die AStA Bettenbörse 
 * Author: Lukas Mateffy
 * Author URI: https://mateffy.me
 * Requires at least: 5.9
 * Tested up to: 5.9
 *
 * Text Domain: bettenboerse
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Lukas Mateffy
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load plugin class files.
require_once 'includes/lib/class-db.php';
require_once 'includes/class-bettenboerse.php';
require_once 'includes/class-settings.php';

// Load bettenboerse models
require_once 'includes/lib/class-announcement.php';

// Load admin functionality
require_once 'includes/lib/admin/helpers.php';
require_once 'includes/lib/admin/class-admin-components.php';
require_once 'includes/lib/admin/class-admin-api.php';
require_once 'includes/lib/admin/class-admin-ui.php';

// require_once 'includes/lib/class-post-type.php';
// require_once 'includes/lib/class-taxonomy.php';

/**
 * Returns the main instance of Bettenboerse to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Bettenboerse
 */
function bettenboerse() {
	$instance = Bettenboerse::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = Settings::instance( $instance );
	}

	return $instance;
}

bettenboerse();
