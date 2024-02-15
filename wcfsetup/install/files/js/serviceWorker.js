/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

self.addEventListener("push", async (event) => {
	if (!(self.Notification && self.Notification.permission === "granted")) {
		return;
	}
	if (!event.data) {
		return;
	}

	const payload = event.data.json();
	const notifications = await self.registration.getNotifications({ tag: payload.notificationID });
	// Close old notifications
	notifications
		.filter((notifications) => {
			if (!notifications.data || !notifications.data.time) {
				return false;
			}
			return notifications.data.time <= payload.time;
		})
		.forEach((notification) => {
			notification.close();
		});
	event.waitUntil(
		self.registration.showNotification(payload.title, {
			body: payload.message,
			icon: payload.icon,
			timestamp: payload.time,
			tag: payload.notificationID,
			data: {
				link: payload.link,
				time: payload.time,
			},
		}),
	);
});
