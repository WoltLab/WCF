/**
 * Provides helper functions to sort boxes per page.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Acp/Ui/Page/BoxOrder
 */
define(['Ajax', 'Language', 'Dom/ChangeListener', 'Ui/Confirmation', 'Ui/Notification'], function (Ajax, Language, DomChangeListener, UiConfirmation, UiNotification) {
	"use strict";
	
	var _pageId = 0;
	var _pbo = elById('pbo');
	
	/**
	 * @exports     WoltLabSuite/Core/Acp/Ui/Page/BoxOrder
	 */
	return {
		/**
		 * Initializes the sorting capabilities.
		 * 
		 * @param       {int}           pageId          page id
		 * @param       {Dictionary}    boxes           list of boxes per position
		 */
		init: function (pageId, boxes) {
			_pageId = pageId;
			
			boxes.forEach(function(boxData, position) {
				var container = elCreate('ul');
				boxData.forEach(function(box) {
					var item = elCreate('li');
					elData(item, 'box-id', box.boxID);
					
					var icon = '';
					if (box.isDisabled) {
						icon = ' <span class="icon icon16 fa-exclamation-triangle red jsTooltip" title="' + Language.get('wcf.acp.box.isDisabled') + '"></span>';
					}
					
					item.innerHTML = box.name + icon;
					
					container.appendChild(item);
				});
				
				if (boxData.length > 1) {
					window.jQuery(container).sortable({
						opacity: .6,
						placeholder: 'sortablePlaceholder'
					});
				}
				
				elBySel('[data-placeholder="' + position + '"]', _pbo).appendChild(container);
			});
			
			elBySel('button[data-type="submit"]').addEventListener(WCF_CLICK_EVENT, this._save.bind(this));
			
			var buttonDiscard = elBySel('.jsButtonCustomShowOrder');
			if (buttonDiscard) buttonDiscard.addEventListener(WCF_CLICK_EVENT, this._discard.bind(this));
			
			DomChangeListener.trigger();
		},
		
		/**
		 * Saves the order of all boxes per position.
		 * 
		 * @param       {Event}         event           event object
		 * @protected
		 */
		_save: function (event) {
			event.preventDefault();
			
			var data = {};
			
			// collect data
			elBySelAll('[data-placeholder]', _pbo, function (position) {
				var boxes = [];
				elBySelAll('li', position, function (li) {
					var id = ~~elData(li, 'box-id');
					if (id) boxes.push(id);
				});
				
				data[elData(position, 'placeholder')] = boxes;
			});
			
			Ajax.api(this, {
				parameters: {
					position: data
				}
			});
		},
		
		/**
		 * Shows an dialog to discard the individual box show order for this page.
		 * 
		 * @param       {Event}         event           event object
		 * @protected
		 */
		_discard: function (event) {
			event.preventDefault();
			
			UiConfirmation.show({
				confirm: (function () {
					Ajax.api(this, {
						actionName: 'resetPosition'
					});
				}).bind(this),
				message: Language.get('wcf.acp.page.boxOrder.discard.confirmMessage')
			})
		},
		
		_ajaxSuccess: function (data) {
			switch (data.actionName) {
				case 'updatePosition':
					UiNotification.show();
					break;
					
				case 'resetPosition':
					UiNotification.show(undefined, function () {
						window.location.reload();
					});
					break;
			}
		},
		
		_ajaxSetup: function () {
			return {
				data: {
					actionName: 'updatePosition',
					className: 'wcf\\data\\page\\PageAction',
					interfaceName: 'wcf\\data\\ISortableAction',
					objectIDs: [_pageId]
				}
			};
		}
	};
});
