/**
 * Renders the notices of a page and object that are visible to the active user.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "WoltLabSuite/Core/Api/Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.renderPageNotices = renderPageNotices;
    function renderPageNotices(pageId, pageObjectId) {
        const url = new URL(`${window.WSC_RPC_API_URL}core/page-notices/render`);
        url.searchParams.set("pageID", pageId.toString());
        if (pageObjectId !== undefined) {
            url.searchParams.set("pageObjectID", pageObjectId.toString());
        }
        return (0, Result_1.fromInfallibleApiRequest)(() => {
            return (0, Backend_1.prepareRequest)(url).get().fetchAsJson();
        });
    }
});
