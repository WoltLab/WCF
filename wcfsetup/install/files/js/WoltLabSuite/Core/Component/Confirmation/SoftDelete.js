define(["require", "exports", "tslib", "../Dialog", "../../Language", "../../Dom/Util"], function (require, exports, tslib_1, Dialog_1, Language, DomUtil) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.ConfirmationSoftDelete = void 0;
    Language = tslib_1.__importStar(Language);
    DomUtil = tslib_1.__importStar(DomUtil);
    class ConfirmationSoftDelete {
        #reasonInput;
        #question;
        constructor(question) {
            this.#question = question;
        }
        async askForReason() {
            return new Promise((resolve) => {
                void this.withFormElements((dialog) => {
                    this.#addReasonInput(dialog);
                }).then(({ result }) => {
                    if (result) {
                        resolve({
                            result: true,
                            reason: this.#reasonInput.value.trim(),
                        });
                    }
                    else {
                        resolve({
                            result: false,
                            reason: "",
                        });
                    }
                });
            });
        }
        #addReasonInput(dialog) {
            const id = DomUtil.getUniqueId();
            const label = Language.get("wcf.dialog.confirmation.softDelete.reason");
            const dl = document.createElement("dl");
            dl.innerHTML = `
      <dt><label for="${id}">${label}</label></dt>
      <dd><textarea id="${id}" cols="40" rows="3"></textarea></dd>
    `;
            this.#reasonInput = dl.querySelector("textarea");
            dialog.content.append(dl);
        }
        async message(message) {
            if (message.trim() === "") {
                throw new Error("An empty message for the delete confirmation was provided. Please use `defaultMessage()` if you do not want to provide a  custom message.");
            }
            const dialog = (0, Dialog_1.dialogFactory)()
                .fromHtml(`<p>${message}</p>`)
                .asConfirmation({
                primary: Language.get("wcf.dialog.button.primary.confirm"),
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
                primary: Language.get("wcf.dialog.button.primary.confirm"),
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
                primary: Language.get("wcf.dialog.button.primary.confirm"),
            });
            dialog.show(this.#question);
            return new Promise((resolve) => {
                dialog.addEventListener("primary", () => resolve(true));
                dialog.addEventListener("cancel", () => resolve(false));
            });
        }
    }
    exports.ConfirmationSoftDelete = ConfirmationSoftDelete;
});
