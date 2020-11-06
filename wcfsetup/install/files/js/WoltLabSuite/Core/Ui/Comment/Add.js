/**
 * Handles the comment add feature.
 *
 * Warning: This implementation is also used for responses, but in a slightly
 *          modified version. Changes made to this class need to be verified
 *          against the response implementation.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Comment/Add
 */
define(["require", "exports", "tslib", "../../Ajax", "../../Controller/Captcha", "../../Core", "../../Dom/Change/Listener", "../../Dom/Util", "../../Event/Handler", "../../Language", "../Dialog", "../Scroll", "../../User", "../Notification"], function (require, exports, tslib_1, Ajax, Captcha_1, Core, Listener_1, Util_1, EventHandler, Language, Dialog_1, UiScroll, User_1, UiNotification) {
    "use strict";
    Ajax = tslib_1.__importStar(Ajax);
    Captcha_1 = tslib_1.__importDefault(Captcha_1);
    Core = tslib_1.__importStar(Core);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    EventHandler = tslib_1.__importStar(EventHandler);
    Language = tslib_1.__importStar(Language);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    UiScroll = tslib_1.__importStar(UiScroll);
    User_1 = tslib_1.__importDefault(User_1);
    UiNotification = tslib_1.__importStar(UiNotification);
    class UiCommentAdd {
        /**
         * Initializes a new quick reply field.
         */
        constructor(container) {
            this._editor = null;
            this._loadingOverlay = null;
            this._container = container;
            this._content = this._container.querySelector(".jsOuterEditorContainer");
            this._textarea = this._container.querySelector(".wysiwygTextarea");
            this._content.addEventListener("click", (event) => {
                if (this._content.classList.contains("collapsed")) {
                    event.preventDefault();
                    this._content.classList.remove("collapsed");
                    this._focusEditor();
                }
            });
            // handle submit button
            const submitButton = this._container.querySelector('button[data-type="save"]');
            submitButton.addEventListener("click", (ev) => this._submit(ev));
        }
        /**
         * Scrolls the editor into view and sets the caret to the end of the editor.
         */
        _focusEditor() {
            UiScroll.element(this._container, () => {
                window.jQuery(this._textarea).redactor("WoltLabCaret.endOfEditor");
            });
        }
        /**
         * Submits the guest dialog.
         */
        _submitGuestDialog(event) {
            // only submit when enter key is pressed
            if (event instanceof KeyboardEvent && event.key !== "Enter") {
                return;
            }
            const target = event.currentTarget;
            const dialogContent = target.closest(".dialogContent");
            const usernameInput = dialogContent.querySelector("input[name=username]");
            if (usernameInput.value === "") {
                Util_1.default.innerError(usernameInput, Language.get("wcf.global.form.error.empty"));
                usernameInput.closest("dl").classList.add("formError");
                return;
            }
            let parameters = {
                parameters: {
                    data: {
                        username: usernameInput.value,
                    },
                },
            };
            if (Captcha_1.default.has("commentAdd")) {
                const data = Captcha_1.default.getData("commentAdd");
                if (data instanceof Promise) {
                    void data.then((data) => {
                        parameters = Core.extend(parameters, data);
                        this._submit(undefined, parameters);
                    });
                }
                else {
                    parameters = Core.extend(parameters, data);
                    this._submit(undefined, parameters);
                }
            }
            else {
                this._submit(undefined, parameters);
            }
        }
        /**
         * Validates the message and submits it to the server.
         */
        _submit(event, additionalParameters) {
            if (event) {
                event.preventDefault();
            }
            if (!this._validate()) {
                // validation failed, bail out
                return;
            }
            this._showLoadingOverlay();
            // build parameters
            const parameters = this._getParameters();
            EventHandler.fire("com.woltlab.wcf.redactor2", "submit_text", parameters.data);
            if (!User_1.default.userId && !additionalParameters) {
                parameters.requireGuestDialog = true;
            }
            Ajax.api(this, Core.extend({
                parameters: parameters,
            }, additionalParameters));
        }
        /**
         * Returns the request parameters to add a comment.
         */
        _getParameters() {
            const commentList = this._container.closest(".commentList");
            return {
                data: {
                    message: this._getEditor().code.get(),
                    objectID: ~~commentList.dataset.objectId,
                    objectTypeID: ~~commentList.dataset.objectTypeId,
                },
            };
        }
        /**
         * Validates the message and invokes listeners to perform additional validation.
         */
        _validate() {
            // remove all existing error elements
            this._container.querySelectorAll(".innerError").forEach((el) => el.remove());
            // check if editor contains actual content
            if (this._getEditor().utils.isEmpty()) {
                this.throwError(this._textarea, Language.get("wcf.global.form.error.empty"));
                return false;
            }
            const data = {
                api: this,
                editor: this._getEditor(),
                message: this._getEditor().code.get(),
                valid: true,
            };
            EventHandler.fire("com.woltlab.wcf.redactor2", "validate_text", data);
            return data.valid;
        }
        /**
         * Throws an error by adding an inline error to target element.
         */
        throwError(element, message) {
            Util_1.default.innerError(element, message === "empty" ? Language.get("wcf.global.form.error.empty") : message);
        }
        /**
         * Displays a loading spinner while the request is processed by the server.
         */
        _showLoadingOverlay() {
            if (this._loadingOverlay === null) {
                this._loadingOverlay = document.createElement("div");
                this._loadingOverlay.className = "commentLoadingOverlay";
                this._loadingOverlay.innerHTML = '<span class="icon icon96 fa-spinner"></span>';
            }
            this._content.classList.add("loading");
            this._content.appendChild(this._loadingOverlay);
        }
        /**
         * Hides the loading spinner.
         */
        _hideLoadingOverlay() {
            this._content.classList.remove("loading");
            const loadingOverlay = this._content.querySelector(".commentLoadingOverlay");
            if (loadingOverlay !== null) {
                loadingOverlay.remove();
            }
        }
        /**
         * Resets the editor contents and notifies event listeners.
         */
        _reset() {
            this._getEditor().code.set("<p>\u200b</p>");
            EventHandler.fire("com.woltlab.wcf.redactor2", "reset_text");
            if (document.activeElement instanceof HTMLElement) {
                document.activeElement.blur();
            }
            this._content.classList.add("collapsed");
        }
        /**
         * Handles errors occurred during server processing.
         */
        _handleError(data) {
            this.throwError(this._textarea, data.returnValues.errorType);
        }
        /**
         * Returns the current editor instance.
         */
        _getEditor() {
            if (this._editor === null) {
                if (typeof window.jQuery === "function") {
                    this._editor = window.jQuery(this._textarea).data("redactor");
                }
                else {
                    throw new Error("Unable to access editor, jQuery has not been loaded yet.");
                }
            }
            return this._editor;
        }
        /**
         * Inserts the rendered message.
         */
        _insertMessage(data) {
            // insert HTML
            Util_1.default.insertHtml(data.returnValues.template, this._container, "after");
            UiNotification.show(Language.get("wcf.global.success.add"));
            Listener_1.default.trigger();
            return this._container.nextElementSibling;
        }
        _ajaxSuccess(data) {
            if (!User_1.default.userId && data.returnValues.guestDialog) {
                Dialog_1.default.openStatic("jsDialogGuestComment", data.returnValues.guestDialog, {
                    closable: false,
                    onClose: () => {
                        if (Captcha_1.default.has("commentAdd")) {
                            Captcha_1.default.delete("commentAdd");
                        }
                    },
                    title: Language.get("wcf.global.confirmation.title"),
                });
                const dialog = Dialog_1.default.getDialog("jsDialogGuestComment");
                const submitButton = dialog.content.querySelector("input[type=submit]");
                submitButton.addEventListener("click", (ev) => this._submitGuestDialog(ev));
                const cancelButton = dialog.content.querySelector('button[data-type="cancel"]');
                cancelButton.addEventListener("click", () => this._cancelGuestDialog());
                const input = dialog.content.querySelector("input[type=text]");
                input.addEventListener("keypress", (ev) => this._submitGuestDialog(ev));
            }
            else {
                const scrollTarget = this._insertMessage(data);
                if (!User_1.default.userId) {
                    Dialog_1.default.close("jsDialogGuestComment");
                }
                this._reset();
                this._hideLoadingOverlay();
                window.setTimeout(() => {
                    UiScroll.element(scrollTarget);
                }, 100);
            }
        }
        _ajaxFailure(data) {
            this._hideLoadingOverlay();
            if (data === null || data.returnValues === undefined || data.returnValues.errorType === undefined) {
                return true;
            }
            this._handleError(data);
            return false;
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: "addComment",
                    className: "wcf\\data\\comment\\CommentAction",
                },
                silent: true,
            };
        }
        /**
         * Cancels the guest dialog and restores the comment editor.
         */
        _cancelGuestDialog() {
            Dialog_1.default.close("jsDialogGuestComment");
            this._hideLoadingOverlay();
        }
    }
    Core.enableLegacyInheritance(UiCommentAdd);
    return UiCommentAdd;
});
