/**
 * Handles the buttons that allow the user to clear the cache.
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Ajax/Backend", "WoltLabSuite/Core/Component/Confirmation", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Ui/Notification"], function (require, exports, tslib_1, Backend_1, Confirmation_1, Language_1, UiNotification) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    UiNotification = tslib_1.__importStar(UiNotification);
    function initButton(button) {
        button.addEventListener("click", () => {
            void clearCache(button.dataset.endpoint);
        });
    }
    async function clearCache(endpoint) {
        const result = await (0, Confirmation_1.confirmationFactory)().custom((0, Language_1.getPhrase)("wcf.acp.cache.clear.sure")).withoutMessage();
        if (result) {
            await (0, Backend_1.prepareRequest)(endpoint).post().fetchAsResponse();
            UiNotification.show();
        }
    }
    function setup() {
        document.querySelectorAll(".jsCacheClearButton").forEach((button) => {
            initButton(button);
        });
    }
});
