define(["require", "exports", "tslib", "./Dialog/Setup", "./Form/form-control", "./Dialog/modal-dialog"], function (require, exports, tslib_1, Setup_1, form_control_1, modal_dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.dialogFactory = void 0;
    function dialogFactory() {
        (0, form_control_1.setup)();
        return new Setup_1.DialogSetup();
    }
    exports.dialogFactory = dialogFactory;
    tslib_1.__exportStar(modal_dialog_1, exports);
});
