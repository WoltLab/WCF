<?php

@\header('Service-Worker-Allowed: /');
@\header('Content-Type: text/javascript; charset=utf-8');
?>/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since		6.1
 */

self.addEventListener("install", () => {
	// There is no fetch handler, so a waiting worker has nothing to hand over.
	self.skipWaiting();
});

self.addEventListener("activate", (event) => {
	// Clients opened before the registration have no `controller`, which
	// silently drops the last-read-time messages sent by the page.
	event.waitUntil(self.clients.claim());
});

self.addEventListener("push", (event) => {
	if (!(self.Notification && self.Notification.permission === "granted")) {
		return;
	}
	if (!event.data) {
		return;
	}

	const payload = event.data.json();

	getTimeOfLastReadNotification().then((notificationLastReadTime) => {
		if (notificationLastReadTime && payload.time <= notificationLastReadTime) {
			return;
		}

		event.waitUntil(
			removeOldNotifications(payload.notificationID, payload.time)
				.then(() =>
					self.registration.showNotification(payload.title, {
						body: payload.message,
						icon: payload.icon,
						timestamp: payload.time * 1000,
						tag: payload.notificationID,
						data: {
							url: payload.url,
							time: payload.time,
						},
					}),
				)
				.then(() => {
					sendToClients(payload);
				}),
		);
	});
});

self.addEventListener("notificationclick", (event) => {
	event.notification.close();

	event.waitUntil(self.clients.openWindow(event.notification.data.url));
});

self.addEventListener("message", (event) => {
	if (event.data && event.data.type === "UPDATE_NOTIFICATION_LAST_READ_TIME") {
		updateNotificationLastReadTime(event.data.timestamp);
	}
});

async function sendToClients(payload) {
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

/**
 * IndexedDB functions to store the last notification timestamp.
 */
function openDatabase() {
	return new Promise((resolve, reject) => {
		const request = indexedDB.open("WOLTLAB_SUITE_CORE", 1);

		request.onupgradeneeded = (event) => {
			const db = event.target.result;

			if (!db.objectStoreNames.contains("notifications")) {
				db.createObjectStore("notifications");
			}
		};

		request.onsuccess = (event) => {
			resolve(event.target.result);
		};

		request.onerror = (event) => {
			reject(new Error(`Database error: ${event.target.errorCode}`));
		};
	});
}

function updateNotificationLastReadTime(timestamp) {
	if (!timestamp || timestamp <= 0) {
		return;
	}

	openDatabase()
		.then((db) => {
			const tx = db.transaction("notifications", "readwrite");
			const store = tx.objectStore("notifications");
			const getRequest = store.get("lastNotification");

			getRequest.onsuccess = () => {
				const storedTimestamp = getRequest.result;

				// Check if the new timestamp is greater than the stored timestamp
				if (storedTimestamp === undefined || timestamp > storedTimestamp) {
					store.put(timestamp, "lastNotification");
				}
			};

			getRequest.onerror = () => {
				console.error("Failed to retrieve timestamp: %s", getRequest.error);
			};

			tx.onerror = () => {
				console.error("Transaction error: %s", tx.error);
			};
		})
		.catch((err) => console.error("Failed to open database: %s", err));
}

function getTimeOfLastReadNotification() {
	return openDatabase().then((db) => {
		return new Promise((resolve, reject) => {
			const tx = db.transaction("notifications", "readonly");
			const store = tx.objectStore("notifications");
			const request = store.get("lastNotification");

			request.onsuccess = () => {
				resolve(request.result);
			};
			request.onerror = () => {
				reject(new Error("Failed to retrieve timestamp"));
			};
		});
	});
}
