<?php
namespace AStA\Bettenboerse;

/**
 * Plugin Name: Bettenbörse
 * Version: 1.1.1
 * Plugin URI: https://github.com/AStA-Luneburg/bettenboerse
 * Description: Funktionen für die AStA Bettenbörse 
 * Author: Lukas Mateffy
 * Author URI: https://mateffy.me
 * Requires at least: 5.9
 * Tested up to: 6.0
 *
 * Text Domain: bettenboerse
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Lukas Mateffy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load plugin class files.
require_once 'includes/plugin-update-checker-4.11/plugin-update-checker.php';
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

define('BETTENBOERSE_VERSION', '1.1.0');
define('BETTENBOERSE_FILE', __FILE__);
define('BETTENBOERSE_CAPABILITY', 'manage_bettenboerse');

/*
 * Diese ID ist nur für Entwicklungszwecke!
 * Sie wird automatisch genutzt, wenn ASTA_ENV auf 'development' define()'d ist.
 */
define('BETTENBOERSE_FORM_ID_DEVELOPMENT', '6');

/*
 * Diese ID muss angepasst werden, falls das WPForms Formular 
 * auf asta-lueneburg.de geändert wird.
 */
define('BETTENBOERSE_FORM_ID', '4697');

/**
 * Returns the main instance of Bettenboerse to prevent the need to use globals.
 *
 * @return object Bettenboerse
 */
function bettenboerse() {
	$instance = Bettenboerse::instance(BETTENBOERSE_FILE, BETTENBOERSE_VERSION);

	if ( is_null( $instance->settings ) ) {
		$instance->settings = Settings::instance( $instance );
	}

	return $instance;
}

bettenboerse();
