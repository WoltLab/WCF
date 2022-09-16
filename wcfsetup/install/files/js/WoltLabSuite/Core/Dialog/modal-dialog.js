define(["require", "exports", "tslib", "../Dom/Util"], function (require, exports, tslib_1, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.ModalDialog = void 0;
    Util_1 = tslib_1.__importDefault(Util_1);
    const dialogContainer = document.createElement("div");
    class ModalDialog extends HTMLElement {
        #content = undefined;
        #dialog;
        #returnFocus = undefined;
        #title;
        constructor() {
            super();
            this.#dialog = document.createElement("dialog");
            this.#title = document.createElement("div");
        }
        connectedCallback() {
            this.#attachDialog();
        }
        show() {
            if (this.#title.textContent.trim().length === 0) {
                throw new Error("Cannot open the modal dialog without a title.");
            }
            if (this.#dialog.parentElement === null) {
                if (dialogContainer.parentElement === null) {
                    document.getElementById("content").append(dialogContainer);
                }
                dialogContainer.append(this);
            }
            this.#dialog.showModal();
        }
        close() {
            this.#dialog.close();
            if (this.#returnFocus !== undefined) {
                const element = this.#returnFocus();
                element?.focus();
            }
        }
        get dialog() {
            return this.#dialog;
        }
        get content() {
            if (this.#content === undefined) {
                this.#content = document.createElement("div");
                this.#content.classList.add("dialog__content");
            }
            return this.#content;
        }
        set content(element) {
            if (this.#content !== undefined) {
                throw new Error("There is already a content element for this dialog.");
            }
            if (!(element instanceof HTMLElement) || element.nodeName !== "DIV") {
                throw new TypeError("Only '<div>' elements are allowed as the content element.");
            }
            this.#content = element;
            this.#content.classList.add("dialog__content");
        }
        set title(title) {
            this.#title.textContent = title;
        }
        set returnFocus(returnFocus) {
            if (typeof returnFocus !== "function") {
                throw new TypeError("Expected a callback function for the return focus.");
            }
            this.#returnFocus = returnFocus;
        }
        get open() {
            return this.#dialog.open;
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
        #attachDialog() {
            if (this.#dialog.parentElement !== null) {
                return;
            }
            const closeButton = document.createElement("button");
            closeButton.innerHTML = '<fa-icon size="24" name="xmark"></fa-icon>';
            closeButton.classList.add("dialog__closeButton");
            closeButton.addEventListener("click", () => {
                this.close();
            });
            const header = document.createElement("div");
            header.classList.add("dialog__header");
            this.#title.classList.add("dialog__title");
            header.append(this.#title, closeButton);
            const doc = document.createElement("div");
            doc.classList.add("dialog__document");
            doc.setAttribute("role", "document");
            doc.append(header, this.content);
            this.#dialog.append(doc);
            this.#dialog.classList.add("dialog");
            this.#dialog.setAttribute("aria-labelledby", Util_1.default.identify(this.#title));
            this.#dialog.addEventListener("cancel", (event) => {
                if (!this.closable) {
                    event.preventDefault();
                    return;
                }
            });
            // Close the dialog by clicking on the backdrop.
            this.#dialog.addEventListener("click", (event) => {
                if (event.target === this.#dialog) {
                    this.close();
                }
            });
            this.append(this.#dialog);
        }
    }
    exports.ModalDialog = ModalDialog;
    window.customElements.define("modal-dialog", ModalDialog);
});
