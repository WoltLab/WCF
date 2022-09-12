define(["require", "exports", "tslib", "../Dom/Util"], function (require, exports, tslib_1, Util_1) {
    "use strict";
    var _ModalDialog_instances, _ModalDialog_content, _ModalDialog_dialog, _ModalDialog_returnFocus, _ModalDialog_title, _ModalDialog_attachDialog;
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = exports.ModalDialog = void 0;
    Util_1 = tslib_1.__importDefault(Util_1);
    class ModalDialog extends HTMLElement {
        constructor() {
            super();
            _ModalDialog_instances.add(this);
            _ModalDialog_content.set(this, undefined);
            _ModalDialog_dialog.set(this, void 0);
            _ModalDialog_returnFocus.set(this, undefined);
            _ModalDialog_title.set(this, void 0);
            tslib_1.__classPrivateFieldSet(this, _ModalDialog_dialog, document.createElement("dialog"), "f");
            tslib_1.__classPrivateFieldSet(this, _ModalDialog_title, document.createElement("title"), "f");
        }
        connectedCallback() {
            tslib_1.__classPrivateFieldGet(this, _ModalDialog_instances, "m", _ModalDialog_attachDialog).call(this);
        }
        show() {
            if (tslib_1.__classPrivateFieldGet(this, _ModalDialog_title, "f").textContent.trim().length === 0) {
                throw new Error("Cannot open the modal dialog without a title.");
            }
            tslib_1.__classPrivateFieldGet(this, _ModalDialog_dialog, "f").showModal();
        }
        close() {
            tslib_1.__classPrivateFieldGet(this, _ModalDialog_dialog, "f").close();
            if (tslib_1.__classPrivateFieldGet(this, _ModalDialog_returnFocus, "f") !== undefined) {
                const element = tslib_1.__classPrivateFieldGet(this, _ModalDialog_returnFocus, "f").call(this);
                element === null || element === void 0 ? void 0 : element.focus();
            }
        }
        get dialog() {
            return tslib_1.__classPrivateFieldGet(this, _ModalDialog_dialog, "f");
        }
        get content() {
            if (tslib_1.__classPrivateFieldGet(this, _ModalDialog_content, "f") === undefined) {
                tslib_1.__classPrivateFieldSet(this, _ModalDialog_content, document.createElement("div"), "f");
            }
            return tslib_1.__classPrivateFieldGet(this, _ModalDialog_content, "f");
        }
        set content(element) {
            if (tslib_1.__classPrivateFieldGet(this, _ModalDialog_content, "f") !== undefined) {
                throw new Error("There is already a content element for this dialog.");
            }
            if (!(element instanceof HTMLElement) || element.nodeName !== "DIV") {
                throw new TypeError("Only '<div>' elements are allowed as the content element.");
            }
            tslib_1.__classPrivateFieldSet(this, _ModalDialog_content, element, "f");
        }
        set title(title) {
            tslib_1.__classPrivateFieldGet(this, _ModalDialog_title, "f").textContent = title;
        }
        set returnFocus(returnFocus) {
            if (typeof returnFocus !== "function") {
                throw new TypeError("Expected a callback function for the return focus.");
            }
            tslib_1.__classPrivateFieldSet(this, _ModalDialog_returnFocus, returnFocus, "f");
        }
        get open() {
            return tslib_1.__classPrivateFieldGet(this, _ModalDialog_dialog, "f").open;
        }
        get closable() {
            return this.hasAttribute("closable");
        }
        set closable(closable) {
            if (closable) {
                this.setAttribute("closable", "");
            }
            else {
                this.removeAttribute("closable");
            }
        }
    }
    exports.ModalDialog = ModalDialog;
    _ModalDialog_content = new WeakMap(), _ModalDialog_dialog = new WeakMap(), _ModalDialog_returnFocus = new WeakMap(), _ModalDialog_title = new WeakMap(), _ModalDialog_instances = new WeakSet(), _ModalDialog_attachDialog = function _ModalDialog_attachDialog() {
        if (tslib_1.__classPrivateFieldGet(this, _ModalDialog_dialog, "f").parentElement !== null) {
            return;
        }
        const closeButton = document.createElement("button");
        closeButton.innerHTML = '<fa-icon name="xmark"></fa-icon>';
        closeButton.addEventListener("click", () => {
            this.close();
        });
        const header = document.createElement("div");
        header.append(tslib_1.__classPrivateFieldGet(this, _ModalDialog_title, "f"), closeButton);
        const doc = document.createElement("div");
        doc.setAttribute("role", "document");
        doc.append(header, this.content);
        tslib_1.__classPrivateFieldGet(this, _ModalDialog_dialog, "f").append(doc);
        tslib_1.__classPrivateFieldGet(this, _ModalDialog_dialog, "f").setAttribute("aria-labelledby", Util_1.default.identify(tslib_1.__classPrivateFieldGet(this, _ModalDialog_title, "f")));
        tslib_1.__classPrivateFieldGet(this, _ModalDialog_dialog, "f").addEventListener("cancel", (event) => {
            if (!this.closable) {
                event.preventDefault();
                return;
            }
        });
        document.body.append(tslib_1.__classPrivateFieldGet(this, _ModalDialog_dialog, "f"));
    };
    function setup() {
        if (window.customElements.get("modal-dialog") === undefined) {
            window.customElements.define("modal-dialog", ModalDialog);
        }
    }
    exports.setup = setup;
});
