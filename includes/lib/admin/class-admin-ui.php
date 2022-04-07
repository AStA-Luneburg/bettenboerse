<?php

namespace AStA\Bettenboerse;

if (!defined('ABSPATH')) {
	exit;
}


/**
 * Admin UI class.
 */
class Admin_UI
{
	/**
	 * Constructor function
	 */
	public function __construct() {
		// Load admin JS & CSS.
		// \add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 10, 1);
		\add_action(
			'admin_enqueue_scripts', 
			function() {
				$plugin = Bettenboerse::instance();

				// Admin Scripts
				\wp_register_script('visjs-timeline', \esc_url($plugin->assets_url) . 'js/vis-timeline.min.js', ['jquery'], '7.5.1', true);
				\wp_register_script($plugin->slug . '-admin', \esc_url($plugin->assets_url) . 'js/admin.js', ['jquery', 'jquery-ui-dialog', 'visjs-timeline'], $plugin->_version, true);
				\wp_enqueue_script($plugin->slug . '-admin');
				wp_localize_script(
					$plugin->slug . '-admin', 
					'bettenboerse_environment',
					[ 
						'ajaxurl' => admin_url('admin-ajax.php'),
					]
   				);

				// Admin Styles
				\wp_register_style('visjs-timeline', \esc_url($plugin->assets_url) . 'css/vis-timeline.min.css', [], '7.5.1');
				\wp_register_style($plugin->slug . '-admin', \esc_url($plugin->assets_url) . 'css/admin.dist.css', ['visjs-timeline', 'wp-jquery-ui-dialog'], $plugin->_version);
				\wp_enqueue_style($plugin->slug . '-admin');
			}, 
			10, 
			1
		);

		add_action('admin_menu', function () {
			add_menu_page(
				'Verwaltung – Bettenbörse',
				'Bettenbörse',
				'manage_options',
				'bettenboerse',
				function() {
					$announcements = $this->fetch_announcements();
					$matches = $this->calculate_matches($announcements['requests'], $announcements['offers']);
					
					Admin_UI_Components::Bettenboerse_Management_Page($announcements, $matches);
				},
				'dashicons-palmtree',
				3
			);
		});
	}

	protected function fetch_announcements() {
		$requests = [
			BedRequest::withDates(new Helpers\JSONDateTime('2021-11-15 00:00:00'), new Helpers\JSONDateTime('2022-03-20 00:00:00'), 'Bernd Bettsucher'),
			BedRequest::withDates(new Helpers\JSONDateTime('2021-10-20 00:00:00'), new Helpers\JSONDateTime('2021-12-05 00:00:00'), 'Anna Bettsucherin'),
		];
		$offers = [
			BedOffer::withDates(new Helpers\JSONDateTime('2021-11-01 00:00:00'), new Helpers\JSONDateTime('2021-12-01 00:00:00'), 'Hans Hausbesitzer'),
		];

		$db = Database::instance();
		return [
			'requests' => $db->find_announcements(AnnouncementType::Request),
			'offers' => $db->find_announcements(AnnouncementType::Offer),
		];
	}

	protected function calculate_matches($requests, $offers) {
		$matches = [];
		
		foreach ($requests as $request) {
			foreach ($offers as $offer) {
				$match = AnnouncementMatch::calculateMatch($request, $offer);

				if ($match->percentage > 0.1) {
					// Match
					array_push($matches, $match);
				}
			}
		}

		return $matches;
	}
}
