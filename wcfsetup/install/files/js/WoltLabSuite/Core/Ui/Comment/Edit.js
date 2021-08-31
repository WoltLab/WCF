/**
 * Provides editing support for comments.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Comment/Edit
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../../Ajax", "../../Core", "../../Dom/Change/Listener", "../../Dom/Util", "../../Environment", "../../Event/Handler", "../../Language", "../Scroll", "../Notification"], function (require, exports, tslib_1, Ajax, Core, Listener_1, Util_1, Environment, EventHandler, Language, UiScroll, UiNotification) {
    "use strict";
    Ajax = (0, tslib_1.__importStar)(Ajax);
    Core = (0, tslib_1.__importStar)(Core);
    Listener_1 = (0, tslib_1.__importDefault)(Listener_1);
    Util_1 = (0, tslib_1.__importDefault)(Util_1);
    Environment = (0, tslib_1.__importStar)(Environment);
    EventHandler = (0, tslib_1.__importStar)(EventHandler);
    Language = (0, tslib_1.__importStar)(Language);
    UiScroll = (0, tslib_1.__importStar)(UiScroll);
    UiNotification = (0, tslib_1.__importStar)(UiNotification);
    class UiCommentEdit {
        /**
         * Initializes the comment edit manager.
         */
        constructor(container) {
            this._activeElement = null;
            this._comments = new WeakSet();
            this._editorContainer = null;
            this._container = container;
            this.rebuild();
            Listener_1.default.add("Ui/Comment/Edit_" + Util_1.default.identify(this._container), this.rebuild.bind(this));
        }
        /**
         * Initializes each applicable message, should be called whenever new
         * messages are being displayed.
         */
        rebuild() {
            this._container.querySelectorAll(".comment").forEach((comment) => {
                if (this._comments.has(comment)) {
                    return;
                }
                if (Core.stringToBool(comment.dataset.canEdit || "")) {
                    const button = comment.querySelector(".jsCommentEditButton");
                    if (button !== null) {
                        button.addEventListener("click", (ev) => this._click(ev));
                    }
                }
                this._comments.add(comment);
            });
        }
        /**
         * Handles clicks on the edit button.
         */
        _click(event) {
            event.preventDefault();
            if (this._activeElement === null) {
                const target = event.currentTarget;
                this._activeElement = target.closest(".comment");
                this._prepare();
                Ajax.api(this, {
                    actionName: "beginEdit",
                    objectIDs: [this._getObjectId(this._activeElement)],
                });
            }
            else {
                UiNotification.show("wcf.message.error.editorAlreadyInUse", null, "warning");
            }
        }
        /**
         * Prepares the message for editor display.
         */
        _prepare() {
            this._editorContainer = document.createElement("div");
            this._editorContainer.className = "commentEditorContainer";
            this._editorContainer.innerHTML = '<span class="icon icon48 fa-spinner"></span>';
            const content = this._activeElement.querySelector(".commentContentContainer");
            content.insertBefore(this._editorContainer, content.firstChild);
        }
        /**
         * Shows the message editor.
         */
        _showEditor(data) {
            const id = this._getEditorId();
            const editorContainer = this._editorContainer;
            const icon = editorContainer.querySelector(".icon");
            icon.remove();
            const editor = document.createElement("div");
            editor.className = "editorContainer";
            Util_1.default.setInnerHtml(editor, data.returnValues.template);
            editorContainer.appendChild(editor);
            // bind buttons
            const formSubmit = editorContainer.querySelector(".formSubmit");
            const buttonSave = formSubmit.querySelector('button[data-type="save"]');
            buttonSave.addEventListener("click", () => this._save());
            const buttonCancel = formSubmit.querySelector('button[data-type="cancel"]');
            buttonCancel.addEventListener("click", () => this._restoreMessage());
            EventHandler.add("com.woltlab.wcf.redactor", `submitEditor_${id}`, (data) => {
                data.cancel = true;
                this._save();
            });
            const editorElement = document.getElementById(id);
            if (Environment.editor() === "redactor") {
                window.setTimeout(() => {
                    UiScroll.element(this._activeElement);
                }, 250);
            }
            else {
                editorElement.focus();
            }
        }
        /**
         * Restores the message view.
         */
        _restoreMessage() {
            this._destroyEditor();
            this._editorContainer.remove();
            this._activeElement = null;
        }
        /**
         * Saves the editor message.
         */
        _save() {
            const parameters = {
                data: {
                    message: "",
                },
            };
            const id = this._getEditorId();
            EventHandler.fire("com.woltlab.wcf.redactor2", `getText_${id}`, parameters.data);
            if (!this._validate(parameters)) {
                // validation failed
                return;
            }
            EventHandler.fire("com.woltlab.wcf.redactor2", `submit_${id}`, parameters);
            Ajax.api(this, {
                actionName: "save",
                objectIDs: [this._getObjectId(this._activeElement)],
                parameters: parameters,
            });
            this._hideEditor();
        }
        /**
         * Validates the message and invokes listeners to perform additional validation.
         */
        _validate(parameters) {
            // remove all existing error elements
            this._activeElement.querySelectorAll(".innerError").forEach((el) => el.remove());
            // check if editor contains actual content
            const editorElement = document.getElementById(this._getEditorId());
            const redactor = window.jQuery(editorElement).data("redactor");
            if (redactor.utils.isEmpty()) {
                this.throwError(editorElement, Language.get("wcf.global.form.error.empty"));
                return false;
            }
            const data = {
                api: this,
                parameters: parameters,
                valid: true,
            };
            EventHandler.fire("com.woltlab.wcf.redactor2", "validate_" + this._getEditorId(), data);
            return data.valid;
        }
        /**
         * Throws an error by adding an inline error to target element.
         */
        throwError(element, message) {
            Util_1.default.innerError(element, message);
        }
        /**
         * Shows the update message.
         */
        _showMessage(data) {
            // set new content
            const container = this._editorContainer.parentElement.querySelector(".commentContent .userMessage");
            Util_1.default.setInnerHtml(container, data.returnValues.message);
            this._restoreMessage();
            UiNotification.show();
        }
        /**
         * Hides the editor from view.
         */
        _hideEditor() {
            const editorContainer = this._editorContainer.querySelector(".editorContainer");
            Util_1.default.hide(editorContainer);
            const icon = document.createElement("span");
            icon.className = "icon icon48 fa-spinner";
            this._editorContainer.appendChild(icon);
        }
        /**
         * Restores the previously hidden editor.
         */
        _restoreEditor() {
            const icon = this._editorContainer.querySelector(".fa-spinner");
            icon.remove();
            const editorContainer = this._editorContainer.querySelector(".editorContainer");
            if (editorContainer !== null) {
                Util_1.default.show(editorContainer);
            }
        }
        /**
         * Destroys the editor instance.
         */
        _destroyEditor() {
            EventHandler.fire("com.woltlab.wcf.redactor2", `autosaveDestroy_${this._getEditorId()}`);
            EventHandler.fire("com.woltlab.wcf.redactor2", `destroy_${this._getEditorId()}`);
        }
        /**
         * Returns the unique editor id.
         */
        _getEditorId() {
            return `commentEditor${this._getObjectId(this._activeElement)}`;
        }
        /**
         * Returns the element's `data-object-id` value.
         */
        _getObjectId(element) {
            return ~~element.dataset.objectId;
        }
        _ajaxFailure(data) {
            const editor = this._editorContainer.querySelector(".redactor-layer");
            // handle errors occurring on editor load
            if (editor === null) {
                this._restoreMessage();
                return true;
            }
            this._restoreEditor();
            if (!data || data.returnValues === undefined || data.returnValues.errorType === undefined) {
                return true;
            }
            Util_1.default.innerError(editor, data.returnValues.errorType);
            return false;
        }
        _ajaxSuccess(data) {
            switch (data.actionName) {
                case "beginEdit":
                    this._showEditor(data);
                    break;
                case "save":
                    this._showMessage(data);
                    break;
            }
        }
        _ajaxSetup() {
            const objectTypeId = ~~this._container.dataset.objectTypeId;
            return {
                data: {
                    className: "wcf\\data\\comment\\CommentAction",
                    parameters: {
                        data: {
                            objectTypeID: objectTypeId,
                        },
                    },
                },
                silent: true,
            };
        }
    }
    Core.enableLegacyInheritance(UiCommentEdit);
    return UiCommentEdit;
});
