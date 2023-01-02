define(["require", "exports", "tslib", "../../Ajax", "../../Ui/Scroll", "../../Ui/Notification", "../../Language", "../../Event/Handler", "../../Dom/Util", "../../Dom/Change/Listener", "./GuestDialog", "../../Core", "../../Ajax/Error"], function (require, exports, tslib_1, Ajax_1, UiScroll, UiNotification, Language_1, EventHandler, Util_1, Listener_1, GuestDialog_1, Core, Error_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.CommentAdd = void 0;
    UiScroll = tslib_1.__importStar(UiScroll);
    UiNotification = tslib_1.__importStar(UiNotification);
    EventHandler = tslib_1.__importStar(EventHandler);
    Util_1 = tslib_1.__importDefault(Util_1);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    Core = tslib_1.__importStar(Core);
    class CommentAdd {
        #container;
        #content;
        #textarea;
        #editor = null;
        #loadingOverlay = null;
        constructor(container) {
            this.#container = container;
            this.#content = this.#container.querySelector(".commentAdd__content");
            this.#textarea = this.#container.querySelector(".wysiwygTextarea");
            this.#initEvents();
        }
        #initEvents() {
            this.#content.addEventListener("click", (event) => {
                if (this.#content.classList.contains("commentAdd__content--collapsed")) {
                    event.preventDefault();
                    this.#content.classList.remove("commentAdd__content--collapsed");
                    this.#focusEditor();
                }
            });
            const submitButton = this.#container.querySelector('button[data-type="save"]');
            submitButton.addEventListener("click", (event) => {
                event.preventDefault();
                void this.#submit();
            });
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
                response = (await (0, Ajax_1.dboAction)("addComment", "wcf\\data\\comment\\CommentAction")
                    .payload(Core.extend(parameters, additionalParameters))
                    .disableLoadingIndicator()
                    .dispatch());
            }
            catch (error) {
                if (error instanceof Error_1.StatusNotOk) {
                    const json = await error.response.json();
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
            const scrollTarget = this.#insertMessage(response.template);
            this.#reset();
            this.#hideLoadingOverlay();
            window.setTimeout(() => {
                UiScroll.element(scrollTarget);
            }, 100);
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
         * Displays a loading spinner while the request is processed by the server.
         */
        #showLoadingOverlay() {
            if (this.#content.classList.contains("commentAdd__content--loading")) {
                return;
            }
            if (this.#loadingOverlay === null) {
                this.#loadingOverlay = document.createElement("div");
                this.#loadingOverlay.className = "commentAdd__loading";
                this.#loadingOverlay.innerHTML = '<fa-icon size="96" name="spinner" solid></fa-icon>';
            }
            this.#content.classList.add("commentAdd__content--loading");
            this.#content.appendChild(this.#loadingOverlay);
        }
        /**
         * Throws an error by adding an inline error to target element.
         */
        #throwError(element, message) {
            Util_1.default.innerError(element, message === "empty" ? (0, Language_1.getPhrase)("wcf.global.form.error.empty") : message);
        }
        /**
         * Returns the request parameters to add a comment.
         */
        #getParameters() {
            const commentList = this.#container.closest(".commentList");
            return {
                data: {
                    message: this.#getEditor().code.get(),
                    objectID: ~~commentList.dataset.objectId,
                    objectTypeID: ~~commentList.dataset.objectTypeId,
                },
            };
        }
        /**
         * Inserts the rendered message.
         */
        #insertMessage(template) {
            Util_1.default.insertHtml(template, this.#container, "after");
            UiNotification.show((0, Language_1.getPhrase)("wcf.global.success.add"));
            Listener_1.default.trigger();
            return this.#container.nextElementSibling;
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
            this.#content.classList.add("commentAdd__content--collapsed");
        }
        /**
         * Hides the loading spinner.
         */
        #hideLoadingOverlay() {
            this.#content.classList.remove("commentAdd__content--loading");
            const loadingOverlay = this.#content.querySelector(".commentAdd__loading");
            if (loadingOverlay !== null) {
                loadingOverlay.remove();
            }
        }
    }
    exports.CommentAdd = CommentAdd;
});
