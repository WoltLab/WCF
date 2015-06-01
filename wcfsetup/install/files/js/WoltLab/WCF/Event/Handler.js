/**
 * Versatile event system similar to the WCF-PHP counter part.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Event/Handler
 */
define(['Core', 'Dictionary'], function(Core, Dictionary) {
	"use strict";
	
	var _listeners = new Dictionary();
	
	/**
	 * @exports	WoltLab/WCF/Event/Handler
	 */
	var EventHandler = {
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
				throw new TypeError("[WoltLab/WCF/Event/Handler] Expected a valid callback for '" + action + "@" + identifier + "'.");
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
		}
	};
	
	return EventHandler;
});
