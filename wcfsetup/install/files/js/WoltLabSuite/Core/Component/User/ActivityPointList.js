/**
 * Shows the activity point list for users.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "WoltLabSuite/Core/Api/Users/GetUserActivityPointList", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Helper/Selector", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Language"], function (require, exports, GetUserActivityPointList_1, PromiseMutex_1, Selector_1, Dialog_1, Language_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    async function showDialog(userId) {
        const { template } = await (0, GetUserActivityPointList_1.getUserActivityPointList)(userId);
        const dialog = (0, Dialog_1.dialogFactory)().fromHtml(template).withoutControls();
        dialog.show((0, Language_1.getPhrase)("wcf.user.activityPoint"));
    }
    function setup() {
        (0, Selector_1.wheneverFirstSeen)(".activityPointsDisplay", (button) => {
            button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)((event) => {
                event.preventDefault();
                return showDialog(parseInt(button.dataset.userId));
            }));
        });
    }
});
