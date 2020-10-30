/**
 * Provides the dialog overlay to add a new page.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/Page/Add
 */
define(['Core', 'Language', 'Ui/Dialog'], function(Core, Language, UiDialog) {
	"use strict";
	
	var _languages, _link;
	
	/**
	 * @exports     WoltLabSuite/Core/Acp/Ui/Page/Add
	 */
	return {
		/**
		 * Initializes the page add handler.
		 * 
		 * @param       {string}        link            redirect URL
		 * @param       {int}           languages       number of available languages
		 */
		init: function(link, languages) {
			_languages = languages;
			_link = link;
			
			var buttons = elBySelAll('.jsButtonPageAdd');
			for (var i = 0, length = buttons.length; i < length; i++) {
				buttons[i].addEventListener('click', this.openDialog.bind(this));
			}
		},
		
		/**
		 * Opens the 'Add Page' dialog.
		 * 
		 * @param       {Event=}        event   event object
		 */
		openDialog: function(event) {
			if (event instanceof Event) {
				event.preventDefault();
			}
			
			UiDialog.open(this);
		},
		
		_dialogSetup: function() {
			return {
				id: 'pageAddDialog',
				options: {
					onSetup: function(content) {
						elBySel('button', content).addEventListener('click', function(event) {
							event.preventDefault();
							
							var pageType = elBySel('input[name="pageType"]:checked', content).value;
							var isMultilingual = (_languages > 1) ? elBySel('input[name="isMultilingual"]:checked', content).value : 0;
							
							window.location = _link.replace(/{\$pageType}/, pageType).replace(/{\$isMultilingual}/, isMultilingual);
						});
					},
					title: Language.get('wcf.acp.page.add')
				}
			};
		}
	};
});
