/**
 * Handles user interaction with the quick reply feature.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Message/Reply
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../../Ajax", "../../Core", "../../Event/Handler", "../../Language", "../../Dom/Change/Listener", "../../Dom/Util", "../Dialog", "../Notification", "../../User", "../../Controller/Captcha", "../Scroll"], function (require, exports, tslib_1, Ajax, Core, EventHandler, Language, Listener_1, Util_1, Dialog_1, UiNotification, User_1, Captcha_1, UiScroll) {
    "use strict";
    Ajax = tslib_1.__importStar(Ajax);
    Core = tslib_1.__importStar(Core);
    EventHandler = tslib_1.__importStar(EventHandler);
    Language = tslib_1.__importStar(Language);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    UiNotification = tslib_1.__importStar(UiNotification);
    User_1 = tslib_1.__importDefault(User_1);
    Captcha_1 = tslib_1.__importDefault(Captcha_1);
    UiScroll = tslib_1.__importStar(UiScroll);
    class UiMessageReply {
        /**
         * Initializes a new quick reply field.
         */
        constructor(opts) {
            this._editor = null;
            this._guestDialogId = "";
            this._loadingOverlay = null;
            this._options = Core.extend({
                ajax: {
                    className: "",
                },
                quoteManager: null,
                successMessage: "wcf.global.success.add",
            }, opts);
            this._container = document.getElementById("messageQuickReply");
            this._content = this._container.querySelector(".messageContent");
            this._textarea = document.getElementById("text");
            // prevent marking of text for quoting
            this._container.querySelector(".message").classList.add("jsInvalidQuoteTarget");
            // handle submit button
            const submitButton = this._container.querySelector('button[data-type="save"]');
            submitButton.addEventListener("click", (ev) => this._submit(ev));
            // bind reply button
            document.querySelectorAll(".jsQuickReply").forEach((replyButton) => {
                replyButton.addEventListener("click", (event) => {
                    event.preventDefault();
                    this._getEditor().WoltLabReply.showEditor();
                    UiScroll.element(this._container, () => {
                        this._getEditor().WoltLabCaret.endOfEditor();
                    });
                });
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
            const captchaId = target.dataset.captchaId;
            if (Captcha_1.default.has(captchaId)) {
                const data = Captcha_1.default.getData(captchaId);
                Captcha_1.default.delete(captchaId);
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
            // Ignore requests to submit the message while a previous request is still pending.
            if (this._content.classList.contains("loading")) {
                if (!this._guestDialogId || !Dialog_1.default.isOpen(this._guestDialogId)) {
                    return;
                }
            }
            if (!this._validate()) {
                // validation failed, bail out
                return;
            }
            this._showLoadingOverlay();
            // build parameters
            const parameters = {};
            Object.entries(this._container.dataset).forEach(([key, value]) => {
                parameters[key.replace(/Id$/, "ID")] = value;
            });
            parameters.data = { message: this._getEditor().code.get() };
            parameters.removeQuoteIDs = this._options.quoteManager
                ? this._options.quoteManager.getQuotesMarkedForRemoval()
                : [];
            // add any available settings
            const settingsContainer = document.getElementById("settings_text");
            if (settingsContainer) {
                settingsContainer
                    .querySelectorAll("input, select, textarea")
                    .forEach((element) => {
                    if (element.nodeName === "INPUT" && (element.type === "checkbox" || element.type === "radio")) {
                        if (!element.checked) {
                            return;
                        }
                    }
                    const name = element.name;
                    if (Object.prototype.hasOwnProperty.call(parameters, name)) {
                        throw new Error(`Variable overshadowing, key '${name}' is already present.`);
                    }
                    parameters[name] = element.value.trim();
                });
            }
            EventHandler.fire("com.woltlab.wcf.redactor2", "submit_text", parameters.data);
            if (!User_1.default.userId && !additionalParameters) {
                parameters.requireGuestDialog = true;
            }
            Ajax.api(this, Core.extend({
                parameters: parameters,
            }, additionalParameters));
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
         *
         * @param       {Element}       element         erroneous element
         * @param       {string}        message         error message
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
                this._loadingOverlay.className = "messageContentLoadingOverlay";
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
            const loadingOverlay = this._content.querySelector(".messageContentLoadingOverlay");
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
            // Opera on Android does not properly blur the editor after submitting the message,
            // causing the keyboard to vanish, but the focus remains inside the editor.
            window.setTimeout(() => {
                var _a;
                const editor = (_a = document.activeElement) === null || _a === void 0 ? void 0 : _a.closest(".redactor-layer");
                if (editor && editor instanceof HTMLElement) {
                    editor.blur();
                }
            }, 50);
        }
        /**
         * Handles errors occurred during server processing.
         */
        _handleError(data) {
            const parameters = {
                api: this,
                cancel: false,
                returnValues: data.returnValues,
            };
            EventHandler.fire("com.woltlab.wcf.redactor2", "handleError_text", parameters);
            if (!parameters.cancel) {
                this.throwError(this._textarea, data.returnValues.realErrorMessage);
            }
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
         * Inserts the rendered message into the post list, unless the post is on the next
         * page in which case a redirect will be performed instead.
         */
        _insertMessage(data) {
            this._getEditor().WoltLabAutosave.reset();
            // redirect to new page
            if (data.returnValues.url) {
                if (window.location.href == data.returnValues.url) {
                    window.location.reload();
                }
                window.location.href = data.returnValues.url;
            }
            else {
                if (data.returnValues.template) {
                    let elementId;
                    // insert HTML
                    if (this._container.dataset.sortOrder === "DESC") {
                        Util_1.default.insertHtml(data.returnValues.template, this._container, "after");
                        elementId = Util_1.default.identify(this._container.nextElementSibling);
                    }
                    else {
                        let insertBefore = this._container;
                        if (insertBefore.previousElementSibling &&
                            insertBefore.previousElementSibling.classList.contains("messageListPagination")) {
                            insertBefore = insertBefore.previousElementSibling;
                        }
                        Util_1.default.insertHtml(data.returnValues.template, insertBefore, "before");
                        elementId = Util_1.default.identify(insertBefore.previousElementSibling);
                    }
                    // update last post time
                    this._container.dataset.lastPostTime = data.returnValues.lastPostTime.toString();
                    window.history.replaceState(undefined, "", `#${elementId}`);
                    UiScroll.element(document.getElementById(elementId));
                }
                UiNotification.show(Language.get(this._options.successMessage));
                if (this._options.quoteManager) {
                    this._options.quoteManager.countQuotes();
                }
                Listener_1.default.trigger();
            }
        }
        /**
         * @param {{returnValues:{guestDialog:string,guestDialogID:string}}} data
         * @protected
         */
        _ajaxSuccess(data) {
            if (!User_1.default.userId && !data.returnValues.guestDialogID) {
                throw new Error("Missing 'guestDialogID' return value for guest.");
            }
            if (!User_1.default.userId && data.returnValues.guestDialog) {
                const guestDialogId = data.returnValues.guestDialogID;
                Dialog_1.default.openStatic(guestDialogId, data.returnValues.guestDialog, {
                    closable: false,
                    onClose: function () {
                        if (Captcha_1.default.has(guestDialogId)) {
                            Captcha_1.default.delete(guestDialogId);
                        }
                    },
                    title: Language.get("wcf.global.confirmation.title"),
                });
                const dialog = Dialog_1.default.getDialog(guestDialogId);
                const submit = dialog.content.querySelector("input[type=submit]");
                submit.addEventListener("click", (ev) => this._submitGuestDialog(ev));
                const input = dialog.content.querySelector("input[type=text]");
                input.addEventListener("keypress", (ev) => this._submitGuestDialog(ev));
                this._guestDialogId = guestDialogId;
            }
            else {
                this._insertMessage(data);
                if (!User_1.default.userId) {
                    Dialog_1.default.close(data.returnValues.guestDialogID);
                }
                this._reset();
                this._hideLoadingOverlay();
            }
        }
        _ajaxFailure(data) {
            this._hideLoadingOverlay();
            if (data === null || data.returnValues === undefined || data.returnValues.realErrorMessage === undefined) {
                return true;
            }
            this._handleError(data);
            return false;
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: "quickReply",
                    className: this._options.ajax.className,
                    interfaceName: "wcf\\data\\IMessageQuickReplyAction",
                },
                silent: true,
            };
        }
    }
    Core.enableLegacyInheritance(UiMessageReply);
    return UiMessageReply;
});
