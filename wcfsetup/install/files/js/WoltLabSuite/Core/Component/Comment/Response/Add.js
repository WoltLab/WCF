/**
 * Handles the reply feature in the comment list.
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Component/Comment/Response/Add
 * @since 6.0
 */
define(["require", "exports", "tslib", "../../../Dom/Util", "../../../Language", "../../../Event/Handler", "../../../Ui/Scroll", "../../../Ajax", "../../../Core", "../../../Ui/Notification", "../../../Ajax/Error", "../GuestDialog"], function (require, exports, tslib_1, Util_1, Language_1, EventHandler, UiScroll, Ajax_1, Core, UiNotification, Error_1, GuestDialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.CommentResponseAdd = void 0;
    Util_1 = tslib_1.__importDefault(Util_1);
    EventHandler = tslib_1.__importStar(EventHandler);
    UiScroll = tslib_1.__importStar(UiScroll);
    Core = tslib_1.__importStar(Core);
    UiNotification = tslib_1.__importStar(UiNotification);
    class CommentResponseAdd {
        #container;
        #content;
        #textarea;
        #callback;
        #messageCache = new Map();
        #editor = null;
        #commentId;
        constructor(container, callback) {
            this.#container = container;
            this.#content = this.#container.querySelector(".commentResponseAdd__content");
            this.#textarea = this.#container.querySelector(".wysiwygTextarea");
            this.#callback = callback;
            this.#initEvents();
        }
        #initEvents() {
            const submitButton = this.#container.querySelector('button[data-type="save"]');
            submitButton.addEventListener("click", (event) => {
                event.preventDefault();
                void this.#submit();
            });
        }
        show(commentId) {
            if (this.#commentId) {
                this.#messageCache.set(this.#commentId, this.#getContent());
            }
            this.#setContent(this.#messageCache.get(commentId) || "");
            this.#commentId = commentId;
            this.#container.hidden = false;
        }
        /**
         * Validates the message and invokes listeners to perform additional validation.
         */
        #validate() {
            // remove all existing error elements
            this.#container.querySelectorAll(".innerError").forEach((el) => el.remove());
            // check if editor contains actual content
            if (this.#getEditor().utils.isEmpty()) {
                this.#throwError(this.#textarea, (0, Language_1.getPhrase)("wcf.global.form.error.empty"));
                return false;
            }
            const data = {
                api: this,
                editor: this.#getEditor(),
                message: this.#getEditor().code.get(),
                valid: true,
            };
            EventHandler.fire("com.woltlab.wcf.redactor2", "validate_text", data);
            return data.valid;
        }
        /**
         * Validates the message and submits it to the server.
         */
        async #submit(additionalParameters = {}) {
            if (!this.#validate()) {
                return;
            }
            this.#showLoadingOverlay();
            const parameters = this.#getParameters();
            EventHandler.fire("com.woltlab.wcf.redactor2", "submit_text", parameters.data);
            let response;
            try {
                response = (await (0, Ajax_1.dboAction)("addResponse", "wcf\\data\\comment\\CommentAction")
                    .objectIds([this.#commentId])
                    .payload(Core.extend(parameters, additionalParameters))
                    .disableLoadingIndicator()
                    .dispatch());
            }
            catch (error) {
                if (error instanceof Error_1.StatusNotOk) {
                    const json = await error.response.clone().json();
                    if (json.code === 412 && json.returnValues) {
                        this.#throwError(this.#textarea, json.returnValues.errorType);
                    }
                }
                else {
                    throw error;
                }
                this.#hideLoadingOverlay();
                return;
            }
            if (response.guestDialog) {
                const additionalParameters = await (0, GuestDialog_1.showGuestDialog)(response.guestDialog);
                if (additionalParameters === undefined) {
                    this.#hideLoadingOverlay();
                }
                else {
                    void this.#submit(additionalParameters);
                }
                return;
            }
            this.#callback(this.#commentId, response.template);
            UiNotification.show((0, Language_1.getPhrase)("wcf.global.success.add"));
            this.#reset();
            this.#hideLoadingOverlay();
        }
        /**
         * Resets the editor contents and notifies event listeners.
         */
        #reset() {
            this.#getEditor().code.set("<p>\u200b</p>");
            EventHandler.fire("com.woltlab.wcf.redactor2", "reset_text");
            if (document.activeElement instanceof HTMLElement) {
                document.activeElement.blur();
            }
            this.#messageCache.delete(this.#commentId);
            this.#container.hidden = true;
        }
        /**
         * Throws an error by adding an inline error to target element.
         */
        #throwError(element, message) {
            Util_1.default.innerError(element, message === "empty" ? (0, Language_1.getPhrase)("wcf.global.form.error.empty") : message);
        }
        /**
         * Returns the current editor instance.
         */
        #getEditor() {
            if (this.#editor === null) {
                if (typeof window.jQuery === "function") {
                    this.#editor = window.jQuery(this.#textarea).data("redactor");
                }
                else {
                    throw new Error("Unable to access editor, jQuery has not been loaded yet.");
                }
            }
            return this.#editor;
        }
        /**
         * Retrieves the current content from the editor.
         */
        #getContent() {
            return window.jQuery(this.#textarea).redactor("code.get");
        }
        /**
         * Sets the content and places the caret at the end of the editor.
         */
        #setContent(html) {
            window.jQuery(this.#textarea).redactor("code.set", html);
            window.jQuery(this.#textarea).redactor("WoltLabCaret.endOfEditor");
            // the error message can appear anywhere in the container, not exclusively after the textarea
            const innerError = this.#textarea.parentElement.querySelector(".innerError");
            if (innerError !== null) {
                innerError.remove();
            }
            this.#focusEditor();
        }
        /**
         * Scrolls the editor into view and sets the caret to the end of the editor.
         */
        #focusEditor() {
            window.setTimeout(() => {
                UiScroll.element(this.#container, () => {
                    const element = window.jQuery(this.#textarea);
                    const editor = element.redactor("core.editor")[0];
                    if (editor !== document.activeElement) {
                        element.redactor("WoltLabCaret.endOfEditor");
                    }
                });
            }, 0);
        }
        /**
         * Returns the request parameters to add a response.
         */
        #getParameters() {
            return {
                data: {
                    message: this.#getEditor().code.get(),
                },
            };
        }
        /**
         * Displays a loading spinner while the request is processed by the server.
         */
        #showLoadingOverlay() {
            if (this.#content.classList.contains("commentResponseAdd__content--loading")) {
                return;
            }
            const loadingOverlay = document.createElement("div");
            loadingOverlay.className = "commentResponseAdd__loading";
            loadingOverlay.innerHTML = '<fa-icon size="96" name="spinner" solid></fa-icon>';
            this.#content.classList.add("commentResponseAdd__content--loading");
            this.#content.appendChild(loadingOverlay);
        }
        /**
         * Hides the loading spinner.
         */
        #hideLoadingOverlay() {
            this.#content.classList.remove("commentResponseAdd__content--loading");
            const loadingOverlay = this.#content.querySelector(".commentResponseAdd__loading");
            if (loadingOverlay !== null) {
                loadingOverlay.remove();
            }
        }
    }
    exports.CommentResponseAdd = CommentResponseAdd;
});
