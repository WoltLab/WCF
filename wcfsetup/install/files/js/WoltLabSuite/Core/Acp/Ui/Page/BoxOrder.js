define(['Ajax', 'Language', 'Ui/Confirmation', 'Ui/Notification'], function (Ajax, Language, UiConfirmation, UiNotification) {
	"use strict";
	
	var _pageId = 0;
	var _pbo = elById('pbo');
	
	return {
		init: function (pageId, boxes) {
			_pageId = pageId;
			
			boxes.forEach(function(boxData, position) {
				var container = elCreate('ul');
				boxData.forEach(function(box) {
					var item = elCreate('li');
					elData(item, 'box-id', box.boxID);
					item.innerHTML = box.name;
					
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
		},
		
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