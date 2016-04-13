/**
 * Default implementation for user interaction menu items used in the user profile.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/User/Profile/Menu/Item/Abstract
 */
define(['Ajax', 'Dom/Util'], function(Ajax, DomUtil) {
	"use strict";
	
	/**
	 * Creates a new user profile menu item.
	 * 
	 * @param       {int}           userId          user id
	 * @param       {boolean}       isActive        true if item is initially active
	 * @constructor
	 */
	function UiUserProfileMenuItemAbstract(userId, isActive) {}
	UiUserProfileMenuItemAbstract.prototype = {
		/**
		 * Creates a new user profile menu item.
		 * 
		 * @param       {int}           userId          user id
		 * @param       {boolean}       isActive        true if item is initially active
		 */
		init: function(userId, isActive) {
			this._userId = userId;
			this._isActive = (isActive !== false);
			
			this._initButton();
			this._updateButton();
		},
		
		/**
		 * Initializes the menu item.
		 * 
		 * @protected
		 */
		_initButton: function() {
			var button = elCreate('a');
			button.href = '#';
			button.addEventListener(WCF_CLICK_EVENT, this._toggle.bind(this));
			
			var listItem = elCreate('li');
			listItem.appendChild(button);
			
			var menu = elBySel('.userProfileButtonMenu[data-menu="interaction"]');
			DomUtil.prepend(listItem, menu);
			
			this._button = button;
			this._listItem = listItem;
		},
		
		/**
		 * Handles clicks on the menu item button.
		 * 
		 * @param       {Event}         event   event object
		 * @protected
		 */
		_toggle: function(event) {
			event.preventDefault();
			
			Ajax.api(this, {
				actionName: this._getAjaxActionName(),
				parameters: {
					data: {
						userID: this._userId
					}
				}
			});
		},
		
		/**
		 * Updates the button state and label.
		 * 
		 * @protected
		 */
		_updateButton: function() {
			this._button.textContent = this._getLabel();
			this._listItem.classList[(this._isActive ? 'add' : 'remove')]('active');
		},
		
		/**
		 * Returns the button label.
		 * 
		 * @return      {string}        button label
		 * @protected
		 * @abstract
		 */
		_getLabel: function() {
			throw new Error("Implement me!");
		},
		
		/**
		 * Returns the Ajax action name.
		 * 
		 * @return      {string}        ajax action name
		 * @protected
		 * @abstract
		 */
		_getAjaxActionName: function() {
			throw new Error("Implement me!");
		},
		
		/**
		 * Handles successful Ajax requests.
		 * 
		 * @protected
		 * @abstract
		 */
		_ajaxSuccess: function() {
			throw new Error("Implement me!");
		},
		
		/**
		 * Returns the default Ajax request data
		 * 
		 * @return      {Object}        ajax request data
		 * @protected
		 * @abstract
		 */
		_ajaxSetup: function() {
			throw new Error("Implement me!");
		}
	};
	
	return UiUserProfileMenuItemAbstract;
});
