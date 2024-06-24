/**
 * The `<woltlab-core-comment-response>` element represents a response in the comment list.
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports", "tslib", "../../../Dom/Util", "../../../Ui/Dropdown/Simple", "../../../Ui/Notification", "../../Confirmation", "../../../Event/Handler", "../../../Ui/Scroll", "../../../Language", "../../Ckeditor", "WoltLabSuite/Core/Api/Comments/Responses/EnableResponse", "WoltLabSuite/Core/Api/Comments/Responses/DeleteResponse", "WoltLabSuite/Core/Api/Comments/Responses/EditResponse", "WoltLabSuite/Core/Api/Comments/Responses/RenderResponse", "WoltLabSuite/Core/Api/Comments/Responses/UpdateResponse"], function (require, exports, tslib_1, Util_1, Simple_1, UiNotification, Confirmation_1, EventHandler, UiScroll, Language_1, Ckeditor_1, EnableResponse_1, DeleteResponse_1, EditResponse_1, RenderResponse_1, UpdateResponse_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.WoltlabCoreCommentResponseElement = void 0;
    Util_1 = tslib_1.__importDefault(Util_1);
    Simple_1 = tslib_1.__importDefault(Simple_1);
    UiNotification = tslib_1.__importStar(UiNotification);
    EventHandler = tslib_1.__importStar(EventHandler);
    UiScroll = tslib_1.__importStar(UiScroll);
    class WoltlabCoreCommentResponseElement extends HTMLParsedElement {
        parsedCallback() {
            if (this.menu) {
                const enableButton = this.menu.querySelector(".commentResponse__option--enable");
                enableButton?.addEventListener("click", (event) => {
                    event.preventDefault();
                    void this.#enable();
                });
                const deleteButton = this.menu.querySelector(".commentResponse__option--delete");
                deleteButton?.addEventListener("click", (event) => {
                    event.preventDefault();
                    void this.#delete();
                });
                const editButton = this.menu.querySelector(".commentResponse__option--edit");
                editButton?.addEventListener("click", (event) => {
                    event.preventDefault();
                    void this.#startEdit();
                });
            }
        }
        async #enable() {
            (await (0, EnableResponse_1.enableResponse)(this.responseId)).unwrap();
            this.querySelector(".commentResponse__status--disabled").hidden = true;
            if (this.menu) {
                this.menu.querySelector(".commentResponse__option--enable").hidden = true;
            }
        }
        async #delete() {
            const result = await (0, Confirmation_1.confirmationFactory)().delete();
            if (result) {
                (await (0, DeleteResponse_1.deleteResponse)(this.responseId)).unwrap();
                UiNotification.show();
                this.dispatchEvent(new CustomEvent("delete"));
            }
        }
        async #startEdit() {
            this.menu.querySelector(".commentResponse__option--edit").hidden = true;
            const { template } = (await (0, EditResponse_1.editResponse)(this.responseId)).unwrap();
            this.#showEditor(template);
        }
        #showEditor(template) {
            this.querySelector(".htmlContent").hidden = true;
            Util_1.default.insertHtml(template, this.#editorContainer, "append");
            const buttonSave = this.querySelector('button[data-type="save"]');
            buttonSave.addEventListener("click", () => {
                void this.#saveEdit();
            });
            const buttonCancel = this.querySelector('button[data-type="cancel"]');
            buttonCancel.addEventListener("click", () => {
                this.#cancelEdit();
            });
            EventHandler.add("com.woltlab.wcf.ckeditor5", `submitEditor_${this.#editorId}`, (data) => {
                data.cancel = true;
                void this.#saveEdit();
            });
            window.setTimeout(() => {
                UiScroll.element(this);
            }, 250);
        }
        async #saveEdit() {
            const ckeditor = (0, Ckeditor_1.getCkeditorById)(this.#editorId);
            const parameters = {
                data: {
                    message: ckeditor.getHtml(),
                },
            };
            if (!this.#validateEdit(parameters)) {
                return;
            }
            EventHandler.fire("com.woltlab.wcf.ckeditor5", `submit_${this.#editorId}`, parameters);
            this.#showLoadingIndicator();
            const response = await (0, UpdateResponse_1.updateResponse)(this.responseId, ckeditor.getHtml());
            if (!response.ok) {
                const validationError = response.error.getValidationError();
                if (validationError === undefined) {
                    throw response.error;
                }
                Util_1.default.innerError(document.getElementById(this.#editorId), validationError.code);
                this.#hideLoadingIndicator();
                return;
            }
            const { template } = (await (0, RenderResponse_1.renderResponse)(this.responseId, true)).unwrap();
            Util_1.default.setInnerHtml(this.querySelector(".htmlContent"), template);
            this.#hideLoadingIndicator();
            this.#cancelEdit();
            UiNotification.show();
        }
        #showLoadingIndicator() {
            let div = this.querySelector(".commentResponse__loading");
            if (!div) {
                div = document.createElement("div");
                div.classList.add("commentResponse__loading");
                div.innerHTML = '<woltlab-core-loading-indicator size="96" hide-text></woltlab-core-loading-indicator>';
                this.querySelector(".commentResponse__message").append(div);
            }
            this.#editorContainer.hidden = true;
            div.hidden = false;
        }
        #hideLoadingIndicator() {
            this.#editorContainer.hidden = false;
            const div = this.querySelector(".commentResponse__loading");
            if (div) {
                div.hidden = true;
            }
        }
        /**
         * Validates the message and invokes listeners to perform additional validation.
         */
        #validateEdit(parameters) {
            this.querySelectorAll(".innerError").forEach((el) => el.remove());
            // check if editor contains actual content
            const editor = (0, Ckeditor_1.getCkeditorById)(this.#editorId);
            if (editor.getHtml() === "") {
                Util_1.default.innerError(editor.element, (0, Language_1.getPhrase)("wcf.global.form.error.empty"));
                return false;
            }
            const data = {
                api: this,
                parameters: parameters,
                valid: true,
            };
            EventHandler.fire("com.woltlab.wcf.ckeditor5", `validate_${this.#editorId}`, data);
            return data.valid;
        }
        #cancelEdit() {
            void (0, Ckeditor_1.getCkeditorById)(this.#editorId).destroy();
            this.#editorContainer.remove();
            this.menu.querySelector(".commentResponse__option--edit").hidden = false;
            this.querySelector(".htmlContent").hidden = false;
        }
        get #editorContainer() {
            let div = this.querySelector(".commentResponse__editor");
            if (!div) {
                div = document.createElement("div");
                div.classList.add("commentResponse__editor");
                this.querySelector(".commentResponse__message").append(div);
            }
            return div;
        }
        get responseId() {
            return parseInt(this.getAttribute("response-id"));
        }
        get menu() {
            let menu = Simple_1.default.getDropdownMenu(`commentResponseOptions${this.responseId}`);
            // The initialization of the menu can taken place after
            // `parsedCallback()` is called.
            if (menu === undefined) {
                menu = this.querySelector(".commentResponse__menu .dropdownMenu") || undefined;
            }
            return menu;
        }
        get #editorId() {
            return `commentResponseEditor${this.responseId}`;
        }
    }
    exports.WoltlabCoreCommentResponseElement = WoltlabCoreCommentResponseElement;
    window.customElements.define("woltlab-core-comment-response", WoltlabCoreCommentResponseElement);
    exports.default = WoltlabCoreCommentResponseElement;
});
