/**
 * Shows the ignore dialogs when editing users on the page listing ignored users.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright  2001-2026 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Component/Snackbar", "WoltLabSuite/Core/Helper/PromiseMutex"], function (require, exports, Dialog_1, Snackbar_1, PromiseMutex_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.UiUserIgnoreList = void 0;
    class UiUserIgnoreList {
        constructor() {
            document.querySelectorAll(".jsEditIgnoreButton").forEach((button) => {
                button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => this.openDialog(button)));
            });
        }
        async openDialog(button) {
            const { ok, result } = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(button.dataset.endpoint);
            if (!ok) {
                return;
            }
            (0, Snackbar_1.showDefaultSuccessSnackbar)();
            if (!result.type) {
                button.closest(".jsIgnoredUser").remove();
            }
        }
    }
    exports.UiUserIgnoreList = UiUserIgnoreList;
    exports.default = UiUserIgnoreList;
});
