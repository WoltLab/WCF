define(["require", "exports", "tslib", "./Dialog/modal-dialog"], function (require, exports, tslib_1, modal_dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.dialogFromHtml = exports.dialogFromId = exports.dialogFromElement = void 0;
    function dialogFromElement(element) {
        if (!(element instanceof HTMLElement) || element.nodeName !== "DIV") {
            throw new TypeError("Only '<div>' elements are allowed as the content element.");
        }
        const dialog = document.createElement("modal-dialog");
        dialog.content = element;
        return dialog;
    }
    exports.dialogFromElement = dialogFromElement;
    function dialogFromId(id) {
        const element = document.getElementById(id);
        if (element === null) {
            throw new Error(`Unable to find the element identified by '${id}'.`);
        }
        return dialogFromElement(element);
    }
    exports.dialogFromId = dialogFromId;
    function dialogFromHtml(html) {
        const element = document.createElement("div");
        element.innerHTML = html;
        if (element.childElementCount === 0) {
            throw new TypeError("The provided HTML string did not contain any elements.");
        }
        return dialogFromElement(element);
    }
    exports.dialogFromHtml = dialogFromHtml;
    tslib_1.__exportStar(modal_dialog_1, exports);
});
