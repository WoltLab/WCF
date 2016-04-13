define(['Core', 'Language', 'Ui/Notification', './Abstract'], function(Core, Language, UiNotification, UiUserProfileMenuItemAbstract) {
	"use strict";
	
	function UiUserProfileMenuItemFollow(userId, isActive) { this.init(userId, isActive); }
	Core.inherit(UiUserProfileMenuItemFollow, UiUserProfileMenuItemAbstract, {
		_getLabel: function() {
			return Language.get('wcf.user.button.' + (this._isActive ? 'un' : '') + 'follow');
		},
		
		_getAjaxActionName: function() {
			return this._isActive ? 'unfollow' : 'follow';
		},
		
		_ajaxSuccess: function(data) {
			this._isActive = (data.returnValues.following ? true : false);
			this._updateButton();
			
			UiNotification.show();
		},
		
		_ajaxSetup: function() {
			return {
				data: {
					className: 'wcf\\data\\user\\follow\\UserFollowAction'
				}
			}
		}
	});
	
	return UiUserProfileMenuItemFollow;
});
