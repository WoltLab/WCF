define(['Language', 'Dom/ChangeListener', 'WoltLab/WCF/Ui/User/Search/Input'], function(Language, DomChangeListener, UiUserSearchInput) {
	"use strict";
	
	function UiAclSimple(prefix) { this.init(prefix); }
	UiAclSimple.prototype = {
		init: function(prefix) {
			this._prefix = prefix || '';
			
			this._build();
		},
		
		_build: function () {
			var container = elById(this._prefix + 'aclInputContainer');
			
			elById(this._prefix + 'aclAllowAll').addEventListener('change', (function() {
				elHide(container);
			}));
			elById(this._prefix + 'aclAllowAll_no').addEventListener('change', (function() {
				elShow(container);
			}));
			
			new UiUserSearchInput(elById(this._prefix + 'aclSearchInput'), {
				callbackSelect: this._select.bind(this),
				includeUserGroups: true,
				preventSubmit: true
			});
			
			this._aclListContainer = elById(this._prefix + 'aclListContainer');
			
			this._list = elById(this._prefix + 'aclAccessList');
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
