<?php

namespace AStA\Bettenboerse;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('\WP_List_Table')) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Match_List_Table extends \WP_List_Table
{
	function get_columns()
	{
		return [
			'request'         => '🛏 Ersuch',
			'matches'           => '🏠 Passende Angebote',
			// 'actions'         => 'Aktionen',
		];
	}

	function column_default($item, $column_name)
	{
		switch ($column_name) {
			case 'matches':
				return self::Matches_Column($item->matches);

			case 'request':
				return self::Request_Column($item->request, $item->matches);

			case 'matchPercentage':
				$color = Helpers\get_match_color($match->percentage);
				$bg_color = Helpers\get_match_bg_color($match->percentage);

				return "<span class=\"px-1 rounded text-lg\" style=\"background: $bg_color; color: $color;\">" . round(floatval($match->percentage) * 100) . ' %' . '</span>';

			// case 'matchDuration':
			// 	return esc_html(Helpers\seconds_to_days($match->duration));

			// case 'actions':
			// 	return self::Match_Row_Actions($match);

			// default:
			// 	return print_r($match, true); //Show the whole array for troubleshooting purposes
		}
	}

	function prepare_items()
	{
		$columns = $this->get_columns();
		$hidden = [];
		$sortable = ['matchPercentage'];

		$this->_column_headers = [$columns, $hidden, $sortable];
	}

	static function Matches_Column($matches) {
		ob_start();

		?> 
			<div class="flex flex-col items-start gap-2"> 
			<?php
				if (count($matches) === 0) {
					?>
					<span class="text-sm text-gray-400">Keine Übereinstimmungen gefunden</span>
					<?php
				}

				foreach ($matches as $match) {
					$color = Helpers\get_match_color($match->percentage);
					$bg_color = Helpers\get_match_bg_color($match->percentage);
					$bg_color_alternate = Helpers\get_match_bg_color_alternate($match->percentage);

					?>
					<div class="flex w-full bg-white rounded shadow justify-start" style="background: <?php echo $bg_color_alternate; ?>;">
						<button 
							class="px-2 flex-1 bg-transparent text-pink-700 p-0 border-0 underline announcement-select-button text-left" 
							data-announcements="<?php echo \esc_attr(json_encode([$match->offer->id, $match->request->id])); ?>"
						>
							<?php echo esc_html($match->offer->name); ?>
						</button>

						<div class="w-16 px-1 rounded text-lg text-center" style="background: <?php echo $bg_color; ?>; color: <?php echo $color; ?>;">
							<?php echo round(floatval($match->percentage) * 100) . ' %'; ?>
						</div>
					</div>

					<?php
				}
			?>
			</div>
		<?php
		return ob_get_clean();
	}

	static function Request_Column(BedRequest $request, $matches) {
		$offer_ids = array_map(function($match) {
			return $match->offer->id;
		}, $matches);

		ob_start();
		?>
			<button 
				class="bg-transparent text-blue-800 p-0 border-0 underline announcement-select-button" 
				data-announcements="<?php echo \esc_attr(json_encode(array_merge($offer_ids, [$request->id]))); ?>"
			>
				<?php echo esc_html($request->name); ?>
			</button>
		<?php
		return ob_get_clean();
	}

	static function Match_Row_Actions(AnnouncementMatch $match)
	{
		?>
		<div class="w-full flex justify-start gap-3">
			<button class="button button-primary">Details</button>
			<button class="button button-secondary">Entfernen</button>
		</div>
		<?php
	}
}





class Admin_UI_Components
{
	/**
	 * Render management page
	 */
	static function Bettenboerse_Management_Page($announcements, $matches)
	{
		?>
			<div class="wrap">
				<h1>Bettenbörse</h1>
				<p class="mb-8">Hier können Sie die Bettenbörse verwalten.</p>


				<details open class="mb-10"> 
					<summary class="text-xl text-gray-600 cursor-pointer mb-2">Visualisierung</summary>
					
					<noscript>Um die Visualisierung zu verwenden, muss JavaScript aktiviert sein.</noscript>
					<div id="matches-timeline" data-announcements="<?php echo \esc_attr(json_encode($announcements)); ?>"></div>
				</details>

				<details open class="mb-20">
					<summary class="text-xl text-gray-600 cursor-pointer -mb-8">Ersuche 🛏</summary>
					
					<?php Admin_UI_Components::Announcement_Match_Table($announcements['requests'], $matches); ?>
				</details>
			</div>

			<article id="announcement-dialog" class="max-w-xl">
				
			</article>
		<?php

		if (defined('WP_DEBUG')) {
			?>
				<details>
					<summary class="text-gray-500">Announcements</summary>
					<pre><?php var_dump($announcements); ?></pre>
				</details>

				<details>
					<summary class="text-gray-500">Matches</summary>
					<pre><?php var_dump($matches); ?></pre>
				</details>
			<?php	
		}
	}

	public static function Announcement_Match_Table($requests, $matches) {
		$items = [];
		$items = array_map(function($request) use ($matches) {
			$item = new \stdClass();
			$item->request = $request;
			$item->matches = array_filter($matches, function($match) use ($request) {
				return $match->request->id === $request->id;
			});

			return $item;
		}, $requests);


		$table = new Match_List_Table();
		$table->items = $items;
		$table->prepare_items();
		$table->display();
		?>

		<style>
			.wp-list-table tr:not(.inline-edit-row):not(.no-items) td:not(.column-primary)::before {
				font-weight: bold !important;
			}

			.column-matchPercentage {
				width: 9rem;
			}

			.column-matchDuration {
				width: 7rem;
			}

			.column-actions {
				width: 13rem;
			}
		</style>
		<?php
	}

	static function Request_List($requests, $matches) {
		ob_start();
		?>
			<div class="w-full flex flex-col gap-10">
				<?php
					foreach ($requests as $request) {
						?>
							<article class="w-full bg-gray-50 border border-gray-200 grid px-4 py-2 rounded-lg shadow">
								<div class="p-4 rounded flex flex-col">
									<h2 class="text-xl text-gray-900"><?php echo esc_html($request->name); ?></h2>
									<h2 class="text-sm text-gray-600">Name</h2>
								</div>
							</article>
						<?php
					}
				?>
			</div>
		<?php
		return ob_get_clean();
	}
}