define(["require", "exports", "tslib", "./Controls"], function (require, exports, tslib_1, Controls_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.DialogSetup = void 0;
    Controls_1 = tslib_1.__importDefault(Controls_1);
    class DialogSetup {
        fromElement(element) {
            if (!(element instanceof HTMLElement) && !(element instanceof DocumentFragment)) {
                throw new TypeError("Expected an HTML element or a document fragment.");
            }
            const dialog = document.createElement("modal-dialog");
            dialog.content.append(element);
            return new Controls_1.default(dialog);
        }
        fromId(id) {
            const element = document.getElementById(id);
            if (element === null) {
                throw new Error(`Unable to find the element identified by '${id}'.`);
            }
            return this.fromElement(element);
        }
        fromHtml(html) {
            const element = document.createElement("div");
            element.innerHTML = html;
            if (element.childElementCount === 0) {
                throw new TypeError("The provided HTML string did not contain any elements.");
            }
            const fragment = document.createDocumentFragment();
            fragment.append(...element.childNodes);
            return this.fromElement(fragment);
        }
    }
    exports.DialogSetup = DialogSetup;
    exports.default = DialogSetup;
});
