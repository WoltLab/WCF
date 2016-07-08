/**
 * Converts `<woltlab-metacode>` into the bbcode representation.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Redactor/Metacode
 */
define(['WoltLab/WCF/Ui/Page/Search'], function(UiPageSearch) {
	"use strict";
	
	function UiRedactorPage(editor, button) { this.init(editor, button); }
	UiRedactorPage.prototype = {
		init: function (editor, button) {
			this._editor = editor;
			
			button.addEventListener(WCF_CLICK_EVENT, this._click.bind(this))
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
