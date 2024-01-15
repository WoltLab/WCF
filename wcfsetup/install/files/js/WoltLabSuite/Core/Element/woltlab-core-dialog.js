/**
 * The web component `<woltlab-core-dialog>` represents a
 * modal dialog with a unified event access for consistent
 * interactions. This is the low-level API of dialogs, you
 * should use the `dialogFactory()` to create them.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports", "tslib", "../Dom/Util", "../Helper/PageOverlay", "../Language", "../Ui/Screen"], function (require, exports, tslib_1, Util_1, PageOverlay_1, Language, Screen_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.WoltlabCoreDialogElement = void 0;
    Util_1 = tslib_1.__importDefault(Util_1);
    Language = tslib_1.__importStar(Language);
    const dialogContainer = document.createElement("div");
    // eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
    class WoltlabCoreDialogElement extends HTMLElement {
        #content;
        #dialog;
        #form;
        #title;
        constructor() {
            super();
            this.#content = document.createElement("div");
            this.#dialog = document.createElement("dialog");
            this.#title = document.createElement("div");
        }
        show(title) {
            if (title.trim().length === 0) {
                throw new Error("Cannot open the modal dialog without a title.");
            }
            this.#title.textContent = title;
            if (this.open) {
                return;
            }
            if (dialogContainer.parentElement === null) {
                document.getElementById("content").append(dialogContainer);
            }
            if (this.parentElement !== dialogContainer) {
                dialogContainer.append(this);
            }
            this.#dialog.showModal();
            (0, PageOverlay_1.adoptPageOverlayContainer)(this.#dialog);
            (0, Screen_1.scrollDisable)();
        }
        close() {
            this.#dialog.close();
            this.#detachDialog();
        }
        #detachDialog() {
            if (this.parentNode === null) {
                return;
            }
            const event = new CustomEvent("afterClose");
            this.dispatchEvent(event);
            (0, PageOverlay_1.releasePageOverlayContainer)(this.#dialog);
            (0, Screen_1.scrollEnable)();
            // Remove the dialog from the DOM, preventing it from
            // causing any collisions caused by elements with IDs
            // contained inside it. Will also cause the DOM element
            // to be garbage collected when there are no more
            // references to it.
            this.remove();
        }
        get dialog() {
            return this.#dialog;
        }
        get content() {
            return this.#content;
        }
        get open() {
            return this.#dialog.open;
        }
        get incomplete() {
            return this.hasAttribute("incomplete");
        }
        set incomplete(incomplete) {
            if (incomplete) {
                this.setAttribute("incomplete", "");
            }
            else {
                this.removeAttribute("incomplete");
            }
        }
        attachControls(options) {
            if (this.#form !== undefined) {
                throw new Error("There is already a form control attached to this dialog.");
            }
            if (options.extra !== undefined && options.cancel === undefined) {
                options.cancel = "";
            }
            const formControl = document.createElement("woltlab-core-dialog-control");
            formControl.primary = options.primary;
            if (options.cancel !== undefined) {
                formControl.cancel = options.cancel;
            }
            if (options.extra !== undefined) {
                formControl.extra = options.extra;
            }
            this.#form = document.createElement("form");
            this.#form.method = "dialog";
            this.#form.classList.add("dialog__form");
            this.#content.insertAdjacentElement("beforebegin", this.#form);
            this.#form.append(this.#content, formControl);
            if (options.isAlert) {
                if (options.cancel === undefined) {
                    this.#dialog.setAttribute("role", "alert");
                }
                else {
                    this.#dialog.setAttribute("role", "alertdialog");
                }
            }
            this.#form.addEventListener("submit", (event) => {
                if (this.incomplete) {
                    event.preventDefault();
                    return;
                }
                const callbacks = [];
                const evt = new CustomEvent("validate", {
                    cancelable: true,
                    detail: callbacks,
                });
                this.dispatchEvent(evt);
                if (evt.defaultPrevented) {
                    event.preventDefault();
                }
                if (evt.detail.length > 0) {
                    // DOM events cannot wait for async functions. We must
                    // reject the event and then wait for the async
                    // callbacks to complete.
                    event.preventDefault();
                    // Blocking further attempts to submit the dialog
                    // while the validation is running.
                    this.incomplete = true;
                    void Promise.all(evt.detail).then((results) => {
                        this.incomplete = false;
                        const failedValidation = results.some((result) => result === false);
                        if (!failedValidation) {
                            // The `primary` event is triggered once the validation
                            // has completed. Triggering the submit again would cause
                            // `validate` to run again, causing an infinite loop.
                            this.#dispatchPrimaryEvent();
                            this.close();
                        }
                    });
                }
            });
            this.#dialog.addEventListener("close", () => {
                if (this.#dialog.returnValue === "") {
                    // Dialog was programmatically closed.
                }
                else {
                    this.#dispatchPrimaryEvent();
                }
                this.#detachDialog();
            });
            formControl.addEventListener("cancel", () => {
                const event = new CustomEvent("cancel", { cancelable: true });
                this.dispatchEvent(event);
                if (!event.defaultPrevented) {
                    this.close();
                }
            });
            if (options.extra !== undefined) {
                formControl.addEventListener("extra", () => {
                    const event = new CustomEvent("extra");
                    this.dispatchEvent(event);
                });
            }
        }
        #dispatchPrimaryEvent() {
            const evt = new CustomEvent("primary");
            this.dispatchEvent(evt);
        }
        connectedCallback() {
            if (this.#dialog.parentElement !== null) {
                return;
            }
            let closeButton;
            const dialogRole = this.#dialog.getAttribute("role");
            if (dialogRole !== "alert" && dialogRole !== "alertdialog") {
                closeButton = document.createElement("button");
                closeButton.innerHTML = '<fa-icon size="24" name="xmark"></fa-icon>';
                closeButton.classList.add("dialog__closeButton", "jsTooltip");
                closeButton.title = Language.get("wcf.dialog.button.close");
                closeButton.addEventListener("click", () => {
                    this.close();
                });
            }
            const header = document.createElement("div");
            header.classList.add("dialog__header");
            this.#title.classList.add("dialog__title");
            header.append(this.#title);
            if (closeButton) {
                header.append(closeButton);
            }
            const doc = document.createElement("div");
            doc.classList.add("dialog__document");
            doc.setAttribute("role", "document");
            doc.append(header);
            this.#content.classList.add("dialog__content");
            if (this.#form) {
                doc.append(this.#form);
            }
            else {
                doc.append(this.#content);
            }
            this.#dialog.append(doc);
            this.#dialog.classList.add("dialog");
            this.#dialog.setAttribute("aria-labelledby", Util_1.default.identify(this.#title));
            this.#dialog.addEventListener("cancel", () => {
                const event = new CustomEvent("cancel");
                this.dispatchEvent(event);
                this.#detachDialog();
            });
            // Close the dialog by clicking on the backdrop.
            //
            // Using the `close` event is not an option because it will
            // also trigger when holding the mouse button inside the
            // dialog and then releasing it on the backdrop.
            this.#dialog.addEventListener("mousedown", (event) => {
                if (event.target === this.#dialog) {
                    const event = new CustomEvent("backdrop", { cancelable: true });
                    this.dispatchEvent(event);
                    if (event.defaultPrevented) {
                        return;
                    }
                    if (this.#shouldClose()) {
                        this.close();
                    }
                }
            });
            this.append(this.#dialog);
        }
        #shouldClose() {
            const event = new CustomEvent("close");
            this.dispatchEvent(event);
            return event.defaultPrevented === false;
        }
    }
    exports.WoltlabCoreDialogElement = WoltlabCoreDialogElement;
    window.customElements.define("woltlab-core-dialog", WoltlabCoreDialogElement);
    exports.default = WoltlabCoreDialogElement;
});
