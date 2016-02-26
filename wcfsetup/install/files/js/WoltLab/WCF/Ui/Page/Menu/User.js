/**
 * Provides the touch-friendly fullscreen user menu.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Page/Menu/User
 */
define(['Core', 'EventHandler', './Abstract'], function(Core, EventHandler, UiPageMenuAbstract) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function UiPageMenuUser() { this.init(); }
	Core.inherit(UiPageMenuUser, UiPageMenuAbstract, {
		/**
		 * Initializes the touch-friendly fullscreen user menu.
		 */
		init: function() {
			// check if user menu is present, as it is absent when not logged-in
			if (elById('pageUserMenuMobile') === null) {
				elBySel('#pageHeader .userPanel').addEventListener(WCF_CLICK_EVENT, function(event) {
					event.preventDefault();
					event.stopPropagation();
					
					EventHandler.fire('com.woltlab.wcf.UserMenuMobile', 'showLogin');
				});
				
				return;
			}
			
			UiPageMenuUser._super.prototype.init.call(
				this,
				'com.woltlab.wcf.UserMenuMobile',
				'pageUserMenuMobile',
				'#pageHeader .userPanel'
			);
		},
		
		/**
		 * Overrides the `_initItem()` method to check for special items that do not
		 * act as a link but instead trigger an event for external processing.
		 * 
		 * @param       {Element}       item    menu item
		 * @protected
		 */
		_initItem: function(item) {
			// check if it should contain a 'more' link w/ an external callback
			var more = elData(item.parentNode, 'more');
			if (more) {
				item.addEventListener(WCF_CLICK_EVENT, (function(event) {
					event.preventDefault();
					event.stopPropagation();
					
					EventHandler.fire(this._eventIdentifier, 'more', {
						handler: this,
						identifier: more
					});
				}).bind(this));
				
				return;
			}
			
			UiPageMenuUser._super.prototype._initItem.call(this, item);
		}
	});
	
	return UiPageMenuUser;
});
