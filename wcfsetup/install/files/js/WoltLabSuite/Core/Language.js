/**
 * Manages language items.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	Language (alias)
 * @module	WoltLabSuite/Core/Language
 */
define(['Dictionary', './Template'], function(Dictionary, Template) {
	"use strict";
	
	var _languageItems = new Dictionary();
	
	/**
	 * @exports	WoltLabSuite/Core/Language
	 */
	var Language = {
		/**
		 * Adds all the language items in the given object to the store.
		 * 
		 * @param	{Object.<string, string>}	object
		 */
		addObject: function(object) {
			_languageItems.merge(Dictionary.fromObject(object));
		},
		
		/**
		 * Adds a single language item to the store.
		 * 
		 * @param	{string}	key
		 * @param	{string}	value
		 */
		add: function(key, value) {
			_languageItems.set(key, value);
		},
		
		/**
		 * Fetches the language item specified by the given key.
		 * If the language item is a string it will be evaluated as
		 * WoltLabSuite/Core/Template with the given parameters.
		 * 
		 * @param	{string}	key		Language item to return.
		 * @param	{Object=}	parameters	Parameters to provide to WoltLabSuite/Core/Template.
		 * @return	{string}
		 */
		get: function(key, parameters) {
			if (!parameters) parameters = { };
			
			var value = _languageItems.get(key);
			
			if (value === undefined) {
				return key;
			}
			
			// fetch Template, as it cannot be provided because of a circular dependency
			if (Template === undefined) Template = require('WoltLabSuite/Core/Template');
			
			if (typeof value === 'string') {
				// lazily convert to WCF.Template
				try {
					_languageItems.set(key, new Template(value));
				}
				catch (e) {
					_languageItems.set(key, new Template('{literal}' + value.replace(/\{\/literal\}/g, '{/literal}{ldelim}/literal}{literal}') + '{/literal}'));
				}
				value = _languageItems.get(key);
			}
			
			if (value instanceof Template) {
				value = value.fetch(parameters);
			}
			
			return value;
		}
	};
	
	return Language;
});
