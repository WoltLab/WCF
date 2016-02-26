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
			var parent = item.parentNode;
			var more = elData(parent, 'more');
			if (more) {
				item.addEventListener(WCF_CLICK_EVENT, (function(event) {
					event.preventDefault();
					event.stopPropagation();
					
					EventHandler.fire(this._eventIdentifier, 'more', {
						handler: this,
						identifier: more,
						item: item,
						parent: parent
					});
				}).bind(this));
				
				return;
			}
			
			UiPageMenuUser._super.prototype._initItem.call(this, item);
		}
	});
	
	return UiPageMenuUser;
});
