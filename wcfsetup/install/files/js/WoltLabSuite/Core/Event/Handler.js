/**
 * Versatile event system similar to the WCF-PHP counter part.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Event/Handler
 */
define(['Core', 'Devtools', 'Dictionary'], function(Core, Devtools, Dictionary) {
	"use strict";
	
	var _listeners = new Dictionary();
	
	/**
	 * @exports	WoltLabSuite/Core/Event/Handler
	 */
	return {
		/**
		 * Adds an event listener.
		 * 
		 * @param	{string}		identifier	event identifier
		 * @param	{string}		action		action name
		 * @param	{function(object)}	callback	callback function
		 * @return	{string}	uuid required for listener removal
		 */
		add: function(identifier, action, callback) {
			if (typeof callback !== 'function') {
				throw new TypeError("[WoltLabSuite/Core/Event/Handler] Expected a valid callback for '" + action + "@" + identifier + "'.");
			}
			
			var actions = _listeners.get(identifier);
			if (actions === undefined) {
				actions = new Dictionary();
				_listeners.set(identifier, actions);
			}
			
			var callbacks = actions.get(action);
			if (callbacks === undefined) {
				callbacks = new Dictionary();
				actions.set(action, callbacks);
			}
			
			var uuid = Core.getUuid();
			callbacks.set(uuid, callback);
			
			return uuid;
		},
		
		/**
		 * Fires an event and notifies all listeners.
		 * 
		 * @param	{string}	identifier	event identifier
		 * @param	{string}	action		action name
		 * @param	{object=}	data		event data
		 */
		fire: function(identifier, action, data) {
			Devtools._internal_.eventLog(identifier, action);
			
			data = data || {};
			
			var actions = _listeners.get(identifier);
			if (actions !== undefined) {
				var callbacks = actions.get(action);
				if (callbacks !== undefined) {
					callbacks.forEach(function(callback) {
						callback(data);
					});
				}
			}
		},
		
		/**
		 * Removes an event listener, requires the uuid returned by add().
		 * 
		 * @param	{string}	identifier	event identifier
		 * @param	{string}	action		action name
		 * @param	{string}	uuid		listener uuid
		 */
		remove: function(identifier, action, uuid) {
			var actions = _listeners.get(identifier);
			if (actions === undefined) {
				return;
			}
			
			var callbacks = actions.get(action);
			if (callbacks === undefined) {
				return;
			}
			
			callbacks['delete'](uuid);
		},
		
		/**
		 * Removes all event listeners for given action. Omitting the second parameter will
		 * remove all listeners for this identifier.
		 * 
		 * @param	{string}	identifier	event identifier
		 * @param	{string=}	action		action name
		 */
		removeAll: function(identifier, action) {
			if (typeof action !== 'string') action = undefined;
			
			var actions = _listeners.get(identifier);
			if (actions === undefined) {
				return;
			}
			
			if (typeof action === 'undefined') {
				_listeners['delete'](identifier);
			}
			else {
				actions['delete'](action);
			}
		},
		
		/**
		 * Removes all listeners registered for an identifier and ending with a special suffix.
		 * This is commonly used to unbound event handlers for the editor.
		 * 
		 * @param       {string}        identifier      event identifier
		 * @param       {string}        suffix          action suffix
		 */
		removeAllBySuffix: function (identifier, suffix) {
			var actions = _listeners.get(identifier);
			if (actions === undefined) {
				return;
			}
			
			suffix = '_' + suffix;
			var length = suffix.length * -1;
			actions.forEach((function (callbacks, action) {
				//noinspection JSUnresolvedFunction
				if (action.substr(length) === suffix) {
					this.removeAll(identifier, action);
				}
			}).bind(this));
		}
	};
});
