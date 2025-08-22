/**
 * Change the style of the current user.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.changeStyle = changeStyle;
    function changeStyle(styleId) {
        return (0, Result_1.fromInfallibleApiRequest)(() => {
            return (0, Backend_1.prepareRequest)(`${window.WSC_RPC_API_URL}core/styles/${styleId}/change`).post().fetchAsJson();
        });
    }
});
