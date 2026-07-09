/**
 * Helper module to expose a fluent API for custom
 * prompts created through `notificationFactory()`.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports", "../Dialog", "WoltLabSuite/Core/Language"], function (require, exports, Dialog_1, Language_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.ConfirmationCustom = void 0;
    class ConfirmationCustom {
        #question;
        constructor(question) {
            this.#question = question;
        }
        async message(message) {
            if (message.trim() === "") {
                throw new Error("An empty message for the delete confirmation was provided. Please use `defaultMessage()` if you do not want to provide a custom message.");
            }
            const dialog = (0, Dialog_1.dialogFactory)()
                .fromHtml(`<p>${message}</p>`)
                .asConfirmation({
                primary: (0, Language_1.getPhrase)("wcf.dialog.button.primary.confirm"),
            });
            dialog.show(this.#question);
            return new Promise((resolve) => {
                dialog.addEventListener("primary", () => resolve(true));
                dialog.addEventListener("cancel", () => resolve(false));
            });
        }
        async withFormElements(callback) {
            const dialog = (0, Dialog_1.dialogFactory)()
                .withoutContent()
                .asConfirmation({
                primary: (0, Language_1.getPhrase)("wcf.dialog.button.primary.confirm"),
            });
            callback(dialog);
            dialog.show(this.#question);
            return new Promise((resolve) => {
                dialog.addEventListener("primary", () => {
                    resolve({
                        result: true,
                        dialog,
                    });
                });
                dialog.addEventListener("cancel", () => {
                    resolve({
                        result: false,
                        dialog,
                    });
                });
            });
        }
        async withoutMessage() {
            const dialog = (0, Dialog_1.dialogFactory)()
                .withoutContent()
                .asConfirmation({
                primary: (0, Language_1.getPhrase)("wcf.dialog.button.primary.confirm"),
            });
            dialog.show(this.#question);
            return new Promise((resolve) => {
                dialog.addEventListener("primary", () => resolve(true));
                dialog.addEventListener("cancel", () => resolve(false));
            });
        }
    }
    exports.ConfirmationCustom = ConfirmationCustom;
});
