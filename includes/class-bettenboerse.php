<?php
namespace AStA\Bettenboerse;

/**
 * Main plugin class file.
 *
 * @package WordPress Plugin Template/Includes
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Main plugin class.
 */
class Bettenboerse {

	/**
	 * The single instance of Bettenboerse.
	 *
	 * @var     object
	 * @access  private
	 * @since   1.0.0
	 */
	private static $_instance = null; //phpcs:ignore

	/**
	 * Local instance of Admin_UI
	 *
	 * @var Admin_UI|null
	 */
	public $admin_ui = null;

	/**
	 * Local instance of Admin_API
	 *
	 * @var Admin_API|null
	 */
	public $admin_api = null;

	/**
	 * Settings class object
	 *
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version; //phpcs:ignore

	/**
	 * The token.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $slug = 'bettenboerse'; //phpcs:ignore

	/**
	 * The main plugin file.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for JavaScripts.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor funtion.
	 *
	 * @param string $file File constructor.
	 * @param string $version Plugin version.
	 */
	public function __construct($file = '', $version = '1.0.0') {
		$this->_version = $version;

		// Load plugin environment variables.
		$this->file       = $file;
		$this->dir        = dirname($this->file);
		$this->assets_dir = \trailingslashit($this->dir) . 'assets';
		$this->assets_url = \esc_url(\trailingslashit(\plugins_url('/assets/', $this->file)));

		$this->script_suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

		\register_activation_hook($this->file, [$this, 'install']);

		// Load frontend JS & CSS.
		// Disabled for now, we don't have a "frontend", only admin
		// add_action('wp_enqueue_scripts', [$this, 'enqueue_styles'], 10);
		// add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts'], 10);

		// Load API for generic admin functions.
		if (\is_admin()) {
			$this->admin_ui = new Admin_UI();
		}

		$this->admin_api = new Admin_API();

		// Database::instance()->seed();


		// Handle localisation.
		$this->load_plugin_textdomain();
		\add_action('init', [$this, 'load_localisation'], 0);

		$myUpdateChecker = \Puc_v4_Factory::buildUpdateChecker(
			'https://github.com/AStA-Luneburg/bettenboerse',
			$this->file,
			$this->slug
		);

		//Set the branch that contains the stable release.
		$myUpdateChecker->setBranch('main');

		//Optional: If you're using a private repository, specify the access token like this:
		// $myUpdateChecker->setAuthentication('your-token-here');
	} // End __construct ()

	/**
	 * Register post type function.
	 *
	 * @param string $post_type Post Type.
	 * @param string $plural Plural Label.
	 * @param string $single Single Label.
	 * @param string $description Description.
	 * @param array  $options Options array.
	 *
	 * @return bool|string|Post_Type
	 */
	public function register_post_type($post_type = '', $plural = '', $single = '', $description = '', $options = array()) {

		if (! $post_type || ! $plural || ! $single) {
			return false;
		}

		$post_type = new Post_Type($post_type, $plural, $single, $description, $options);

		return $post_type;
	}

	/**
	 * Wrapper function to register a new taxonomy.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @param string $plural Plural Label.
	 * @param string $single Single Label.
	 * @param array  $post_types Post types to register this taxonomy for.
	 * @param array  $taxonomy_args Taxonomy arguments.
	 *
	 * @return bool|string|Taxonomy
	 */
	public function register_taxonomy($taxonomy = '', $plural = '', $single = '', $post_types = array(), $taxonomy_args = array()) {

		if (! $taxonomy || ! $plural || ! $single) {
			return false;
		}

		$taxonomy = new Taxonomy($taxonomy, $plural, $single, $post_types, $taxonomy_args);

		return $taxonomy;
	}

	// /**
	//  * Load frontend CSS.
	//  *
	//  * @access  public
	//  * @return void
	//  * @since   1.0.0
	//  */
	// public function enqueue_styles() {
	// 	wp_register_style($this->slug . '-frontend', esc_url($this->assets_url) . 'css/frontend.css', array(), $this->_version);
	// 	wp_enqueue_style($this->slug . '-frontend');
	// } // End enqueue_styles ()

	// /**
	//  * Load frontend Javascript.
	//  *
	//  * @access  public
	//  * @return  void
	//  * @since   1.0.0
	//  */
	// public function enqueue_scripts() {
	// 	wp_register_script($this->slug . '-frontend', esc_url($this->assets_url) . 'js/frontend' . $this->script_suffix . '.js', ['jquery'), ]his->_version, true);
	// 	wp_enqueue_script($this->slug . '-frontend');
	// } // End enqueue_scripts ()

	/**
	 * Load plugin localisation
	 *
	 * @access  public
	 * @return  void
	 * @since   1.0.0
	 */
	public function load_localisation() {
		\load_plugin_textdomain('bettenboerse', false, dirname(\plugin_basename($this->file)) . '/lang/');
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 *
	 * @access  public
	 * @return  void
	 * @since   1.0.0
	 */
	public function load_plugin_textdomain() {
		$domain = 'bettenboerse';

		$locale = \apply_filters('plugin_locale', \get_locale(), $domain);

		\load_textdomain($domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo');
		\load_plugin_textdomain($domain, false, dirname(\plugin_basename($this->file)) . '/lang/');
	} // End load_plugin_textdomain ()

	/**
	 * Main Bettenboerse Instance
	 *
	 * Ensures only one instance of Bettenboerse is loaded or can be loaded.
	 *
	 * @param string $file File instance.
	 * @param string $version Version parameter.
	 *
	 * @return Object Bettenboerse instance
	 * @see Bettenboerse()
	 * @since 1.0.0
	 * @static
	 */
	public static function instance($file = '', $version = '1.0.0') {
		if (is_null(self::$_instance)) {
			self::$_instance = new self($file, $version);
		}

		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong(__FUNCTION__, esc_html(__('Cloning of Bettenboerse is forbidden')), esc_attr($this->_version));

	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong(__FUNCTION__, esc_html(__('Unserializing instances of Bettenboerse is forbidden')), esc_attr($this->_version));
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 *
	 * @access  public
	 * @return  void
	 * @since   1.0.0
	 */
	public function install() {
		$this->_log_version_number();

		Database::instance()->install();
	} // End install ()

	/**
	 * Log the plugin version number.
	 *
	 * @access  public
	 * @return  void
	 * @since   1.0.0
	 */
	private function _log_version_number() { //phpcs:ignore
		update_option($this->slug . '_version', $this->_version);
	} // End _log_version_number ()

}
