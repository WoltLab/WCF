/**
 * Handles the comment response add feature.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Comment/Add
 */
define([
	'Core', 'Language', 'Dom/ChangeListener', 'Dom/Util', 'Dom/Traverse', 'Ui/Notification',  'WoltLabSuite/Core/Ui/Comment/Add'
],
function(
	Core, Language, DomChangeListener, DomUtil, DomTraverse, UiNotification, UiCommentAdd
) {
	"use strict";
	
	if (!COMPILER_TARGET_DEFAULT) {
		var Fake = function() {};
		Fake.prototype = {
			init: function() {},
			getContainer: function() {},
			getContent: function() {},
			setContent: function() {},
			_submitGuestDialog: function() {},
			_submit: function() {},
			_getParameters: function () {},
			_validate: function() {},
			throwError: function() {},
			_showLoadingOverlay: function() {},
			_hideLoadingOverlay: function() {},
			_reset: function() {},
			_handleError: function() {},
			_getEditor: function() {},
			_insertMessage: function() {},
			_ajaxSuccess: function() {},
			_ajaxFailure: function() {},
			_ajaxSetup: function() {}
		};
		return Fake;
	}
	
	/**
	 * @constructor
	 */
	function UiCommentResponseAdd(container, options) { this.init(container, options); }
	Core.inherit(UiCommentResponseAdd, UiCommentAdd, {
		init: function (container, options) {
			UiCommentResponseAdd._super.prototype.init.call(this, container);
			
			this._options = Core.extend({
				callbackInsert: null
			}, options);
		},
		
		/**
		 * Returns the editor container for placement or `null` if the editor is busy.
		 * 
		 * @return      {(Element|null)}
		 */
		getContainer: function() {
			return (this._isBusy) ? null : this._container;
		},
		
		/**
		 * Retrieves the current content from the editor.
		 * 
		 * @return      {string}
		 */
		getContent: function () {
			return window.jQuery(this._textarea).redactor('code.get');
		},
		
		/**
		 * Sets the content and places the caret at the end of the editor.
		 * 
		 * @param       {string}        html
		 */
		setContent: function (html) {
			window.jQuery(this._textarea).redactor('code.set', html);
			window.jQuery(this._textarea).redactor('WoltLabCaret.endOfEditor');
			
			// the error message can appear anywhere in the container, not exclusively after the textarea
			var innerError = elBySel('.innerError', this._textarea.parentNode);
			if (innerError !== null) elRemove(innerError);
			
			this._content.classList.remove('collapsed');
			this._focusEditor();
		},
		
		_getParameters: function () {
			var parameters = UiCommentResponseAdd._super.prototype._getParameters.call(this);
			parameters.data.commentID = ~~elData(this._container.closest('.comment'), 'object-id');
			
			return parameters;
		},
		
		_insertMessage: function(data) {
			var commentContent = DomTraverse.childByClass(this._container.parentNode, 'commentContent');
			var responseList = commentContent.nextElementSibling;
			if (responseList === null || !responseList.classList.contains('commentResponseList')) {
				responseList = elCreate('ul');
				responseList.className = 'containerList commentResponseList';
				elData(responseList, 'responses', 0);
				
				commentContent.parentNode.insertBefore(responseList, commentContent.nextSibling);
			}
			
			// insert HTML
			//noinspection JSCheckFunctionSignatures
			DomUtil.insertHtml(data.returnValues.template, responseList, 'append');
			
			UiNotification.show(Language.get('wcf.global.success.add'));
			
			DomChangeListener.trigger();
			
			// reset editor
			window.jQuery(this._textarea).redactor('code.set', '');
			
			if (this._options.callbackInsert !== null) this._options.callbackInsert();
			
			// update counter
			elData(responseList, 'responses', responseList.children.length);
			
			return responseList.lastElementChild;
		},
		
		_ajaxSetup: function() {
			var data = UiCommentResponseAdd._super.prototype._ajaxSetup.call(this);
			data.data.actionName = 'addResponse';
			
			return data;
		}
	});
	
	return UiCommentResponseAdd;
});
