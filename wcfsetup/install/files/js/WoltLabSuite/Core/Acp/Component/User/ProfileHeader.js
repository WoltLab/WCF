/**
 * Refreshes the user profile header on the user edit page after an
 * interaction has changed the state of the user.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Api/Users/GetAdminUserProfileHeader"], function (require, exports, GetAdminUserProfileHeader_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    async function refreshHeader(header, userId) {
        const { template } = await (0, GetAdminUserProfileHeader_1.getAdminUserProfileHeader)(userId);
        const container = document.createElement("template");
        container.innerHTML = template;
        const newHeader = container.content.querySelector(".userProfileHeader");
        if (!newHeader) {
            return;
        }
        // The buttons contain the interaction context menu, which refreshes itself.
        // Keeping them preserves the initialized dropdown and its event listeners.
        const buttons = header.querySelector(".userProfileHeader__buttons");
        const newButtons = newHeader.querySelector(".userProfileHeader__buttons");
        if (buttons && newButtons) {
            newButtons.replaceWith(buttons);
        }
        header.replaceChildren(...newHeader.childNodes);
    }
    function setup(header) {
        const userId = parseInt(header.dataset.objectId);
        header.addEventListener("interaction:invalidate", (event) => {
            if (event.detail._reloadPage === "true") {
                return;
            }
            void refreshHeader(header, userId);
        });
    }
});
