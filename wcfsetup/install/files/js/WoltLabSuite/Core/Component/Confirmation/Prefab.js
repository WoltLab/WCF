define(["require", "exports", "tslib", "../Dialog", "../../Language"], function (require, exports, tslib_1, Dialog_1, Language) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.ConfirmationPrefab = void 0;
    Language = tslib_1.__importStar(Language);
    class ConfirmationPrefab {
        #title;
        constructor(title) {
            this.#title = title;
        }
        async delete() {
            const html = `<p>${Language.get("wcf.dialog.confirmation.cannotBeUndone")}</p>`;
            const dialog = (0, Dialog_1.dialogFactory)()
                .fromHtml(html)
                .asConfirmation({
                primary: Language.get("wcf.dialog.button.primary.delete"),
            });
            const question = Language.get("wcf.dialog.confirmation.delete", { title: this.#title });
            dialog.show(question);
            return new Promise((resolve) => {
                dialog.addEventListener("primary", () => resolve(true));
                dialog.addEventListener("cancel", () => resolve(false));
            });
        }
        async restore() {
            const question = Language.get("wcf.dialog.confirmation.restore", { title: this.#title });
            return this.#withoutFormElements(question);
        }
        async softDelete() {
            const question = Language.get("wcf.dialog.confirmation.softDelete", { title: this.#title });
            return this.#withoutFormElements(question);
        }
        #withoutFormElements(question) {
            const dialog = (0, Dialog_1.dialogFactory)().withoutContent().asConfirmation();
            dialog.show(question);
            return new Promise((resolve) => {
                dialog.addEventListener("primary", () => resolve(true));
                dialog.addEventListener("cancel", () => resolve(false));
            });
        }
    }
    exports.ConfirmationPrefab = ConfirmationPrefab;
});
