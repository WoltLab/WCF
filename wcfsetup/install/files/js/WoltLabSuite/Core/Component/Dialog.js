define(["require", "exports", "./Dialog/Setup", "../Element/woltlab-core-dialog", "../Element/woltlab-core-dialog-control"], function (require, exports, Setup_1, woltlab_core_dialog_1, woltlab_core_dialog_control_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.dialogFactory = void 0;
    function dialogFactory() {
        (0, woltlab_core_dialog_1.setup)();
        (0, woltlab_core_dialog_control_1.setup)();
        return new Setup_1.DialogSetup();
    }
    exports.dialogFactory = dialogFactory;
});
