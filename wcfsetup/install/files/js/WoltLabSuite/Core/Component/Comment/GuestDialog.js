/**
 * Handles the guest dialog in the comment list.
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Component/Comment/GuestDialog
 * @since 6.0
 */
define(["require", "exports", "tslib", "../../Controller/Captcha", "../../Language", "../Dialog"], function (require, exports, tslib_1, Captcha_1, Language_1, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.showGuestDialog = void 0;
    Captcha_1 = tslib_1.__importDefault(Captcha_1);
    function showGuestDialog(template) {
        const captchaId = "commentAdd";
        const dialog = (0, Dialog_1.dialogFactory)().fromHtml(template).asPrompt();
        dialog.show((0, Language_1.getPhrase)("wcf.comment.guestDialog.title"));
        const usernameInput = dialog.content.querySelector("input[name=username]");
        dialog.incomplete = usernameInput.value.trim() === "";
        usernameInput.addEventListener("input", () => {
            dialog.incomplete = usernameInput.value.trim() === "";
        });
        dialog.addEventListener("afterClose", () => {
            if (Captcha_1.default.has(captchaId)) {
                Captcha_1.default.delete(captchaId);
            }
        });
        return new Promise((resolve) => {
            dialog.addEventListener("primary", () => {
                const parameters = {
                    data: {
                        username: usernameInput.value,
                    },
                };
                if (Captcha_1.default.has(captchaId)) {
                    const data = Captcha_1.default.getData(captchaId);
                    Captcha_1.default.delete(captchaId);
                    if (data instanceof Promise) {
                        void data.then((data) => {
                            resolve({
                                ...parameters,
                                ...data,
                            });
                        });
                    }
                    else {
                        resolve({
                            ...parameters,
                            ...data,
                        });
                    }
                }
                else {
                    resolve(parameters);
                }
            });
            dialog.addEventListener("cancel", () => {
                resolve(undefined);
            });
        });
    }
    exports.showGuestDialog = showGuestDialog;
});
