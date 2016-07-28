/**
 * Dialog based style changer.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Controller/Style/Changer
 */
define(['Ajax', 'Language', 'Ui/Dialog'], function(Ajax, Language, UiDialog) {
	"use strict";
	
	/**
	 * @exports	WoltLab/WCF/Controller/Style/Changer
	 */
	return {
		/**
		 * Adds the style changer to the bottom navigation.
		 */
		setup: function() {
			var link = elBySel('.jsButtonStyleChanger');
			if (link) {
				link.addEventListener(WCF_CLICK_EVENT, this.showDialog.bind(this));
			}
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
});
