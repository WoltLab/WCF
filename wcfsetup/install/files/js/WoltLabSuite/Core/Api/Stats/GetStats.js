/**
 * Fetches the daily statistics of the requested types, grouped by the requested time unit.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "WoltLabSuite/Core/Api/Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getStats = getStats;
    async function getStats(startDate, endDate, objectTypeIDs, dateGrouping, value) {
        const url = new URL(`${window.WSC_RPC_API_URL}core/stats`);
        url.searchParams.set("startDate", startDate);
        url.searchParams.set("endDate", endDate);
        for (const objectTypeID of objectTypeIDs) {
            url.searchParams.append("objectTypeIDs[]", objectTypeID.toString());
        }
        url.searchParams.set("dateGrouping", dateGrouping);
        url.searchParams.set("value", value);
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
