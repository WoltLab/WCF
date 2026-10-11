/**
 * Exports the email addresses of one or more users and offers the resulting
 * file for download.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Component/Dialog"], function (require, exports, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setupBulk = setupBulk;
    async function exportEmailAddresses(endpoint) {
        const { ok, result } = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(endpoint);
        if (!ok) {
            return false;
        }
        const url = window.URL.createObjectURL(new Blob([result.content], { type: `${result.mimeType};charset=utf-8` }));
        const link = document.createElement("a");
        link.href = url;
        link.download = result.filename;
        link.click();
        // Revoking the URL synchronously can abort the download in some browsers.
        window.setTimeout(() => window.URL.revokeObjectURL(url), 0);
        return true;
    }
    function setupBulk(identifier, container) {
        container.addEventListener("bulk-interaction", (event) => {
            if (event.detail.bulkInteraction !== identifier) {
                return;
            }
            void exportEmailAddresses(event.detail.endpoint).then((ok) => {
                if (!ok) {
                    return;
                }
                container.dispatchEvent(new CustomEvent("interaction:reset-selection"));
            });
        });
    }
});
