<?php
namespace AStA\Bettenboerse;

/**
 * Settings class file.
 *
 * @package WordPress Plugin Template/Settings
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Settings class.
 */
class Settings {

	/**
	 * The single instance of Bettenboerse_Settings.
	 *
	 * @var     object
	 * @access  private
	 * @since   1.0.0
	 */
	private static $_instance = null; //phpcs:ignore

	/**
	 * The main plugin object.
	 *
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = 'bettenboerse';

	/**
	 * Available settings for plugin.
	 *
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	/**
	 * Constructor function.
	 *
	 * @param object $parent Parent object.
	 */
	public function __construct($parent) {
		$this->parent = $parent;

		$this->base = 'bettenboerse_';

		// Initialise settings.
		\add_action('init', array($this, 'init_settings'), 11);

		// Register plugin settings.
		\add_action('admin_init', array($this, 'register_settings'));

		// Add settings page to menu.
		\add_action('admin_menu', array($this, 'add_menu_item'));

		// Add settings link to plugins page.
		\add_filter(
			'plugin_action_links_' . plugin_basename($this->parent->file),
			array(
				$this,
				'add_settings_link',
			)
		);

		// Configure placement of plugin settings page. See readme for implementation.
		\add_filter($this->base . 'menu_settings', array($this, 'configure_settings'));
	}

	/**
	 * Initialise settings
	 *
	 * @return void
	 */
	public function init_settings() {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 *
	 * @return void
	 */
	public function add_menu_item() {

		$args = $this->menu_settings();

		// Do nothing if wrong location key is set.
		if (is_array($args) && isset($args['location']) && function_exists('add_' . $args['location'] . '_page')) {
			switch ($args['location']) {
				case 'options':
				case 'submenu':
					$page = add_submenu_page($args['parent_slug'], $args['page_title'], $args['menu_title'], $args['capability'], $args['menu_slug'], $args['function']);
					break;
				case 'menu':
					$page = add_menu_page($args['page_title'], $args['menu_title'], $args['capability'], $args['menu_slug'], $args['function'], $args['icon_url'], $args['position']);
					break;
				default:
					return;
			}
			\add_action('admin_print_styles-' . $page, array($this, 'settings_assets'));
		}
	}

	/**
	 * Prepare default settings page arguments
	 *
	 * @return mixed|void
	 */
	private function menu_settings() {
		return \apply_filters(
			$this->base . 'menu_settings',
			array(
				'location'    => 'options', // Possible settings: options, menu, submenu.
				'parent_slug' => 'options-general.php',
				'page_title'  => __('Einstellungen der Bettenbörse', 'bettenboerse'),
				'menu_title'  => __('Bettenbörse', 'bettenboerse'),
				'capability'  => BETTENBOERSE_CAPABILITY,
				'menu_slug'   => $this->parent->slug . '_settings',
				'function'    => array($this, 'settings_page'),
				'icon_url'    => '',
				'position'    => null,
			)
		);
	}

	/**
	 * Container for settings page arguments
	 *
	 * @param array $settings Settings array.
	 *
	 * @return array
	 */
	public function configure_settings($settings = array()) {
		return $settings;
	}

	/**
	 * Load settings JS & CSS
	 *
	 * @return void
	 */
	public function settings_assets() {
		// We're including the WP media scripts here because they're needed for the image upload field.
		// If you're not including an image upload then you can leave this function call out.
		wp_enqueue_media();

		wp_register_script($this->parent->slug . '-settings-js', $this->parent->assets_url . 'js/settings' . $this->parent->script_suffix . '.js', array('farbtastic', 'jquery'), '1.0.0', true);
		wp_enqueue_script($this->parent->slug . '-settings-js');
	}

	/**
	 * Add settings link to plugin list table
	 *
	 * @param  array $links Existing links.
	 * @return array        Modified links.
	 */
	public function add_settings_link($links) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->slug . '_settings">' . __('Settings') . '</a>';
		array_push($links, $settings_link);
		return $links;
	}

