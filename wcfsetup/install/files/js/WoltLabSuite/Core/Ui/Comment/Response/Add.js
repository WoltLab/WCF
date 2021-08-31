/**
 * Handles the comment response add feature.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Comment/Add
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../../../Core", "../../../Dom/Change/Listener", "../../../Dom/Util", "../../../Language", "../Add", "../../Notification"], function (require, exports, tslib_1, Core, Listener_1, Util_1, Language, Add_1, UiNotification) {
    "use strict";
    Core = (0, tslib_1.__importStar)(Core);
    Listener_1 = (0, tslib_1.__importDefault)(Listener_1);
    Util_1 = (0, tslib_1.__importDefault)(Util_1);
    Language = (0, tslib_1.__importStar)(Language);
    Add_1 = (0, tslib_1.__importDefault)(Add_1);
    UiNotification = (0, tslib_1.__importStar)(UiNotification);
    class UiCommentResponseAdd extends Add_1.default {
        constructor(container, options) {
            super(container);
            this._options = Core.extend({
                callbackInsert: null,
            }, options);
        }
        /**
         * Returns the editor container for placement.
         */
        getContainer() {
            return this._container;
        }
        /**
         * Retrieves the current content from the editor.
         */
        getContent() {
            return window.jQuery(this._textarea).redactor("code.get");
        }
        /**
         * Sets the content and places the caret at the end of the editor.
         */
        setContent(html) {
            window.jQuery(this._textarea).redactor("code.set", html);
            window.jQuery(this._textarea).redactor("WoltLabCaret.endOfEditor");
            // the error message can appear anywhere in the container, not exclusively after the textarea
            const innerError = this._textarea.parentElement.querySelector(".innerError");
            if (innerError !== null) {
                innerError.remove();
            }
            this._content.classList.remove("collapsed");
            this._focusEditor();
        }
        _getParameters() {
            const parameters = super._getParameters();
            const comment = this._container.closest(".comment");
            parameters.data.commentID = ~~comment.dataset.objectId;
            return parameters;
        }
        _insertMessage(data) {
            const commentContent = this._container.parentElement.querySelector(".commentContent");
            let responseList = commentContent.nextElementSibling;
            if (responseList === null || !responseList.classList.contains("commentResponseList")) {
                responseList = document.createElement("ul");
                responseList.className = "containerList commentResponseList";
                responseList.dataset.responses = "0";
                commentContent.insertAdjacentElement("afterend", responseList);
            }
            // insert HTML
            Util_1.default.insertHtml(data.returnValues.template, responseList, "append");
            UiNotification.show(Language.get("wcf.global.success.add"));
            Listener_1.default.trigger();
            // reset editor
            window.jQuery(this._textarea).redactor("code.set", "");
            if (this._options.callbackInsert !== null) {
                this._options.callbackInsert();
            }
            // update counter
            responseList.dataset.responses = responseList.children.length.toString();
            return responseList.lastElementChild;
        }
        _ajaxSetup() {
            const data = super._ajaxSetup();
            data.data.actionName = "addResponse";
            return data;
        }
    }
    Core.enableLegacyInheritance(UiCommentResponseAdd);
    return UiCommentResponseAdd;
});
