/**
 * Requests to reset the removal quotes.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.resetRemovalQuotes = resetRemovalQuotes;
    async function resetRemovalQuotes() {
        const url = new URL(window.WSC_RPC_API_URL + "core/messages/reset-removal-quotes");
        return (0, Result_1.fromInfallibleApiRequest)(() => {
            return (0, Backend_1.prepareRequest)(url).post().fetchAsJson();
        });
    }
});
