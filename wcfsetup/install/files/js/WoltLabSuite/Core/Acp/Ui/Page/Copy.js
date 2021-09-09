define(["require", "exports", "tslib", "../../../Language", "../../../Ui/Dialog"], function (require, exports, tslib_1, Language, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = void 0;
    Language = (0, tslib_1.__importStar)(Language);
    Dialog_1 = (0, tslib_1.__importDefault)(Dialog_1);
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
                    title: Language.get("wcf.acp.page.copy"),
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
    exports.init = init;
});
