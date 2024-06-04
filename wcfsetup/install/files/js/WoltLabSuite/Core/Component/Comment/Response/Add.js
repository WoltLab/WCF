/**
 * Handles the reply feature in the comment list.
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports", "tslib", "../../../Dom/Util", "../../../Language", "../../../Event/Handler", "../../../Ui/Scroll", "../../../Ajax", "../../../Core", "../../../Ui/Notification", "../../../Ajax/Error", "../GuestDialog", "../../Ckeditor", "../../Ckeditor/Event"], function (require, exports, tslib_1, Util_1, Language_1, EventHandler, UiScroll, Ajax_1, Core, UiNotification, Error_1, GuestDialog_1, Ckeditor_1, Event_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.CommentResponseAdd = void 0;
    Util_1 = tslib_1.__importDefault(Util_1);
    EventHandler = tslib_1.__importStar(EventHandler);
    UiScroll = tslib_1.__importStar(UiScroll);
    Core = tslib_1.__importStar(Core);
    UiNotification = tslib_1.__importStar(UiNotification);
    class CommentResponseAdd {
        container;
        #content;
        #textarea;
        #callback;
        #messageCache = new Map();
        #editor;
        #commentId;
        constructor(container, callback) {
            this.container = container;
            this.#content = this.container.querySelector(".commentResponseAdd__content");
            this.#textarea = this.container.querySelector(".wysiwygTextarea");
            this.#callback = callback;
            this.#initEvents();
        }
        #initEvents() {
            const submitButton = this.container.querySelector('button[data-type="save"]');
            submitButton.addEventListener("click", (event) => {
                event.preventDefault();
                void this.#submit();
            });
            (0, Event_1.listenToCkeditor)(this.#textarea).setupFeatures(({ features }) => {
                features.heading = false;
                features.spoiler = false;
                features.table = false;
            });
        }
        show(commentId) {
            if (this.#commentId) {
                this.#messageCache.set(this.#commentId, this.#getContent());
            }
            this.#setContent(this.#messageCache.get(commentId) || "");
            this.#commentId = commentId;
            this.container.hidden = false;
        }
        /**
         * Validates the message and invokes listeners to perform additional validation.
         */
        #validate() {
            // remove all existing error elements
            this.container.querySelectorAll(".innerError").forEach((el) => el.remove());
            const message = this.#getEditor().getHtml();
            if (message === "") {
                this.#throwError(this.#getEditor().element, (0, Language_1.getPhrase)("wcf.global.form.error.empty"));
                return false;
            }
            const data = {
                api: this,
                editor: this.#getEditor(),
                message,
                valid: true,
            };
            EventHandler.fire("com.woltlab.wcf.ckeditor5", "validate_text", data);
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
            EventHandler.fire("com.woltlab.wcf.ckeditor", "submit_text", parameters.data);
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
                        this.#throwError(this.#getEditor().element, json.returnValues.errorType);
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
            this.#getEditor().reset();
            if (document.activeElement instanceof HTMLElement) {
                document.activeElement.blur();
            }
            this.#messageCache.delete(this.#commentId);
            this.container.hidden = true;
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
            if (this.#editor === undefined) {
                this.#editor = (0, Ckeditor_1.getCkeditor)(this.#textarea);
            }
            return this.#editor;
        }
        /**
         * Retrieves the current content from the editor.
         */
        #getContent() {
            return this.#getEditor().getHtml();
        }
        /**
         * Sets the content and places the caret at the end of the editor.
         */
        #setContent(html) {
            this.#getEditor().setHtml(html);
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
                UiScroll.element(this.container, () => {
                    this.#getEditor().focus();
                });
            }, 0);
        }
        /**
         * Returns the request parameters to add a response.
         */
        #getParameters() {
            return {
                data: {
                    message: this.#getContent(),
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
            loadingOverlay.innerHTML = '<woltlab-core-loading-indicator size="96" hide-text></woltlab-core-loading-indicator>';
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
