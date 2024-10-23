/**
 * The `confirmationFactory()` offers a consistent way to
 * prompt the user to confirm an action.
 *
 * The actions at minimum require you to provide the question
 * of the dialog. The question is used as the title of dialog
 * and must always be a full sentence that makes a reference
 * to the elements being affectedby the action.
 *
 * Confirmation dialogs should only be presented for actions
 * that are either destructive or that might have a severe
 * impact when executed unintentionally. You should not prompt
 * the user for actions that have no harmful impact in order
 * to prevent confirmation fatigue.
 *
 * Please see the documentation for the guidelines on
 * confirmation dialogs and the phrasing of the question.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports", "tslib", "./Dialog", "../Language", "../Dom/Util", "./Confirmation/Custom"], function (require, exports, tslib_1, Dialog_1, Language_1, DomUtil, Custom_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.confirmationFactory = confirmationFactory;
    DomUtil = tslib_1.__importStar(DomUtil);
    class ConfirmationPrefab {
        custom(question) {
            return new Custom_1.ConfirmationCustom(question);
        }
        async delete(title) {
            const html = `<p>${(0, Language_1.getPhrase)("wcf.dialog.confirmation.cannotBeUndone")}</p>`;
            const dialog = (0, Dialog_1.dialogFactory)()
                .fromHtml(html)
                .asConfirmation({
                primary: (0, Language_1.getPhrase)("wcf.dialog.button.primary.delete"),
            });
            let question;
            if (title === undefined) {
                question = (0, Language_1.getPhrase)("wcf.dialog.confirmation.delete.indeterminate");
            }
            else {
                question = (0, Language_1.getPhrase)("wcf.dialog.confirmation.delete", { title });
            }
            dialog.show(question);
            return new Promise((resolve) => {
                dialog.addEventListener("primary", () => resolve(true));
                dialog.addEventListener("cancel", () => resolve(false));
            });
        }
        async restore(title) {
            const dialog = (0, Dialog_1.dialogFactory)().withoutContent().asConfirmation();
            let question;
            if (title === undefined) {
                question = (0, Language_1.getPhrase)("wcf.dialog.confirmation.restore.indeterminate");
            }
            else {
                question = (0, Language_1.getPhrase)("wcf.dialog.confirmation.restore", { title });
            }
            dialog.show(question);
            return new Promise((resolve) => {
                dialog.addEventListener("primary", () => resolve(true));
                dialog.addEventListener("cancel", () => resolve(false));
            });
        }
        async softDelete(title, askForReason = false) {
            let question;
            if (title === undefined) {
                question = (0, Language_1.getPhrase)("wcf.dialog.confirmation.softDelete.indeterminate");
            }
            else {
                question = (0, Language_1.getPhrase)("wcf.dialog.confirmation.softDelete", { title });
            }
            if (askForReason) {
                return this.withReason(question, true);
            }
            const dialog = (0, Dialog_1.dialogFactory)().withoutContent().asConfirmation();
            dialog.show(question);
            return new Promise((resolve) => {
                dialog.addEventListener("primary", () => {
                    resolve({
                        result: true,
                    });
                });
                dialog.addEventListener("cancel", () => {
                    resolve({
                        result: false,
                    });
                });
            });
        }
        async withReason(question, isOptional) {
            const dialog = (0, Dialog_1.dialogFactory)().withoutContent().asConfirmation();
            const id = DomUtil.getUniqueId();
            const label = (0, Language_1.getPhrase)(isOptional ? "wcf.dialog.confirmation.reason.optional" : "wcf.dialog.confirmation.reason");
            const dl = document.createElement("dl");
            dl.innerHTML = `
      <dt><label for="${id}">${label}</label></dt>
      <dd><textarea id="${id}" cols="40" rows="3"></textarea></dd>
    `;
            const reason = dl.querySelector("textarea");
            dialog.content.append(dl);
            dialog.show(question);
            return new Promise((resolve) => {
                dialog.addEventListener("primary", () => {
                    resolve({
                        result: true,
                        reason: reason.value.trim(),
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
});
