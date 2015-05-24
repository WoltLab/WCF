/**
 * Dialog based style changer.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Controller/Style/Changer
 */
define(['Ajax', 'Language', 'UI/Dialog'], function(Ajax, Language, UIDialog) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function ControllerStyleChanger() {};
	ControllerStyleChanger.prototype = {
		/**
		 * Adds the style changer to the bottom navigation.
		 */
		setup: function() {
			var list = document.querySelector('#footerNavigation > ul.navigationItems');
			if (list === null) {
				return;
			}
			
			var listItem = document.createElement('li');
			listItem.classList.add('styleChanger');
			listItem.addEventListener('click', this.showDialog.bind(this));
			
			var link = document.createElement('a');
			link.setAttribute('href', '#');
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
			
			if (UIDialog.getDialog('styleChanger') === undefined) {
				Ajax.apiOnce({
					data: {
						actionName: 'getStyleChooser',
						className: 'wcf\\data\\style\\StyleAction'
					},
					success: (function(data) {
						var dialog = UIDialog.open('styleChanger', data.returnValues.template, {
							disableContentPadding: true,
							title: Language.get('wcf.style.changeStyle')
						});
						
						var styles = dialog.content.querySelectorAll('.styleList > li');
						for (var i = 0, length = styles.length; i < length; i++) {
							var style = styles[i];
							
							style.classList.add('pointer');
							style.addEventListener('click', this._click.bind(this));
						}
					}).bind(this)
				});
			}
			else {
				UIDialog.open('styleChanger');
			}
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
					objectIDs: [ event.currentTarget.getAttribute('data-style-id') ]
				},
				success: function() { window.location.reload(); }
			});
		}
	};
	
	return new ControllerStyleChanger();
});
