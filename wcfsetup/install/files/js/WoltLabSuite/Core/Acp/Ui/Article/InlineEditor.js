/**
 * Handles article trash, restore and delete.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/Article/InlineEditor
 */
define(['Ajax', 'Dictionary', 'Language', 'Ui/Confirmation', 'Ui/Notification'], function (Ajax, Dictionary, Language, UiConfirmation, UiNotification) {
	"use strict";
	
	var _articles = new Dictionary();
	
	/**     
	 * @constructor
	 */
	function AcpUiArticleInlineEditor() { this.init(); }
	AcpUiArticleInlineEditor.prototype = {
		/**
		 * Initializes the ACP inline editor for articles.
		 */
		init: function () {
			elBySelAll('.jsArticleRow', undefined, this._initArticle.bind(this));
		},
		
		/**
		 * Initializes an article row element.
		 * 
		 * @param       {Element}       article         article row element
		 * @protected
		 */
		_initArticle: function (article) {
			var objectId = ~~elData(article, 'object-id');
			
			var buttonDelete = elBySel('.jsButtonDelete', article);
			buttonDelete.addEventListener(WCF_CLICK_EVENT, this._prompt.bind(this, objectId, 'delete'));
			
			var buttonRestore = elBySel('.jsButtonRestore', article);
			buttonRestore.addEventListener(WCF_CLICK_EVENT, this._prompt.bind(this, objectId, 'restore'));
			
			var buttonTrash = elBySel('.jsButtonTrash', article);
			buttonTrash.addEventListener(WCF_CLICK_EVENT, this._prompt.bind(this, objectId, 'trash'));
			
			_articles.set(objectId, {
				buttons: {
					delete: buttonDelete,
					restore: buttonRestore,
					trash: buttonTrash
				},
				element: article
			});
		},
		
		/**
		 * Prompts a user to confirm the clicked action before executing it.
		 * 
		 * @param       {int}           objectId        article id
		 * @param       {string}        actionName      action name
		 * @param       {Event}         event           event object
		 * @protected
		 */
		_prompt: function (objectId, actionName, event) {
			event.preventDefault();
			
			var article = _articles.get(objectId);
			
			UiConfirmation.show({
				confirm: (function () { this._invoke(objectId, actionName) }).bind(this),
				message: elData(article.buttons[actionName], 'confirm-message-html'),
				messageIsHtml: true
			});
		},
		
		/**
		 * Invokes the selected action.
		 * 
		 * @param       {int}           objectId        article id
		 * @param       {string}        actionName      action name
		 * @protected
		 */
		_invoke: function (objectId, actionName) {
			Ajax.api(this, {
				actionName: actionName,
				objectIDs: [objectId]
			});
		},
		
		_ajaxSuccess: function (data) {
			var article = _articles.get(data.objectIDs[0]);
			switch (data.actionName) {
				case 'delete':
					var tbody = article.element.parentNode;
					elRemove(article.element);
					
					if (elBySel('tr', tbody) === null) {
						window.location.reload();
					}
					break;
					
				case 'restore':
					elHide(article.buttons.delete);
					elHide(article.buttons.restore);
					elShow(article.buttons.trash);
					
					elRemove(elBySel('.jsIconDeleted', article.element));
					break;
					
				case 'trash':
					elShow(article.buttons.delete);
					elShow(article.buttons.restore);
					elHide(article.buttons.trash);
					
					var badge = elCreate('span');
					badge.className = 'badge label red jsIconDeleted';
					badge.textContent = Language.get('wcf.message.status.deleted');
					
					var h3 = elBySel('.containerHeadline > h3', article.element);
					h3.insertBefore(badge, h3.firstChild);
					
					break;
			}
			
			UiNotification.show();
		},
		
		_ajaxSetup: function () {
			return {
				data: {
					className: 'wcf\\data\\article\\ArticleAction'
				}
			}
		}
	};
	
	return AcpUiArticleInlineEditor;
});
