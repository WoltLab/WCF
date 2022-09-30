define(["require", "exports", "tslib", "../Dialog", "../../Language"], function (require, exports, tslib_1, Dialog_1, Language) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.ConfirmationDelete = void 0;
    Language = tslib_1.__importStar(Language);
    class ConfirmationDelete {
        #question;
        constructor(question) {
            this.#question = question;
        }
        async defaultMessage(title = "") {
            const message = Language.get("wcf.dialog.confirmation.delete", { title });
            return this.message(message);
        }
        async message(message) {
            if (message.trim() === "") {
                throw new Error("An empty message for the delete confirmation was provided. Please use `defaultMessage()` if you do not want to provide a  custom message.");
            }
            const dialog = (0, Dialog_1.dialogFactory)()
                .fromHtml(`<p>${message}</p>`)
                .asConfirmation({
                primary: Language.get("wcf.dialog.button.primary.delete"),
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
                primary: Language.get("wcf.dialog.button.primary.delete"),
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
    }
    exports.ConfirmationDelete = ConfirmationDelete;
});
