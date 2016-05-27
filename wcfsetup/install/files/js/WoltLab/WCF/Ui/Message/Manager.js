/**
 * Provides access and editing of message properties.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Message/Manager
 */
define(['Ajax', 'Core', 'Dictionary', 'Language', 'Dom/Util'], function(Ajax, Core, Dictionary, Language, DomUtil) {
	"use strict";
	
	/**
	 * @param       {Object}        options         initilization options
	 * @constructor
	 */
	function UiMessageManager(options) { this.init(options); }
	UiMessageManager.prototype = {
		/**
		 * Initializes a new manager instance.
		 * 
		 * @param       {Object}        options         initilization options
		 */
		init: function(options) {
			this._elements = null;
			this._options = Core.extend({
				className: '',
				selector: ''
			}, options);
			
			this.rebuild();
		},
		
		/**
		 * Rebuilds the list of observed messages. You should call this method whenever a
		 * message has been either added or removed from the document.
		 */
		rebuild: function() {
			this._elements = new Dictionary();
			
			var element, elements = elBySelAll(this._options.selector);
			for (var i = 0, length = elements.length; i < length; i++) {
				element = elements[i];
				
				this._elements.set(elData(element, 'object-id'), element);
			}
		},
		
		/**
		 * Returns a boolean value for the given permission. The permission should not start
		 * with "can" or "can-" as this is automatically assumed by this method.
		 * 
		 * @param       {int}           objectId        message object id 
		 * @param       {string}        permission      permission name without a leading "can" or "can-"
		 * @return      {boolean}       true if permission was set and is either 'true' or '1'
		 */
		getPermission: function(objectId, permission) {
			permission = 'can-' + this._getAttributeName(permission);
			var element = this._elements.get(objectId);
			if (element === undefined) {
				throw new Error("Unknown object id '" + objectId + "' for selector '" + this._options.selector + "'");
			}
			
			return elDataBool(element, permission);
		},
		
		/**
		 * Returns the given property value from a message, optionally supporting a boolean return value.
		 * 
		 * @param       {int}           objectId        message object id
		 * @param       {string}        propertyName    attribute name
		 * @param       {boolean}       asBool          attempt to interpret property value as boolean
		 * @return      {(boolean|string)}      raw property value or boolean if requested
		 */
		getPropertyValue: function(objectId, propertyName, asBool) {
			var element = this._elements.get(objectId);
			if (element === undefined) {
				throw new Error("Unknown object id '" + objectId + "' for selector '" + this._options.selector + "'");
			}
			
			return window[(asBool ? 'elDataBool' : 'elData')](element, this._getAttributeName(propertyName));
		},
		
		/**
		 * Invokes a method for given message object id in order to alter its state or properties.
		 * 
		 * @param       {int}           objectId        message object id
		 * @param       {string}        actionName      action name used for the ajax api
		 * @param       {Object=}       parameters      optional list of parameters included with the ajax request
		 */
		update: function(objectId, actionName, parameters) {
			Ajax.api(this, {
				actionName: actionName,
				parameters: parameters || {},
				objectIDs: [objectId]
			});
		},
		
		/**
		 * Updates properties and states for given object ids. Keep in mind that this method does
		 * not support setting individual properties per message, instead all property changes
		 * are applied to all matching message objects.
		 * 
		 * @param       {Array<int>}    objectIds       list of message object ids
		 * @param       {Object}        data            list of updated properties
		 */
		updateItems: function(objectIds, data) {
			if (!Array.isArray(objectIds)) {
				objectIds = [objectIds];
			}
			
			var element;
			for (var i = 0, length = objectIds.length; i < length; i++) {
				element = this._elements.get(objectIds[i]);
				if (element === undefined) {
					continue;
				}
				
				for (var key in data) {
					if (data.hasOwnProperty(key)) {
						this._update(element, key, data[key]);
					}
				}
			}
		},
		
		/**
		 * Bulk updates the properties and states for all observed messages at once.
		 * 
		 * @param       {Object}        data            list of updated properties
		 */
		updateAllItems: function(data) {
			var objectIds = [];
			this._elements.forEach((function(element, objectId) {
				objectIds.push(objectId);
			}).bind(this));
			
			this.updateItems(objectIds, data);
		},
		
		/**
		 * Updates a single property of a message element.
		 * 
		 * @param       {Element}       element         message element
		 * @param       {string}        propertyName    property name
		 * @param       {?}             propertyValue   property value, will be implicitly converted to string
		 * @protected
		 */
		_update: function(element, propertyName, propertyValue) {
			elData(element, this._getAttributeName(propertyName), propertyValue);
			
			// handle special properties
			var propertyValueBoolean = (propertyValue == 1 || propertyValue === true || propertyValue === 'true');
			this._updateState(element, propertyName, propertyValue, propertyValueBoolean);
		},
		
		/**
		 * Updates the message element's state based upon a property change.
		 * 
		 * @param       {Element}       element                 message element
		 * @param       {string}        propertyName            property name
		 * @param       {?}             propertyValue           property value
		 * @param       {boolean}       propertyValueBoolean    true if `propertyValue` equals either 'true' or '1'
		 * @protected
		 */
		_updateState: function(element, propertyName, propertyValue, propertyValueBoolean) {
			switch (propertyName) {
				case 'isDeleted':
					element.classList[(propertyValueBoolean ? 'add' : 'remove')]('messageDeleted');
					this._toggleMessageStatus(element, 'jsIconDeleted', 'wcf.message.status.deleted', 'red', propertyValueBoolean);
					
					break;
				
				case 'isDisabled':
					element.classList[(propertyValueBoolean ? 'add' : 'remove')]('messageDisabled');
					this._toggleMessageStatus(element, 'jsIconDisabled', 'wcf.message.status.disabled', 'green', propertyValueBoolean);
					
					break;
			}
		},
		
		/**
		 * Toggles the message status bade for provided element.
		 * 
		 * @param       {Element}       element         message element
		 * @param       {string}        className       badge class name
		 * @param       {string}        phrase          language phrase
		 * @param       {string}        badgeColor      color css class
		 * @param       {boolean}       addBadge        add or remove badge
		 * @protected
		 */
		_toggleMessageStatus: function(element, className, phrase, badgeColor, addBadge) {
			var messageStatus = elBySel('.messageStatus', element);
			if (messageStatus === null) {
				var messageHeaderMetaData = elBySel('.messageHeaderMetaData', element);
				if (messageHeaderMetaData === null) {
					// can't find appropriate location to insert badge
					return;
				}
				
				messageStatus = elCreate('ul');
				messageStatus.className = 'messageStatus';
				DomUtil.insertAfter(messageStatus, messageHeaderMetaData);
			}
			
			var badge = elBySel('.' + className, messageStatus);
			
			if (addBadge) {
				if (badge !== null) {
					// badge already exists
					return;
				}
				
				badge = elCreate('span');
				badge.className = 'badge label ' + badgeColor + ' ' + className;
				badge.textContent = Language.get(phrase);
				
				var listItem = elCreate('li');
				listItem.appendChild(badge);
				messageStatus.appendChild(listItem);
			}
			else {
				if (badge === null) {
					// badge does not exist
					return;
				}
				
				elRemove(badge.parentNode);
			}
		},
		
		/**
		 * Transforms camel-cased property names into their attribute equivalent.
		 * 
		 * @param       {string}        propertyName    camel-cased property name
		 * @return      {string}        equivalent attribute name
		 * @protected
		 */
		_getAttributeName: function(propertyName) {
			if (propertyName.indexOf('-') !== -1) {
				return propertyName;
			}
			
			var attributeName = '';
			var str, tmp = propertyName.split(/([A-Z][a-z]+)/);
			for (var i = 0, length = tmp.length; i < length; i++) {
				str = tmp[i];
				if (str.length) {
					if (attributeName.length) attributeName += '-';
					attributeName += str.toLowerCase();
				}
			}
			
			return attributeName;
		},
		
		_ajaxSuccess: function() {
			throw new Error("Method _ajaxSuccess() must be implemented by deriving functions.");
		},
		
		_ajaxSetup: function() {
			return {
				data: {
					className: this._options.className
				}
			};
		}
	};
	
	return UiMessageManager;
});