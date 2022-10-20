/**
 * Updates the user's timezone option on the server if the browser's
 * timezone differs.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Controller\User\Profile
 * @since 6.0
 */
define(["require", "exports", "../../../Core"], function (require, exports, Core_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.maybeSyncServerTimezone = void 0;
    function getServerTimeZone() {
        const meta = document.querySelector('meta[name="timezone"]');
        return meta?.content;
    }
    function getClientTimeZone() {
        return Intl.DateTimeFormat().resolvedOptions().timeZone;
    }
    function getSessionStorageKey() {
        return `${(0, Core_1.getStoragePrefix)()}tz-sync`;
    }
    function hasPreviouslyRequestedAnUpdate() {
        const key = getSessionStorageKey();
        try {
            const value = window.sessionStorage.getItem(key);
            if (value !== null) {
                const lastAttempt = new Date(value);
                const OneDayAgo = Date.now() - 86400;
                if (lastAttempt.getTime() >= OneDayAgo) {
                    // The time zone database on the server might be broken or
                    // otherwise outdated. Throttling the sync request to once
                    // per day should avoid dispatching many requests that are
                    // likely to fail.
                    return true;
                }
            }
        }
        catch {
            // Failing to access the session storage means that we cannot
            // reliably detect if an update was previously requested.
        }
        return true;
    }
    async function maybeSyncServerTimezone() {
        const serverTimeZone = getServerTimeZone();
        if (serverTimeZone === undefined) {
            // The server did not provide a valid time zone, possibly the
            // templates are outdated.
            console.warn("Unable to evaluate the server's time zone, the <meta> tag is missing.");
            return;
        }
        const clientTimeZone = getClientTimeZone();
        if (serverTimeZone === clientTimeZone) {
            return;
        }
        if (hasPreviouslyRequestedAnUpdate()) {
            return;
        }
        try {
            // Keeps track of the last time a time zone sync was attempted.
            window.sessionStorage.setItem(getSessionStorageKey(), Date.now.toString());
            await fetch(`${window.WSC_API_URL}index.php?user-timezone-sync/`, {
                method: "POST",
                headers: {
                    "content-type": "application/json; charset=UTF-8",
                    "x-xsrf-token": (0, Core_1.getXsrfToken)(),
                },
                body: JSON.stringify({ tz: clientTimeZone }),
            });
        }
        catch {
            // Ignore
        }
    }
    exports.maybeSyncServerTimezone = maybeSyncServerTimezone;
});