	/**
	 * Build settings fields
	 *
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields() {
		// 0 => 'name',
		// 1 => 'email',
		// 3 => 'type',
		// 4 => 'phone',
		// 6 => 'bedType',
		// 8 => 'bedCount',
		// 9 => 'from',
		// 10 => 'until',
		// 12 => 'locationHint',
		// 13 => 'wishes',
		// 14 => 'privacy',
		// 15 => 'gender',
		// //
		$settings['standard'] = array(
			'title'       => __('WPForms Zuweisung', 'bettenboerse'),
			'description' => __('Die Felder des Formulars können hier den Daten zugeteilt werden.', 'bettenboerse'),
			'fields'      => array(
				array(
					'id'          => 'form_field',
					'label'       => __('ID des Formulars', 'bettenboerse'),
					'description' => __('Die WPForms ID', 'bettenboerse'),
					'type'        => 'number',
					'min'         => 0,
					'default'     => '',
					'placeholder' => __('Formular-ID', 'bettenboerse'),
				),
				array(
					'id'          => 'name_field',
					'label'       => __('Feld für den Namen', 'bettenboerse'),
					'description' => __('Feld-Inhalt: Text', 'bettenboerse'),
					'type'        => 'number',
					'min'         => 0,
					'default'     => '',
					'placeholder' => __('Feld-ID', 'bettenboerse'),
				),
				array(
					'id'          => 'email_field',
					'label'       => __('Feld für die E-Mail', 'bettenboerse'),
					'description' => __('Feld-Inhalt: Text / E-Mail', 'bettenboerse'),
					'type'        => 'number',
					'min'         => 0,
					'default'     => '',
					'placeholder' => __('Feld-ID', 'bettenboerse'),
				),
				array(
					'id'          => 'type_field',
					'label'       => __('Feld für die Art der Anfrage', 'bettenboerse'),
					'description' => __('Feld-Inhalt: Multi-Choice', 'bettenboerse'),
					'type'        => 'number',
					'min'         => 0,
					'default'     => '',
					'placeholder' => __('Feld-ID', 'bettenboerse'),
				),
				array(
					'id'          => 'phone_field',
					'label'       => __('Feld für die Telefonnummer', 'bettenboerse'),
					'description' => __('Feld-Inhalt: Text', 'bettenboerse'),
					'type'        => 'number',
					'min'         => 0,
					'default'     => '',
					'placeholder' => __('Feld-ID', 'bettenboerse'),
				),
				array(
					'id'          => 'bedType_field',
					'label'       => __('Feld für die Art der Betten', 'bettenboerse'),
					'description' => __('Feld-Inhalt: Text', 'bettenboerse'),
					'type'        => 'number',
					'min'         => 0,
					'default'     => '',
					'placeholder' => __('Feld-ID', 'bettenboerse'),
				),
				array(
					'id'          => 'bedCount_field',
					'label'       => __('Feld für die Anzahl der Betten', 'bettenboerse'),
					'description' => __('Feld-Inhalt: Nummer', 'bettenboerse'),
					'type'        => 'number',
					'min'         => 0,
					'default'     => '',
					'placeholder' => __('Feld-ID', 'bettenboerse'),
				),
				array(
					'id'          => 'from_field',
					'label'       => __('Feld für das Start-Datum', 'bettenboerse'),
					'description' => __('Feld-Inhalt: Datum', 'bettenboerse'),
					'type'        => 'number',
					'min'         => 0,
					'default'     => '',
					'placeholder' => __('Feld-ID', 'bettenboerse'),
				),
				array(
					'id'          => 'until_field',
					'label'       => __('Feld für das End-Datum', 'bettenboerse'),
					'description' => __('Feld-Inhalt: Datum', 'bettenboerse'),
					'type'        => 'number',
					'min'         => 0,
					'default'     => '',
					'placeholder' => __('Feld-ID', 'bettenboerse'),
				),
				array(
					'id'          => 'locationHint_field',
					'label'       => __('Feld für die Ortsinformationen', 'bettenboerse'),
					'description' => __('Feld-Inhalt: Text', 'bettenboerse'),
					'type'        => 'number',
					'min'         => 0,
					'default'     => '',
					'placeholder' => __('Feld-ID', 'bettenboerse'),
				),
				array(
					'id'          => 'wishes_field',
					'label'       => __('Feld für die Wünsche', 'bettenboerse'),
					'description' => __('Feld-Inhalt: Text', 'bettenboerse'),
					'type'        => 'number',
					'min'         => 0,
					'default'     => '',
					'placeholder' => __('Feld-ID', 'bettenboerse'),
				),
				array(
					'id'          => 'gender_field',
					'label'       => __('Feld für das Geschlecht', 'bettenboerse'),
					'description' => __('Feld-Inhalt: Text', 'bettenboerse'),
					'type'        => 'number',
					'min'         => 0,
					'default'     => '',
					'placeholder' => __('Feld-ID', 'bettenboerse'),
				),
				array(
					'id'          => 'privacy_field',
					'label'       => __('Feld für das Datenschutz-Häckchen', 'bettenboerse'),
					'description' => __('Feld-Inhalt: Haken gesetzt', 'bettenboerse'),
					'type'        => 'number',
					'min'         => 0,
					'default'     => '',
					'placeholder' => __('Feld-ID', 'bettenboerse'),
				),
			),
		);

		$settings['types'] = array(
			'title'       => __('Typ-Zuweisung', 'bettenboerse'),
			'description' => __('Damit die Art der Anfrage richtig erkannt wird, müssen die Multiple-Choice Texte hier Ersuch und Angebot zugewiesen werden.', 'bettenboerse'),
			'fields'      => array(
				array(
					'id'          => 'offer_multi_field',
					'label'       => __('Angebot Text', 'bettenboerse'),
					'description' => __('Dieses Feld muss mit dem in WPForms identisch sein.', 'bettenboerse'),
					'type'        => 'text',
					'default'     => 'Ich biete',
					'placeholder' => __('Ich biete', 'bettenboerse'),
					'required'    => true
				),
				array(
					'id'          => 'request_multi_field',
					'label'       => __('Ersuch Text', 'bettenboerse'),
					'description' => __('Dieses Feld muss mit dem in WPForms identisch sein.', 'bettenboerse'),
					'type'        => 'text',
					'default'     => 'Ich suche',
					'placeholder' => __('Ich suche', 'bettenboerse'),
				),
			),
		);

		$settings = \apply_filters($this->parent->slug . '_settings_fields', $settings);

		return $settings;
	}

	/**
	 * Register plugin settings
	 *
	 * @return void
	 */
	public function register_settings() {
		if (is_array($this->settings)) {

			// Check posted/selected tab.
			//phpcs:disable
			$current_section = '';
			if (isset($_POST['tab']) && $_POST['tab']) {
				$current_section = $_POST['tab'];
			} else {
				if (isset($_GET['tab']) && $_GET['tab']) {
					$current_section = $_GET['tab'];
				}
			}
			//phpcs:enable

			foreach ($this->settings as $section => $data) {

				if ($current_section && $current_section !== $section) {
					continue;
				}

				// Add section to page.
				add_settings_section($section, $data['title'], array($this, 'settings_section'), $this->parent->slug . '_settings');

				foreach ($data['fields'] as $field) {

					// Validation callback for field.
					$validation = '';
					if (isset($field['callback'])) {
						$validation = $field['callback'];
					}

					// Register field.
					$option_name = $this->base . $field['id'];
					register_setting($this->parent->slug . '_settings', $option_name, $validation);

					// Add field to page.
					add_settings_field(
						$field['id'],
						$field['label'],
						array($this->parent->admin_api, 'display_field'),
						$this->parent->slug . '_settings',
						$section,
						array(
							'field'  => $field,
							'prefix' => $this->base,
						)
					);
				}

				if (! $current_section) {
					break;
				}
			}
		}
	}

