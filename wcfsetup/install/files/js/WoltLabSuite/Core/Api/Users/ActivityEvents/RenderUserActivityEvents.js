/**
 * Loads a paginated list of recent user activity events.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.renderUserActivityEvents = renderUserActivityEvents;
    async function renderUserActivityEvents(lastEventTime, lastEventID = 0, userID = 0, boxID = 0, filteredByFollowedUsers = false) {
        const url = new URL(`${window.WSC_RPC_API_URL}core/users/activity-events/render`);
        url.searchParams.set("lastEventTime", lastEventTime.toString());
        url.searchParams.set("lastEventID", lastEventID.toString());
        url.searchParams.set("userID", userID.toString());
        url.searchParams.set("boxID", boxID.toString());
        url.searchParams.set("filteredByFollowedUsers", filteredByFollowedUsers ? "1" : "0");
        let response;
        try {
            response = (await (0, Backend_1.prepareRequest)(url).get().fetchAsJson());
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)(response);
    }
});
