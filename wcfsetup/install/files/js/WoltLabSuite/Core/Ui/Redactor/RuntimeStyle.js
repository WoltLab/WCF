/**
 * Provides an easy API to register CSS styles on runtime.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Redactor/RuntimeStyle
 */
define([], function () {
	"use strict";
	
	var _knownClasses = [];
	var _style = null;
	
	/**
	 * @exports     WoltLabSuite/Core/Ui/Redactor/RuntimeStyle
	 */
	return {
		/**
		 * Adds a new style rule for provided class name.
		 * 
		 * @param       {string}        className       CSS class name without a leading dot
		 * @param       {string}        definitions     CSS definitions
		 */
		add: function (className, definitions) {
			if (_knownClasses.indexOf(className) !== -1) {
				return;
			}
			
			if (_style === null) {
				_style = elCreate('style');
				_style.appendChild(document.createTextNode(''));
				elData(_style, 'created-by', 'WoltLabSuite/Core/Ui/Redactor/RuntimeStyle');
				document.head.appendChild(_style);
			}
			
			//noinspection JSUnresolvedVariable
			_style.sheet.insertRule('.' + className + ' { ' + definitions + ' }', _style.sheet.cssRules.length);
			_knownClasses.push(className);
		}
	};
});