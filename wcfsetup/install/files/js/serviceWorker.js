/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

self.addEventListener("push", function (event) {
	if (!(self.Notification && self.Notification.permission === "granted")) {
		return;
	}

	if (event.data);
	{
		const payload = event.data.json();
		if (!payload || !payload.title) {
			return;
		}
		event.waitUntil(self.registration.showNotification(payload.title, payload));
	}
});

