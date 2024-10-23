/**
 * Provides the confirmation dialog overlay.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.0 Use `confirmationFactory()` instead.
 */
define(["require", "exports", "tslib", "../Core", "../Language", "./Dialog"], function (require, exports, tslib_1, Core, Language, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.show = show;
    exports.getContentElement = getContentElement;
    Core = tslib_1.__importStar(Core);
    Language = tslib_1.__importStar(Language);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    class UiConfirmation {
        _active = false;
        parameters;
        confirmButton;
        _content;
        dialog;
        text;
        callbackCancel;
        callbackConfirm;
        constructor() {
            this.dialog = document.createElement("div");
            this.dialog.id = "wcfSystemConfirmation";
            this.dialog.classList.add("systemConfirmation");
            this.text = document.createElement("p");
            this.dialog.appendChild(this.text);
            this._content = document.createElement("div");
            this._content.id = "wcfSystemConfirmationContent";
            this.dialog.appendChild(this._content);
            const formSubmit = document.createElement("div");
            formSubmit.classList.add("formSubmit");
            this.dialog.appendChild(formSubmit);
            this.confirmButton = document.createElement("button");
            this.confirmButton.type = "button";
            this.confirmButton.dataset.type = "submit";
            this.confirmButton.classList.add("button", "buttonPrimary");
            this.confirmButton.textContent = Language.get("wcf.global.confirmation.confirm");
            formSubmit.appendChild(this.confirmButton);
            const cancelButton = document.createElement("button");
            cancelButton.type = "button";
            cancelButton.classList.add("button");
            cancelButton.textContent = Language.get("wcf.global.confirmation.cancel");
            cancelButton.addEventListener("click", () => {
                Dialog_1.default.close(this);
            });
            formSubmit.appendChild(cancelButton);
            document.body.appendChild(this.dialog);
        }
        open(options) {
            this.parameters = options.parameters || {};
            this._content.innerHTML = typeof options.template === "string" ? options.template.trim() : "";
            this.text[options.messageIsHtml ? "innerHTML" : "textContent"] = options.message;
            if (typeof options.legacyCallback === "function") {
                this.callbackCancel = (parameters) => {
                    options.legacyCallback("cancel", parameters, this.content);
                };
                this.callbackConfirm = (parameters) => {
                    options.legacyCallback("confirm", parameters, this.content);
                };
            }
            else {
                if (typeof options.cancel !== "function") {
                    options.cancel = () => {
                        // Do nothing
                    };
                }
                this.callbackCancel = options.cancel;
                this.callbackConfirm = options.confirm;
            }
            this._active = true;
            Dialog_1.default.open(this);
        }
        get active() {
            return this._active;
        }
        get content() {
            return this._content;
        }
        /**
         * Invoked if the user confirms the dialog.
         */
        _confirm() {
            this.callbackConfirm(this.parameters, this.content);
            this._active = false;
            Dialog_1.default.close("wcfSystemConfirmation");
        }
        /**
         * Invoked on dialog close or if user cancels the dialog.
         */
        _onClose() {
            if (this.active) {
                this.confirmButton.blur();
                this._active = false;
                this.callbackCancel(this.parameters);
            }
        }
        /**
         * Sets the focus on the confirm button on dialog open for proper keyboard support.
         */
        _onShow() {
            window.setTimeout(() => {
                this.confirmButton.blur();
                this.confirmButton.focus();
            }, 1);
        }
        _dialogSetup() {
            return {
                id: "wcfSystemConfirmation",
                options: {
                    onClose: this._onClose.bind(this),
                    onShow: this._onShow.bind(this),
                    title: Language.get("wcf.global.confirmation.title"),
                },
            };
        }
        _dialogSubmit() {
            this._confirm();
        }
    }
    let confirmation;
    function getConfirmation() {
        if (!confirmation) {
            confirmation = new UiConfirmation();
        }
        return confirmation;
    }
    /**
     * Shows the confirmation dialog.
     * @deprecated 6.0 Use `confirmationFactory()` instead.
     */
    function show(options) {
        if (getConfirmation().active) {
            return;
        }
        options = Core.extend({
            cancel: null,
            confirm: null,
            legacyCallback: null,
            message: "",
            messageIsHtml: false,
            parameters: {},
            template: "",
        }, options);
        options.message = typeof options.message === "string" ? options.message.trim() : "";
        if (!options.message) {
            throw new Error("Expected a non-empty string for option 'message'.");
        }
        if (typeof options.confirm !== "function" && typeof options.legacyCallback !== "function") {
            throw new TypeError("Expected a valid callback for option 'confirm'.");
        }
        getConfirmation().open(options);
    }
    /**
     * Returns content container element.
     */
    function getContentElement() {
        return getConfirmation().content;
    }
});
