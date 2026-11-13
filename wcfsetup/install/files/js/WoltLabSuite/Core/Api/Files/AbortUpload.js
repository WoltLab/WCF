/**
 * Discards a pending upload that has been started through `upload()`.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.abortUpload = abortUpload;
    /**
     * Deletes the temporary upload and the chunks received so far. Fails with a
     * validation error for unknown identifiers, which includes uploads that have
     * already been completed.
     */
    async function abortUpload(identifier) {
        const url = new URL(`${window.WSC_RPC_API_URL}core/files/upload/${identifier}`);
        try {
            await (0, Backend_1.prepareRequest)(url).delete().disableLoadingIndicator().fetchAsJson();
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)([]);
    }
});
