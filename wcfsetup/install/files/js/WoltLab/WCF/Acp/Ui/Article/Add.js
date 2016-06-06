/**
 * Provides the dialog overlay to add a new article.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Acp/Ui/Article/Add
 */
define(['Core', 'Language', 'Ui/Dialog'], function(Core, Language, UiDialog) {
	"use strict";
	
	var _link;
	
	/**
	 * @exports     WoltLab/WCF/Acp/Ui/Article/Add
	 */
	return {
		/**
		 * Initializes the article add handler.
		 * 
		 * @param       {string}        link    redirect URL
		 */
		init: function(link) {
			_link = link;
			
			var buttons = elBySelAll('.jsButtonArticleAdd');
			for (var i = 0, length = buttons.length; i < length; i++) {
				buttons[i].addEventListener(WCF_CLICK_EVENT, this.openDialog.bind(this));
			}
		},
		
		/**
		 * Opens the 'Add Article' dialog.
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
				id: 'articleAddDialog',
				options: {
					onSetup: function(content) {
						elBySel('button', content).addEventListener(WCF_CLICK_EVENT, function(event) {
							event.preventDefault();
							
							var isMultilingual = elBySel('input[name="isMultilingual"]:checked', content).value;
							
							window.location = _link.replace(/{\$isMultilingual}/, isMultilingual);
						});
					},
					title: Language.get('wcf.acp.article.add')
				}
			};
		}
	}
});
