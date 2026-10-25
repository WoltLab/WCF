/**
 * Handles the user ignore buttons.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 * @deprecated 6.3 Use the `ignore` interaction of `wcf\system\interaction\user\UserProfileInteractions` instead.
 */
define(["require", "exports", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Helper/Selector", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Component/Dialog", "../Snackbar"], function (require, exports, PromiseMutex_1, Selector_1, Language_1, Dialog_1, Snackbar_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    async function toggleIgnore(button) {
        const { ok, result } = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(button.dataset.ignoreUser);
        if (ok) {
            if (result.type) {
                button.dataset.ignored = "1";
                if (button.dataset.type === "button") {
                    button.textContent = (0, Language_1.getPhrase)("wcf.user.button.unignore");
                }
                else {
                    button.dataset.tooltip = (0, Language_1.getPhrase)("wcf.user.button.unignore");
                    button.querySelector("fa-icon")?.setIcon("eye", true);
                }
            }
            else {
                button.dataset.ignored = "0";
                if (button.dataset.type === "button") {
                    button.textContent = (0, Language_1.getPhrase)("wcf.user.button.ignore");
                }
                else {
                    button.dataset.tooltip = (0, Language_1.getPhrase)("wcf.user.button.ignore");
                    button.querySelector("fa-icon")?.setIcon("eye-slash", true);
                }
            }
            (0, Snackbar_1.showDefaultSuccessSnackbar)();
        }
    }
    function setup() {
        (0, Selector_1.wheneverFirstSeen)("[data-ignore-user]", (button) => {
            button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => toggleIgnore(button)));
        });
    }
});
