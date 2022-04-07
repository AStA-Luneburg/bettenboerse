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
	htmlEntities(str = 'TEST') {
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	},
	TYPE_MAP: {
		['request']: 'Ersuch',
		['offer']: 'Angebot',
	},
	ICON_TYPE_MAP: {
		['request']: '🛏 Ersuch',
		['offer']: '🏠 Angebot',
	},
	CLASS_TYPE_MAP: {
		['request']: 'text-blue-900 bg-blue-300 border-blue-400',
		['offer']: 'text-pink-900 bg-pink-300 border-pink-400',
	},

	transformToDataSet(announcements) {
		return new vis.DataSet(
			announcements.requests
				.concat(announcements.offers)
				.map((announcement) => ({
					id: announcement.id,
					type: 'range',
					content: `<strong>${
						BETTENBOERSE.ICON_TYPE_MAP[announcement.type]
					}</strong> – ${BETTENBOERSE.htmlEntities(
						announcement.name
					)}`,
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
	},
	createAnnouncementTableHTML(announcement) {
		return `
			<table class="text-left" cellspacing="10">
				<tbody>
					<tr>
						<th scope="row">Typ:</th>
						<td>
							${BETTENBOERSE.ICON_TYPE_MAP[announcement.type]}
						</td>
					</tr>
					<tr>
						<th scope="row">Name:</th>
						<td>${BETTENBOERSE.htmlEntities(announcement.name)}</td>
					</tr>
					<tr>
						<th scope="row">Vom:</th>
						<td>
							${new Date(announcement.from).toLocaleDateString()}
						</td>
					</tr>
					<tr>
						<th scope="row">Bis:</th>
						<td>
							${new Date(announcement.until).toLocaleDateString()}
						</td>
					</tr>
					<tr>
						<th scope="row">E-Mail:</th>
						<td>
							${BETTENBOERSE.htmlEntities(announcement.email)}
						</td>
					</tr>
					<tr>
						<th scope="row">Telefon:</th>
						<td>
							${BETTENBOERSE.htmlEntities(announcement.phone)}
						</td>
					</tr>
					<tr>
						<th scope="row">Geschlecht:</th>
						<td>
							${BETTENBOERSE.htmlEntities(announcement.gender)}
						</td>
					</tr>
					<tr>
						<th scope="row">Bett-Art:</th>
						<td>
							${BETTENBOERSE.htmlEntities(announcement.bedType)}
						</td>
					</tr>
					<tr>
						<th scope="row">Bed-Anzahl:</th>
						<td>
							${BETTENBOERSE.htmlEntities(announcement.bedType)}
						</td>
					</tr>
					<tr>
						<th scope="row" class="pr-5">
							Ortsinformation:
						</th>
						<td>
							${BETTENBOERSE.htmlEntities(announcement.bedType)}
						</td>
					</tr>
					<tr>
						<th scope="row">Wünsche:</th>
						<td>
							${BETTENBOERSE.htmlEntities(announcement.bedType)}
						</td>
					</tr>
				</tbody>
			</table>
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
			dialogClass: 'wp-dialog w-64',
			autoOpen: true,
			draggable: false,
			width: '500px',
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
					text: 'Löschen',
					click: () => {
						if (
							!confirm(
								'Bist du sicher, dass du diesen Eintrag löschen möchtest?'
							)
						) {
							return;
						}

						BETTENBOERSE.deleteAnnouncement(announcement);
						jQuery('#announcement-dialog').dialog('close');
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
};

jQuery(document).ready(function () {
	// DOM element where the Timeline will be attached
	const container = document.getElementById('matches-timeline');
	const announcements = JSON.parse(
		container.getAttribute('data-announcements')
	);

	BETTENBOERSE.renderAnnouncements(container, announcements);

	// Open the popup when any .announcement-popup-button is clicked
	jQuery('.announcement-popup-button').click(function () {
		const announcement = JSON.parse(this.getAttribute('data-announcement'));
		BETTENBOERSE.showAnnouncementPopup(announcement);
	});
});
