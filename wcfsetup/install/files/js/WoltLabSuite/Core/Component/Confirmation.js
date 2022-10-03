define(["require", "exports", "tslib", "./Dialog", "../Language", "../Dom/Util", "./Confirmation/Custom"], function (require, exports, tslib_1, Dialog_1, Language, DomUtil, Custom_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.confirmationFactory = void 0;
    Language = tslib_1.__importStar(Language);
    DomUtil = tslib_1.__importStar(DomUtil);
    class ConfirmationPrefab {
        custom(question) {
            return new Custom_1.ConfirmationCustom(question);
        }
        async delete(title) {
            const html = `<p>${Language.get("wcf.dialog.confirmation.cannotBeUndone")}</p>`;
            const dialog = (0, Dialog_1.dialogFactory)()
                .fromHtml(html)
                .asConfirmation({
                primary: Language.get("wcf.dialog.button.primary.delete"),
            });
            const question = Language.get("wcf.dialog.confirmation.delete", { title });
            dialog.show(question);
            return new Promise((resolve) => {
                dialog.addEventListener("primary", () => resolve(true));
                dialog.addEventListener("cancel", () => resolve(false));
            });
        }
        async restore(title) {
            const dialog = (0, Dialog_1.dialogFactory)().withoutContent().asConfirmation();
            const question = Language.get("wcf.dialog.confirmation.restore", { title });
            dialog.show(question);
            return new Promise((resolve) => {
                dialog.addEventListener("primary", () => resolve(true));
                dialog.addEventListener("cancel", () => resolve(false));
            });
        }
        async softDelete(title, askForReason) {
            const dialog = (0, Dialog_1.dialogFactory)().withoutContent().asConfirmation();
            let reason = undefined;
            if (askForReason) {
                const id = DomUtil.getUniqueId();
                const label = Language.get("wcf.dialog.confirmation.softDelete.reason");
                const dl = document.createElement("dl");
                dl.innerHTML = `
        <dt><label for="${id}">${label}</label></dt>
        <dd><textarea id="${id}" cols="40" rows="3"></textarea></dd>
      `;
                reason = dl.querySelector("textarea");
                dialog.append(reason);
            }
            const question = Language.get("wcf.dialog.confirmation.softDelete", { title });
            dialog.show(question);
            return new Promise((resolve) => {
                dialog.addEventListener("primary", () => {
                    resolve({
                        result: true,
                        reason: reason ? reason.value.trim() : "",
                    });
                });
                dialog.addEventListener("cancel", () => {
                    resolve({
                        result: false,
                        reason: "",
                    });
                });
            });
        }
    }
    function confirmationFactory() {
        return new ConfirmationPrefab();
    }
    exports.confirmationFactory = confirmationFactory;
});
