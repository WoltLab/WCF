/**
 * The `<woltlab-core-comment>` element represents a comment in the comment list.
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports", "tslib", "../../Dom/Util", "../../Ui/Dropdown/Simple", "../../Ui/Notification", "../Confirmation", "../../Ui/Scroll", "../../Event/Handler", "../../Language", "../Ckeditor", "WoltLabSuite/Core/Api/Comments/DeleteComment", "WoltLabSuite/Core/Api/Comments/EnableComment", "WoltLabSuite/Core/Api/Comments/EditComment", "WoltLabSuite/Core/Api/Comments/UpdateComment", "WoltLabSuite/Core/Api/Comments/RenderComment"], function (require, exports, tslib_1, Util_1, Simple_1, UiNotification, Confirmation_1, UiScroll, EventHandler, Language_1, Ckeditor_1, DeleteComment_1, EnableComment_1, EditComment_1, UpdateComment_1, RenderComment_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.WoltlabCoreCommentElement = void 0;
    Util_1 = tslib_1.__importDefault(Util_1);
    Simple_1 = tslib_1.__importDefault(Simple_1);
    UiNotification = tslib_1.__importStar(UiNotification);
    UiScroll = tslib_1.__importStar(UiScroll);
    EventHandler = tslib_1.__importStar(EventHandler);
    class WoltlabCoreCommentElement extends HTMLParsedElement {
        parsedCallback() {
            if (this.menu) {
                const enableButton = this.menu.querySelector(".comment__option--enable");
                enableButton?.addEventListener("click", (event) => {
                    event.preventDefault();
                    void this.#enable();
                });
                const deleteButton = this.menu.querySelector(".comment__option--delete");
                deleteButton?.addEventListener("click", (event) => {
                    event.preventDefault();
                    void this.#delete();
                });
                const editButton = this.menu.querySelector(".comment__option--edit");
                editButton?.addEventListener("click", (event) => {
                    event.preventDefault();
                    void this.#startEdit();
                });
            }
            const replyButton = this.querySelector(".comment__button--reply");
            replyButton?.addEventListener("click", () => {
                this.dispatchEvent(new CustomEvent("reply"));
            });
        }
        async #enable() {
            (await (0, EnableComment_1.enableComment)(this.commentId)).unwrap();
            this.querySelector(".comment__status--disabled").hidden = true;
            if (this.menu) {
                this.menu.querySelector(".comment__option--enable").hidden = true;
            }
        }
        async #delete() {
            const result = await (0, Confirmation_1.confirmationFactory)().delete();
            if (result) {
                (await (0, DeleteComment_1.deleteComment)(this.commentId)).unwrap();
                UiNotification.show();
                this.dispatchEvent(new CustomEvent("delete"));
            }
        }
        async #startEdit() {
            this.menu.querySelector(".comment__option--edit").hidden = true;
            const { template } = (await (0, EditComment_1.editComment)(this.commentId)).unwrap();
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
            const response = await (0, UpdateComment_1.updateComment)(this.commentId, ckeditor.getHtml());
            if (!response.ok) {
                const validationError = response.error.getValidationError();
                if (validationError === undefined) {
                    throw new Error("Unexpected validation error", { cause: response.error });
                }
                Util_1.default.innerError(document.getElementById(this.#editorId), validationError.code);
                this.#hideLoadingIndicator();
                return;
            }
            const { template } = (await (0, RenderComment_1.renderComment)(this.commentId, undefined, true)).unwrap();
            Util_1.default.setInnerHtml(this.querySelector(".htmlContent"), template);
            this.#hideLoadingIndicator();
            this.#cancelEdit();
            UiNotification.show();
        }
        #showLoadingIndicator() {
            let div = this.querySelector(".comment__loading");
            if (!div) {
                div = document.createElement("div");
                div.classList.add("comment__loading");
                div.innerHTML = '<woltlab-core-loading-indicator size="96" hide-text></woltlab-core-loading-indicator>';
                this.querySelector(".comment__message").append(div);
            }
            this.#editorContainer.hidden = true;
            div.hidden = false;
        }
        #hideLoadingIndicator() {
            this.#editorContainer.hidden = false;
            const div = this.querySelector(".comment__loading");
            if (div) {
                div.hidden = true;
            }
        }
        /**
         * Validates the message and invokes listeners to perform additional validation.
         */
        #validateEdit(parameters) {
            this.querySelectorAll(".innerError").forEach((el) => el.remove());
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
            this.menu.querySelector(".comment__option--edit").hidden = false;
            this.querySelector(".htmlContent").hidden = false;
        }
        get #editorContainer() {
            let div = this.querySelector(".comment__editor");
            if (!div) {
                div = document.createElement("div");
                div.classList.add("comment__editor");
                this.querySelector(".comment__message").append(div);
            }
            return div;
        }
        get commentId() {
            return parseInt(this.getAttribute("comment-id"));
        }
        get menu() {
            let menu = Simple_1.default.getDropdownMenu(`commentOptions${this.commentId}`);
            // The initialization of the menu can taken place after
            // `parsedCallback()` is called.
            if (menu === undefined) {
                menu = this.querySelector(".comment__menu .dropdownMenu") || undefined;
            }
            return menu;
        }
        get #editorId() {
            return `commentEditor${this.commentId}`;
        }
    }
    exports.WoltlabCoreCommentElement = WoltlabCoreCommentElement;
    window.customElements.define("woltlab-core-comment", WoltlabCoreCommentElement);
    exports.default = WoltlabCoreCommentElement;
});
