/**
 * Renders the preview of the active user's signature.
 *
 * @author  Marcel Werk
 * @copyright  2001-2026 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "WoltLabSuite/Core/Api/Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.renderSignaturePreview = renderSignaturePreview;
    async function renderSignaturePreview(message) {
        const url = new URL(`${window.WSC_RPC_API_URL}core/users/signature/preview`);
        let response;
        try {
            response = (await (0, Backend_1.prepareRequest)(url).post({ message }).fetchAsJson());
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)(response);
    }
});
