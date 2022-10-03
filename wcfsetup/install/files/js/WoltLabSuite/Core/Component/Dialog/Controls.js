define(["require", "exports", "tslib", "../../Language"], function (require, exports, tslib_1, Language) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.DialogControls = void 0;
    Language = tslib_1.__importStar(Language);
    class DialogControls {
        #dialog;
        constructor(dialog) {
            this.#dialog = dialog;
        }
        asAlert(options) {
            const formControlOptions = {
                cancel: undefined,
                extra: undefined,
                isAlert: true,
                primary: options?.primary || "",
            };
            this.#dialog.attachFormControls(formControlOptions);
            return this.#dialog;
        }
        asConfirmation(options) {
            const formControlOptions = {
                cancel: "",
                extra: undefined,
                isAlert: true,
                primary: options?.primary || Language.get("wcf.dialog.button.primary.confirm"),
            };
            this.#dialog.attachFormControls(formControlOptions);
            return this.#dialog;
        }
        asPrompt(options) {
            const formControlOptions = {
                cancel: options?.cancel || "",
                extra: options?.extra,
                isAlert: false,
                primary: options?.primary || Language.get("wcf.dialog.button.primary.submit"),
            };
            this.#dialog.attachFormControls(formControlOptions);
            return this.#dialog;
        }
        withoutControls() {
            return this.#dialog;
        }
    }
    exports.DialogControls = DialogControls;
    exports.default = DialogControls;
});
