define(["require", "exports", "tslib", "WoltLabSuite/Core/Language", "../../../Ui/Dialog"], function (require, exports, tslib_1, Language_1, UiDialog) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    UiDialog = tslib_1.__importStar(UiDialog);
    class AcpUiBoxCopy {
        constructor() {
            document.querySelectorAll(".jsButtonCopyBox").forEach((button) => {
                button.addEventListener("click", (ev) => this.click(ev));
            });
        }
        click(event) {
            event.preventDefault();
            UiDialog.open(this);
        }
        _dialogSetup() {
            return {
                id: "acpBoxCopyDialog",
                options: {
                    title: (0, Language_1.getPhrase)("wcf.acp.box.copy"),
                },
            };
        }
    }
    let acpUiBoxCopy;
    function init() {
        if (!acpUiBoxCopy) {
            acpUiBoxCopy = new AcpUiBoxCopy();
        }
    }
});
