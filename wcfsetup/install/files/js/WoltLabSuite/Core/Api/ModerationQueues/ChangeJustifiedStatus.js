/**
 * Changes the justified status of a report.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.changeJustifiedStatus = changeJustifiedStatus;
    async function changeJustifiedStatus(queueId, markAsJustified) {
        return (0, Result_1.fromInfallibleApiRequest)(() => {
            return (0, Backend_1.prepareRequest)(`${window.WSC_RPC_API_URL}core/moderation-queues/${queueId}/change-justified-status`)
                .post({
                markAsJustified,
            })
                .fetchAsJson();
        });
    }
});
