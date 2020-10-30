/**
 * Converts `<woltlab-metacode>` into the bbcode representation.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Redactor/Page
 */
define(['WoltLabSuite/Core/Ui/Page/Search'], function(UiPageSearch) {
	"use strict";
	
	if (!COMPILER_TARGET_DEFAULT) {
		var Fake = function() {};
		Fake.prototype = {
			init: function() {},
			_click: function() {},
			_insert: function() {}
		};
		return Fake;
	}
	
	function UiRedactorPage(editor, button) { this.init(editor, button); }
	UiRedactorPage.prototype = {
		init: function (editor, button) {
			this._editor = editor;
			
			button.addEventListener('click', this._click.bind(this));
		},
		
		_click: function (event) {
			event.preventDefault();
			
			UiPageSearch.open(this._insert.bind(this));
		},
		
		_insert: function (pageID) {
			this._editor.buffer.set();
			
			this._editor.insert.text("[wsp='" + pageID + "'][/wsp]");
		}
	};
	
	return UiRedactorPage;
});
