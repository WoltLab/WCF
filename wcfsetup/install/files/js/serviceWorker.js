/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

self.addEventListener("push", (event) => {
	if (!(Notification && Notification.permission === "granted")) {
		return;
	}

	//TODO send notification
});
