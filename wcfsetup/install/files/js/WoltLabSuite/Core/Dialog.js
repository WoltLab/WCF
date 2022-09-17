define(["require", "exports", "tslib", "./Dialog/Setup", "./Dialog/modal-dialog"], function (require, exports, tslib_1, Setup_1, modal_dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.createDialog = void 0;
    function createDialog() {
        return new Setup_1.DialogSetup();
    }
    exports.createDialog = createDialog;
    tslib_1.__exportStar(modal_dialog_1, exports);
});
