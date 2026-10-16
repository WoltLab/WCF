/**
 * Shows the trophies of users.
 *
 * @author Joshua Ruesweg, Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Helper/Selector", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Language"], function (require, exports, PromiseMutex_1, Selector_1, Dialog_1, Language_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    async function showDialog(userId) {
        await (0, Dialog_1.dialogFactory)()
            .usingListView()
            .fromPreset((0, Language_1.getPhrase)("wcf.user.trophy.trophies"), "wcf\\system\\listView\\user\\UserTrophyListView", new Map([["userID", userId.toString()]]), undefined, "time", "DESC");
    }
    function setup() {
        (0, Selector_1.wheneverFirstSeen)(".userTrophyOverlayList", (button) => {
            button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)((event) => {
                event.preventDefault();
                return showDialog(parseInt(button.dataset.userId));
            }));
        });
    }
});
