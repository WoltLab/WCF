/**
 * Provides editing support for comment responses.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Comment/Response/Edit
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../../../Ajax", "../../../Core", "../../../Dom/Change/Listener", "../../../Dom/Util", "../Edit", "../../Notification"], function (require, exports, tslib_1, Ajax, Core, Listener_1, Util_1, Edit_1, UiNotification) {
    "use strict";
    Ajax = tslib_1.__importStar(Ajax);
    Core = tslib_1.__importStar(Core);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    Edit_1 = tslib_1.__importDefault(Edit_1);
    UiNotification = tslib_1.__importStar(UiNotification);
    class UiCommentResponseEdit extends Edit_1.default {
        /**
         * Initializes the comment edit manager.
         *
         * @param  {Element}       container       container element
         */
        constructor(container) {
            super(container);
            this._responses = new WeakSet();
            this.rebuildResponses();
            Listener_1.default.add("Ui/Comment/Response/Edit_" + Util_1.default.identify(this._container), () => this.rebuildResponses());
        }
        rebuild() {
            // Do nothing, we want to avoid implicitly invoking `UiCommentEdit.rebuild()`.
        }
        /**
         * Initializes each applicable message, should be called whenever new
         * messages are being displayed.
         */
        rebuildResponses() {
            this._container.querySelectorAll(".commentResponse").forEach((response) => {
                if (this._responses.has(response)) {
                    return;
                }
                if (Core.stringToBool(response.dataset.canEdit || "")) {
                    const button = response.querySelector(".jsCommentResponseEditButton");
                    if (button !== null) {
                        button.addEventListener("click", (ev) => this._click(ev));
                    }
                }
                this._responses.add(response);
            });
        }
        /**
         * Handles clicks on the edit button.
         */
        _click(event) {
            event.preventDefault();
            if (this._activeElement === null) {
                const target = event.currentTarget;
                this._activeElement = target.closest(".commentResponse");
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
         *
         * @protected
         */
        _prepare() {
            this._editorContainer = document.createElement("div");
            this._editorContainer.className = "commentEditorContainer";
            this._editorContainer.innerHTML = '<span class="icon icon48 fa-spinner"></span>';
            const content = this._activeElement.querySelector(".commentResponseContent");
            content.insertBefore(this._editorContainer, content.firstChild);
        }
        /**
         * Shows the update message.
         */
        _showMessage(data) {
            // set new content
            const parent = this._editorContainer.parentElement;
            Util_1.default.setInnerHtml(parent.querySelector(".commentResponseContent .userMessage"), data.returnValues.message);
            this._restoreMessage();
            UiNotification.show();
        }
        /**
         * Returns the unique editor id.
         */
        _getEditorId() {
            return `commentResponseEditor${this._getObjectId(this._activeElement)}`;
        }
        _ajaxSetup() {
            const objectTypeId = ~~this._container.dataset.objectTypeId;
            return {
                data: {
                    className: "wcf\\data\\comment\\response\\CommentResponseAction",
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
    Core.enableLegacyInheritance(UiCommentResponseEdit);
    return UiCommentResponseEdit;
});
