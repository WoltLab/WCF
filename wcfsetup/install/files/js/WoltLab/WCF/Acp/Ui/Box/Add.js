/**
 * Provides the dialog overlay to add a new box.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Acp/Ui/Box/Add
 */
define(['Core', 'Language', 'Ui/Dialog'], function(Core, Language, UiDialog) {
	"use strict";
	
	var _link;
	
	/**
	 * @exports     WoltLab/WCF/Acp/Ui/Box/Add
	 */
	return {
		/**
		 * Initializes the box add handler.
		 * 
		 * @param       {string}        link    redirect URL
		 */
		init: function(link) {
			_link = link;
			
			var buttons = elBySelAll('.jsButtonBoxAdd');
			for (var i = 0, length = buttons.length; i < length; i++) {
				buttons[i].addEventListener(WCF_CLICK_EVENT, this.openDialog.bind(this));
			}
		},
		
		/**
		 * Opens the 'Add Box' dialog.
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
				id: 'boxAddDialog',
				options: {
					onSetup: function(content) {
						elBySel('button', content).addEventListener(WCF_CLICK_EVENT, function(event) {
							event.preventDefault();
							
							var boxType = elBySel('input[name="boxType"]:checked', content).value;
							var isMultilingual = 0;
							if (boxType !== 'system') isMultilingual = elBySel('input[name="isMultilingual"]:checked', content).value;
							
							window.location = _link.replace(/{\$boxType}/, boxType).replace(/{\$isMultilingual}/, isMultilingual);
						});
						
						elBySelAll('input[type="radio"][name="boxType"]', content, function(element) {
							element.addEventListener('change', function(event) {
								elBySelAll('input[type="radio"][name="isMultilingual"]', content, function(element) {
									element.disabled = (event.currentTarget.value === 'system');
								});
							});
						});
					},
					title: Language.get('wcf.acp.box.add')
				}
			};
		}
	}
});
