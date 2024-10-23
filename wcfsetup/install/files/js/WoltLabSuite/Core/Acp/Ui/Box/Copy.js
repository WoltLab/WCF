define(["require", "exports", "tslib", "../../../Language", "../../../Ui/Dialog"], function (require, exports, tslib_1, Language, UiDialog) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    Language = tslib_1.__importStar(Language);
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
                    title: Language.get("wcf.acp.box.copy"),
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