	/**
	 * Settings section.
	 *
	 * @param array $section Array of section ids.
	 * @return void
	 */
	public function settings_section($section) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html; //phpcs:ignore
	}

	/**
	 * Load settings page content.
	 *
	 * @return void
	 */
	public function settings_page() {

		// Build page HTML.
		$html      = '<div class="wrap" id="' . $this->parent->slug . '_settings">' . "\n";
			$html .= '<h2>' . __('Einstellungen der Bettenbörse', 'bettenboerse') . '</h2>' . "\n";

			$tab = '';
		//phpcs:disable
		if (isset($_GET['tab']) && $_GET['tab']) {
			$tab .= $_GET['tab'];
		}
		//phpcs:enable

		// Show page tabs.
		if (is_array($this->settings) && 1 < count($this->settings)) {

			$html .= '<h2 class="nav-tab-wrapper">' . "\n";

			$c = 0;
			foreach ($this->settings as $section => $data) {

				// Set tab class.
				$class = 'nav-tab';
				if (! isset($_GET['tab'])) { //phpcs:ignore
					if (0 === $c) {
						$class .= ' nav-tab-active';
					}
				} else {
					if (isset($_GET['tab']) && $section == $_GET['tab']) { //phpcs:ignore
						$class .= ' nav-tab-active';
					}
				}

				// Set tab link.
				$tab_link = add_query_arg(array('tab' => $section));
				if (isset($_GET['settings-updated'])) { //phpcs:ignore
					$tab_link = remove_query_arg('settings-updated', $tab_link);
				}

				// Output tab.
				$html .= '<a href="' . $tab_link . '" class="' . esc_attr($class) . '">' . esc_html($data['title']) . '</a>' . "\n";

				++$c;
			}

			$html .= '</h2>' . "\n";
		}

			$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

				// Get settings fields.
				ob_start();
				settings_fields($this->parent->slug . '_settings');
				do_settings_sections($this->parent->slug . '_settings');
				$html .= ob_get_clean();

				$html     .= '<p class="submit">' . "\n";
					$html .= '<input type="hidden" name="tab" value="' . esc_attr($tab) . '" />' . "\n";
					$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr(__('Save Settings', 'bettenboerse')) . '" />' . "\n";
				$html     .= '</p>' . "\n";
			$html         .= '</form>' . "\n";
		$html             .= '</div>' . "\n";

		echo $html; //phpcs:ignore
	}

	/**
	 * Main Bettenboerse_Settings Instance
	 *
	 * Ensures only one instance of Bettenboerse_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Bettenboerse()
	 * @param object $parent Object instance.
	 * @return object Bettenboerse_Settings instance
	 */
	public static function instance($parent) {
		if (is_null(self::$_instance)) {
			self::$_instance = new self($parent);
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong(__FUNCTION__, esc_html(__('Cloning of Bettenboerse_API is forbidden.')), esc_attr($this->parent->_version));
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong(__FUNCTION__, esc_html(__('Unserializing instances of Bettenboerse_API is forbidden.')), esc_attr($this->parent->_version));
	} // End __wakeup()

}
