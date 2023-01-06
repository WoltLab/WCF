/**
 * The `<woltlab-core-comment-response>` element represents a response in the comment list.
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports", "tslib", "../../../Ajax", "../../../Dom/Util", "../../../Ui/Dropdown/Simple", "../../../Ui/Notification", "../../Confirmation", "../../../Environment", "../../../Event/Handler", "../../../Ui/Scroll", "../../../Ajax/Error", "../../../Language"], function (require, exports, tslib_1, Ajax_1, Util_1, Simple_1, UiNotification, Confirmation_1, Environment, EventHandler, UiScroll, Error_1, Language_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.WoltlabCoreCommentResponseElement = void 0;
    Util_1 = tslib_1.__importDefault(Util_1);
    Simple_1 = tslib_1.__importDefault(Simple_1);
    UiNotification = tslib_1.__importStar(UiNotification);
    Environment = tslib_1.__importStar(Environment);
    EventHandler = tslib_1.__importStar(EventHandler);
    UiScroll = tslib_1.__importStar(UiScroll);
    class WoltlabCoreCommentResponseElement extends HTMLElement {
        connectedCallback() {
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
            await (0, Ajax_1.dboAction)("enable", "wcf\\data\\comment\\response\\CommentResponseAction")
                .objectIds([this.responseId])
                .dispatch();
            this.querySelector(".commentResponse__status--disabled").hidden = true;
            if (this.menu) {
                this.menu.querySelector(".commentResponse__option--enable").hidden = true;
            }
        }
        async #delete() {
            const result = await (0, Confirmation_1.confirmationFactory)().delete("todo");
            if (result) {
                await (0, Ajax_1.dboAction)("delete", "wcf\\data\\comment\\response\\CommentResponseAction")
                    .objectIds([this.responseId])
                    .dispatch();
                UiNotification.show();
                this.dispatchEvent(new CustomEvent("delete"));
            }
        }
        async #startEdit() {
            this.menu.querySelector(".commentResponse__option--edit").hidden = true;
            const { template } = (await (0, Ajax_1.dboAction)("beginEdit", "wcf\\data\\comment\\response\\CommentResponseAction")
                .objectIds([this.responseId])
                .dispatch());
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
            EventHandler.add("com.woltlab.wcf.redactor", `submitEditor_${this.#editorId}`, (data) => {
                data.cancel = true;
                void this.#saveEdit();
            });
            const editorElement = document.getElementById(this.#editorId);
            if (Environment.editor() === "redactor") {
                window.setTimeout(() => {
                    UiScroll.element(this);
                }, 250);
            }
            else {
                editorElement.focus();
            }
        }
        async #saveEdit() {
            const parameters = {
                data: {
                    message: "",
                },
            };
            EventHandler.fire("com.woltlab.wcf.redactor2", `getText_${this.#editorId}`, parameters.data);
            if (!this.#validateEdit(parameters)) {
                return;
            }
            EventHandler.fire("com.woltlab.wcf.redactor2", `submit_${this.#editorId}`, parameters);
            this.#showLoadingIndicator();
            let response;
            try {
                response = (await (0, Ajax_1.dboAction)("save", "wcf\\data\\comment\\response\\CommentResponseAction")
                    .objectIds([this.responseId])
                    .payload(parameters)
                    .dispatch());
            }
            catch (error) {
                if (error instanceof Error_1.StatusNotOk) {
                    const json = await error.response.json();
                    if (json.code === 412 && json.returnValues) {
                        Util_1.default.innerError(document.getElementById(this.#editorId), json.returnValues.errorType);
                    }
                }
                else {
                    throw error;
                }
                this.#hideLoadingIndicator();
                return;
            }
            Util_1.default.setInnerHtml(this.querySelector(".htmlContent"), response.message);
            this.#hideLoadingIndicator();
            this.#cancelEdit();
            UiNotification.show();
        }
        #showLoadingIndicator() {
            // todo
        }
        #hideLoadingIndicator() {
            // todo
        }
        /**
         * Validates the message and invokes listeners to perform additional validation.
         */
        #validateEdit(parameters) {
            this.querySelectorAll(".innerError").forEach((el) => el.remove());
            // check if editor contains actual content
            const editorElement = document.getElementById(this.#editorId);
            const redactor = window.jQuery(editorElement).data("redactor");
            if (redactor.utils.isEmpty()) {
                Util_1.default.innerError(editorElement, (0, Language_1.getPhrase)("wcf.global.form.error.empty"));
                return false;
            }
            const data = {
                api: this,
                parameters: parameters,
                valid: true,
            };
            EventHandler.fire("com.woltlab.wcf.redactor2", `validate_${this.#editorId}`, data);
            return data.valid;
        }
        #cancelEdit() {
            this.#destroyEditor();
            this.#editorContainer.remove();
            this.menu.querySelector(".commentResponse__option--edit").hidden = false;
            this.querySelector(".htmlContent").hidden = false;
        }
        #destroyEditor() {
            EventHandler.fire("com.woltlab.wcf.redactor2", `autosaveDestroy_${this.#editorId}`);
            EventHandler.fire("com.woltlab.wcf.redactor2", `destroy_${this.#editorId}`);
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
            return Simple_1.default.getDropdownMenu(`commentResponseOptions${this.responseId}`);
        }
        get #editorId() {
            return `commentResponseEditor${this.responseId}`;
        }
    }
    exports.WoltlabCoreCommentResponseElement = WoltlabCoreCommentResponseElement;
    window.customElements.define("woltlab-core-comment-response", WoltlabCoreCommentResponseElement);
    exports.default = WoltlabCoreCommentResponseElement;
});
