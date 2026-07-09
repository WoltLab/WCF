define(["require", "exports", "tslib", "WoltLabSuite/Core/Language", "../../../Ui/Dialog"], function (require, exports, tslib_1, Language_1, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    class AcpUiPageCopy {
        constructor() {
            document.querySelectorAll(".jsButtonCopyPage").forEach((button) => {
                button.addEventListener("click", (ev) => this.click(ev));
            });
        }
        click(event) {
            event.preventDefault();
            Dialog_1.default.open(this);
        }
        _dialogSetup() {
            return {
                id: "acpPageCopyDialog",
                options: {
                    title: (0, Language_1.getPhrase)("wcf.acp.page.copy"),
                },
            };
        }
    }
    let acpUiPageCopy;
    function init() {
        if (!acpUiPageCopy) {
            acpUiPageCopy = new AcpUiPageCopy();
        }
    }
});
