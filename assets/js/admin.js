// [
// 	{ id: 1, content: 'item 1', start: '2013-04-20' },
// 	{ id: 2, content: 'item 2', start: '2013-04-14' },
// 	{ id: 3, content: 'item 3', start: '2013-04-18' },
// 	{
// 		id: 4,
// 		content: 'item 4',
// 		start: '2013-04-16',
// 		end: '2013-04-19',
// 	},
// 	{ id: 5, content: 'item 5', start: '2013-04-25' },
// 	{ id: 6, content: 'item 6', start: '2013-04-27' },
// ];

const BETTENBOERSE = {
	htmlEntities(str = '–') {
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	},
	ICON_MAP: {
		['request']: '🛏',
		['offer']: '🏠',
	},
	ICON_TYPE_MAP: {
		['request']: '🛏 Ersuch',
		['offer']: '🏠 Angebot',
	},
	CLASS_TYPE_MAP: {
		['request']:
			'text-blue-900 bg-blue-300 border-blue-400 request cursor-pointer',
		['offer']:
			'text-pink-900 bg-pink-300 border-pink-400 offer cursor-pointer',
	},

	transformToDataSet(announcements) {
		return new vis.DataSet(
			announcements.requests
				.concat(announcements.offers)
				.map((announcement) => ({
					id: announcement.id,
					type: 'range',
					content: `<strong>${
						BETTENBOERSE.ICON_MAP[announcement.type]
					}&nbsp;${BETTENBOERSE.htmlEntities(
						announcement.name
					)}</strong>`,
					start: announcement.from,
					end: announcement.until,
					className: BETTENBOERSE.CLASS_TYPE_MAP[announcement.type],
					group: announcement.type === 'offer' ? 1 : 2,
				}))
		);
	},
	renderAnnouncements(container, announcements) {
		// Create a DataSet (allows two way data-binding)
		const items = BETTENBOERSE.transformToDataSet(announcements);

		// Configuration for the Timeline
		const options = {};

		// Create a Timeline
		const timeline = new vis.Timeline(container, items, options);
		timeline.setGroups([
			{
				id: 1,
				content: 'Angebot',
			},
			{
				id: 2,
				content: 'Ersuch',
			},
		]);

		timeline.on('select', ({ items }) => {
			if (items.length > 0) {
				const id = items[0];
				const announcement =
					announcements.requests.find(
						(announcement) => announcement.id === id
					) ||
					announcements.offers.find(
						(announcement) => announcement.id === id
					);

				console.log(id, announcement);
				if (announcement) {
					BETTENBOERSE.showAnnouncementPopup(announcement);
				}
			}
		});

		return timeline;
	},
	createAnnouncementTableHTML(announcement) {
		return `
			<div class="flex flex-col w-full gap-5">
				<div class="grid grid-cols-2 gap-5 mb-2">
					<div class="col-span-1">
						<h2 class="text-lg font-medium">
							${BETTENBOERSE.htmlEntities(announcement.name)}
						</h2>
						<span class="text-gray-500 font-medium">Name</span>
					</div>
					<div class="col-span-1 grid grid-cols-2 gap-5">
						<div class="col-span-1">
							<h2 class="text-lg font-medium">
								${new Date(announcement.from).toLocaleDateString()}
							</h2>
							<span class="text-gray-500 font-medium">Von</span>
						</div>
						<div class="col-span-1">
							<h2 class="text-lg font-medium">
								${new Date(announcement.until).toLocaleDateString()}
							</h2>
							<span class="text-gray-500 font-medium">Bis</span>
						</div>
					</div>
				</div>
				<div class="grid grid-cols-2 gap-5">
					<div class="col-span-1">
						<h2 class="text-base font-medium">
							${BETTENBOERSE.htmlEntities(announcement.email)}
						</h2>
						<span class="text-gray-500 font-medium">E-Mail</span>
					</div>
					<div class="col-span-1">
						<h2 class="text-base font-medium">
							${BETTENBOERSE.htmlEntities(announcement.phone)}
						</h2>
						<span class="text-gray-500 font-medium">Telefon</span>
					</div>
				</div>
				<div class="grid grid-cols-2 gap-5">
					<div class="col-span-1">
						<h2 class="text-base font-medium">
							${BETTENBOERSE.htmlEntities(announcement.gender || '–')}
						</h2>
						<span class="text-gray-500 font-medium">Geschlecht</span>
					</div>
				</div>
				<div class="grid grid-cols-2 gap-5">
					<div class="col-span-1">
						<h2 class="text-base font-medium">
							${BETTENBOERSE.htmlEntities(announcement.bedType)}
						</h2>
						<span class="text-gray-500 font-medium">Bett-Typ</span>
					</div>
					<div class="col-span-1">
						<h2 class="text-base font-medium">
							${parseInt(announcement.bedCount)} 
							
							${'🛏'.repeat(Math.min(10, parseInt(announcement.bedCount)))} 
						</h2>
						<span class="text-gray-500 font-medium">Anzahl an Betten</span>
					</div>
				</div>

				<div class="grid grid-cols-2 gap-5 justify-between">
					<div class="col-span-1">
						<h2 class="text-base font-medium truncate">
							${BETTENBOERSE.htmlEntities(announcement.locationHint)}
						</h2>
						<span class="text-gray-500 font-medium">Ortsinformationen</span>
					</div>
					<div class="col-span-1">
						<h2 class="text-base font-medium truncate">
							${BETTENBOERSE.htmlEntities(announcement.wishes)}
						</h2>
						<span class="text-gray-500 font-medium">Wünsche</span>
					</div>
				</div>
			</div>
		`;
	},
	showAnnouncementPopup(announcement, match = null) {
		jQuery('#announcement-dialog').html(
			BETTENBOERSE.createAnnouncementTableHTML(announcement)
		);

		jQuery('#announcement-dialog').dialog({
			title: `${BETTENBOERSE.ICON_TYPE_MAP[announcement.type]} – ${
				announcement.name
			}`,
			dialogClass:
				'wp-dialog max-w-lg flex flex-col min-h-full sm:min-h-fit truncate',
			autoOpen: true,
			draggable: false,
			width: '100%',
			height: '',
			modal: true,
			resizable: false,
			closeOnEscape: true,
			position: {
				my: 'center',
				at: 'center',
				of: window,
			},
			buttons: [
				{
					text: 'E-Mail schreiben',
					click: () => {
						window.location.href = 'mailto:' + announcement.email;
					},
					class: 'close',
				},
				{
					text: 'Löschen',
					click: async (e) => {
						if (
							!confirm(
								'Bist du sicher, dass du diesen Eintrag löschen möchtest?'
							)
						) {
							return;
						}

						e.target.disabled = true;
						e.target.textContent = 'Wird gelöscht...';

						await BETTENBOERSE.deleteAnnouncement(announcement);
						window.location.reload();
						// jQuery('#announcement-dialog').dialog('close');
					},
					class: 'bg-red-500 border border-red-600 text-white hover:bg-red-700 hover:text-white active:bg-red-700 focus:bg-red-700 focus:text-white',
				},
				{
					text: 'Schließen',
					click: () => {
						jQuery('#announcement-dialog').dialog('close');
					},
					class: 'close',
				},
			],
			open: function () {
				jQuery('button.close').focus();

				// close dialog by clicking the overlay behind it
				jQuery('.ui-widget-overlay').bind('click', function () {
					jQuery('#announcement-dialog').dialog('close');
				});
			},
			create: function () {
				// style fix for WordPress admin
				jQuery('.ui-dialog-titlebar-close').addClass('ui-button');
			},
		});
	},
	deleteAnnouncement(announcement) {
		return new Promise((resolve, reject) => {
			jQuery.post(
				bettenboerse_environment.ajaxurl,
				{
					action: 'bettenboerse_delete_announcement',
					id: announcement.id,
				},
				function (response) {
					console.log('The server responded: ', response);
					resolve();
				}
			);
		});
	},
};

jQuery(document).ready(function () {
	// DOM element where the Timeline will be attached
	const container = document.getElementById('matches-timeline');
	const announcements = JSON.parse(
		container.getAttribute('data-announcements')
	);

	const timeline = BETTENBOERSE.renderAnnouncements(container, announcements);

	// Open the popup when any .announcement-popup-button is clicked
	jQuery('.announcement-select-button').click(function () {
		const announcements = JSON.parse(
			this.getAttribute('data-announcements')
		);
		// BETTENBOERSE.showAnnouncementPopup(announcement);
		timeline.setSelection(announcements);
		timeline.focus(announcements);
	});
});
