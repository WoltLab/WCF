<?php

@\header('Service-Worker-Allowed: /');
@\header('Content-Type: text/javascript; charset=utf-8');
?>
/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

self.addEventListener("push", (event) => {
	if (!(self.Notification && self.Notification.permission === "granted")) {
		return;
	}
	if (!event.data) {
		return;
	}

	const payload = event.data.json();

	event.waitUntil(
		removeOldNotifications(payload.notificationID, payload.time).then(() =>
			self.registration.showNotification(payload.title, {
				body: payload.message,
				icon: payload.icon,
				timestamp: payload.time,
				tag: payload.notificationID,
				data: {
					url: payload.url,
					time: payload.time,
				},
			}),
		),
	);
});

self.addEventListener("notificationclick", (event) => {
	event.notification.close();

	event.waitUntil(self.clients.openWindow(event.notification.data.url));
});

async function sendToClients(payload){
	const allClients = await self.clients.matchAll({
		includeUncontrolled: true,
		type: "window",
	});
	for (const client of allClients) {
		if (!client.url.startsWith(self.origin)) {
			continue;
		}
		client.postMessage(payload);
	}
}

async function removeOldNotifications(notificationID, time) {
	const notifications = await self.registration.getNotifications({ tag: notificationID });
	// Close old notifications
	notifications
		.filter((notifications) => {
			if (!notifications.data || !notifications.data.time) {
				return false;
			}
			return notifications.data.time <= time;
		})
		.forEach((notification) => {
			notification.close();
		});
}
