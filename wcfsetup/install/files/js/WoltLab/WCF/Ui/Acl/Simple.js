define(['Language', 'Dom/ChangeListener', 'WoltLab/WCF/Ui/User/Search/Input'], function(Language, DomChangeListener, UiUserSearchInput) {
	"use strict";
	
	function UiAclSimple() { this.init(); }
	UiAclSimple.prototype = {
		init: function() {
			this._build();
		},
		
		_build: function () {
			var container = elById('aclInputContainer');
			
			elById('aclAllowAll').addEventListener('change', (function() {
				elHide(container);
			}));
			elById('aclAllowAll_no').addEventListener('change', (function() {
				elShow(container);
			}));
			
			new UiUserSearchInput(elById('aclSearchInput'), {
				callbackSelect: this._select.bind(this),
				includeUserGroups: true,
				preventSubmit: true
			});
			
			this._aclListContainer = elById('aclListContainer');
			
			this._list = elById('aclAccessList');
			this._list.addEventListener(WCF_CLICK_EVENT, this._removeItem.bind(this));
			
			DomChangeListener.trigger();
		},
		
		_select: function(listItem) {
			var type = elData(listItem, 'type');
			
			var html = '<span class="icon icon16 fa-' + (type === 'group' ? 'users' : 'user') + '"></span>';
			html += '<span class="aclLabel">' + elData(listItem, 'label') + '</span>';
			html += '<span class="icon icon16 fa-times pointer jsTooltip" title="' + Language.get('wcf.global.button.delete') + '"></span>';
			html += '<input type="hidden" name="aclValues[' + type + '][]" value="' + elData(listItem, 'object-id') + '">';
			
			var item = elCreate('li');
			item.innerHTML = html;
			
			var firstUser = elBySel('.fa-user', this._list);
			if (firstUser === null) {
				this._list.appendChild(item);
			}
			else {
				this._list.insertBefore(item, firstUser.parentNode);
			}
			
			elShow(this._aclListContainer);
			
			DomChangeListener.trigger();
			
			return false;
		},
		
		_removeItem: function (event) {
			if (event.target.classList.contains('fa-times')) {
				elRemove(event.target.parentNode);
				
				if (this._list.childElementCount === 0) {
					elHide(this._aclListContainer);
				}
			}
		}
	};
	
	return UiAclSimple;
});
