<?php
namespace AStA\Bettenboerse;

/**
 * Post type Admin API file.
 *
 * @package WordPress Plugin Template/Includes
 */

if (! defined('ABSPATH')) {
	exit;
}

class Database {
	protected static $_instance = null;
	protected $wpdb = null;
	protected $table_name = null;

	public function __construct($wpdb) {
		$this->wpdb = $wpdb;
		$this->table_name = $this->wpdb->prefix . 'bettenboerse_announcements';
	}

	public function find_announcements(string $type = null) {
		$results = $this->wpdb->get_results(
			is_null($type) 
				? "SELECT * FROM $this->table_name"
				: $this->wpdb->prepare("SELECT * FROM $this->table_name WHERE type = '%s'", $type)
		);

		$announcements = [];
		foreach ($results as $result) {
			$announcements[] = IAnnouncement::fromDBResult($result);
		}

		return $announcements;
	}

	public function create_announcement(IAnnouncement $announcement) {
		$this->wpdb->insert($this->table_name, $announcement->toArray());
	}


	public function install() {
		$charset_collate = $this->wpdb->get_charset_collate();

		$sql = "CREATE TABLE $this->table_name (
			id varchar(36) NOT NULL,
			type varchar(20) NOT NULL,
			name varchar(255) NOT NULL,
			gender varchar(255),
			email varchar(255) NOT NULL,
			phone varchar(255) NOT NULL,

			bedType varchar(255) NOT NULL,
			bedCount int NOT NULL,
			locationHint TEXT,
			wishes TEXT,

			from_date DATETIME NOT NULL, 
			until_date DATETIME NOT NULL,

			PRIMARY KEY  (id)
		) $charset_collate;";

		$this->wpdb->query($sql);
		
		// require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		// dbDelta($sql);
	}

	public function seed() {
		$this->wpdb->insert($this->table_name, [
			'id' => uniqid(),
			'type' => 'request',
			'name' => 'John Doe',
			'email' => 'test@example.com',
			'phone' => '+49 1234567890',
			'from_date' => '2020-01-01T00:00:00+00:00',
			'until_date' => '2020-01-01T00:00:00+00:00',
			'bedType' => 'Bett',
			'bedCount' => 1,
			'locationHint' => '',
			'wishes' => '',
		]);
	}

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
	public static function instance() {
		if (is_null(self::$_instance)) {
			global $wpdb;
			self::$_instance = new self($wpdb);
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
}