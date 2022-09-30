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
            const dialog = (0, Dialog_1.dialogFactory)()
                .fromHtml(`<p>${message}</p>`)
                .asConfirmation({ primary: Language.get("wcf.dialog.button.primary.delete") });
            dialog.show(this.#question);
            return new Promise((resolve) => {
                dialog.addEventListener("primary", () => resolve(true));
                dialog.addEventListener("cancel", () => resolve(false));
            });
        }
    }
    exports.ConfirmationDelete = ConfirmationDelete;
});
