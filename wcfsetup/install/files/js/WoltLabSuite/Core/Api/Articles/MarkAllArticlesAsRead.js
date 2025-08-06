/**
 * Mark all articles as read
 *
 * @author Olaf Braun
 * @copyright  2001-2025 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "WoltLabSuite/Core/Api/Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.markAllArticlesAsRead = markAllArticlesAsRead;
    async function markAllArticlesAsRead() {
        try {
            await (0, Backend_1.prepareRequest)(new URL(`${window.WSC_RPC_API_URL}core/articles/mark-all-as-read`))
                .post()
                .fetchAsJson();
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)([]);
    }
});
