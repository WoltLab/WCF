/**
 * Sets whether the recent activities shown to the active user are filtered by the users they follow.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setUserActivityEventFilter = setUserActivityEventFilter;
    async function setUserActivityEventFilter(filteredByFollowedUsers) {
        await (0, Result_1.fromInfallibleApiRequest)(() => {
            return (0, Backend_1.prepareRequest)(`${window.WSC_RPC_API_URL}core/users/activity-events/filter`)
                .post({ filteredByFollowedUsers })
                .fetchAsJson();
        });
    }
});
