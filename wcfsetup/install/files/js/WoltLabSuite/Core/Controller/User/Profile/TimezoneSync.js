/**
 * Updates the user's timezone option on the server if the browser's
 * timezone differs.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Controller\User\Profile
 */
define(["require", "exports", "../../../Core"], function (require, exports, Core_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.maybeSyncServerTimezone = void 0;
    function getServerTimezone() {
        const meta = document.querySelector('meta[name="timezone"]');
        return meta?.content;
    }
    function getClientTimezone() {
        return Intl.DateTimeFormat().resolvedOptions().timeZone;
    }
    async function maybeSyncServerTimezone() {
        if (getServerTimezone() !== getClientTimezone()) {
            try {
                await fetch(`${window.WSC_API_URL}index.php?user-timezone-sync/`, {
                    method: "POST",
                    headers: {
                        "content-type": "application/json; charset=UTF-8",
                        "x-xsrf-token": (0, Core_1.getXsrfToken)(),
                    },
                    body: JSON.stringify({ tz: getClientTimezone() }),
                });
            }
            catch {
                // Ignore
            }
        }
    }
    exports.maybeSyncServerTimezone = maybeSyncServerTimezone;
});
