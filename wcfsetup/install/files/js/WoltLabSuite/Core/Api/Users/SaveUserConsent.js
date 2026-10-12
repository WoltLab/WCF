/**
 * Saves the consent of the active user to always display external media.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.saveUserConsent = saveUserConsent;
    async function saveUserConsent() {
        try {
            await (0, Backend_1.prepareRequest)(`${window.WSC_RPC_API_URL}core/users/user-consent`)
                .post()
                .disableLoadingIndicator()
                .fetchAsJson();
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)([]);
    }
});
