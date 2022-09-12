define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.fromHtml = exports.fromId = exports.fromElement = void 0;
    function fromElement(element) {
        if (!(element instanceof HTMLElement) || element.nodeName !== "DIV") {
            throw new TypeError("Only '<div>' elements are allowed as the content element.");
        }
        const dialog = document.createElement("modal-dialog");
        dialog.content = element;
        return dialog;
    }
    exports.fromElement = fromElement;
    function fromId(id) {
        const element = document.getElementById(id);
        if (element === null) {
            throw new Error(`Unable to find the element identified by '${id}'.`);
        }
        return fromElement(element);
    }
    exports.fromId = fromId;
    function fromHtml(html) {
        const element = document.createElement("div");
        element.innerHTML = html;
        if (element.childElementCount === 0) {
            throw new TypeError("The provided HTML string did not contain any elements.");
        }
        return fromElement(element);
    }
    exports.fromHtml = fromHtml;
});
