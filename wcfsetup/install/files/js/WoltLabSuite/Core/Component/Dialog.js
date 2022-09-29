define(["require", "exports", "tslib", "./Dialog/Setup", "../Element/woltlab-core-dialog-control", "../Element/woltlab-core-dialog"], function (require, exports, tslib_1, Setup_1, woltlab_core_dialog_control_1, woltlab_core_dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.dialogFactory = void 0;
    function dialogFactory() {
        (0, woltlab_core_dialog_control_1.setup)();
        return new Setup_1.DialogSetup();
    }
    exports.dialogFactory = dialogFactory;
    tslib_1.__exportStar(woltlab_core_dialog_1, exports);
});
