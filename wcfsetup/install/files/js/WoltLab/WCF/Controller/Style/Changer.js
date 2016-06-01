/**
 * Dialog based style changer.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Controller/Style/Changer
 */
define(['Ajax', 'Language', 'Ui/Dialog'], function(Ajax, Language, UiDialog) {
	"use strict";
	
	/**
	 * @exports	WoltLab/WCF/Controller/Style/Changer
	 */
	var ControllerStyleChanger = {
		/**
		 * Adds the style changer to the bottom navigation.
		 */
		setup: function() {
			var list = elBySel('#footerNavigation > ul.navigationItems');
			if (list === null) {
				return;
			}
			
			var listItem = elCreate('li');
			listItem.classList.add('styleChanger');
			listItem.addEventListener(WCF_CLICK_EVENT, this.showDialog.bind(this));
			
			var link = elCreate('a');
			elAttr(link, 'href', '#');
			link.textContent = Language.get('wcf.style.changeStyle');
			listItem.appendChild(link);
			
			list.appendChild(listItem);
		},
		
		/**
		 * Loads and displays the style change dialog.
		 * 
		 * @param	{object}	event	event object
		 */
		showDialog: function(event) {
			event.preventDefault();
			
			UiDialog.open(this);
		},
		
		_dialogSetup: function() {
			return {
				id: 'styleChanger',
				options: {
					disableContentPadding: true,
					title: Language.get('wcf.style.changeStyle')
				},
				source: {
					data: {
						actionName: 'getStyleChooser',
						className: 'wcf\\data\\style\\StyleAction'
					},
					after: (function(content) {
						var styles = elBySelAll('.styleList > li', content);
						for (var i = 0, length = styles.length; i < length; i++) {
							var style = styles[i];
							
							style.classList.add('pointer');
							style.addEventListener(WCF_CLICK_EVENT, this._click.bind(this));
						}
					}).bind(this)
				}
			};
		},
		
		/**
		 * Changes the style and reloads current page.
		 * 
		 * @param	{object}	event	event object
		 */
		_click: function(event) {
			event.preventDefault();
			
			Ajax.apiOnce({
				data: {
					actionName: 'changeStyle',
					className: 'wcf\\data\\style\\StyleAction',
					objectIDs: [ elData(event.currentTarget, 'style-id') ]
				},
				success: function() { window.location.reload(); }
			});
		}
	};
	
	return ControllerStyleChanger;
});
