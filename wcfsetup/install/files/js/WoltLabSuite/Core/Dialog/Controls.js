define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.DialogControls = void 0;
    class DialogControls {
        #dialog;
        constructor(dialog) {
            this.#dialog = dialog;
        }
        asAlert(options) {
            options = Object.assign({
                primary: "",
            }, options);
            this.#dialog.attachFormControls(options);
            return this.#dialog;
        }
        withoutControls() {
            return this.#dialog;
        }
    }
    exports.DialogControls = DialogControls;
    exports.default = DialogControls;
});
