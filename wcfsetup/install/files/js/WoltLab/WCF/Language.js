/**
 * Manages language items.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Language
 */
define(['Dictionary', './Template'], function(Dictionary, Template) {
	"use strict";
	
	var _languageItems = new Dictionary();
	
	/**
	 * @exports	WoltLab/WCF/Language
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
		 * WoltLab/WCF/Template with the given parameters.
		 * 
		 * @param	{string}	key		Language item to return.
		 * @param	{Object=}	parameters	Parameters to provide to WoltLab/WCF/Template.
		 * @return	{string}
		 */
		get: function(key, parameters) {
			if (!parameters) parameters = { };
			
			var value = _languageItems.get(key);
			
			if (value === undefined) {
				console.warn("Attempt to retrieve unknown phrase '" + key + "'.");
				return key;
			}
			
			if (typeof value === 'string') {
				// lazily convert to WCF.Template
				_languageItems.set(key, new Template(value));
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
