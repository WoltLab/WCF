/**
 * Provides the sidebar toggle button.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Collapsible/Sidebar
 */
define(['Ajax', 'Language', 'Dom/Util'], function(Ajax, Language, DomUtil) {
	"use strict";
	
	var _isOpen = false;
	var _main = null;
	var _name = '';
	
	/**
	 * @module	WoltLab/WCF/Ui/Collapsible/Sidebar
	 */
	var UiCollapsibleSidebar = {
		/**
		 * Sets up the toggle button.
		 */
		setup: function() {
			var sidebar = document.querySelector('.sidebar');
			if (sidebar === null) {
				return;
			}
			
			_isOpen = (sidebar.getAttribute('data-is-open') === 'true');
			_main = document.getElementById('main');
			_name = sidebar.getAttribute('data-sidebar-name');
			
			this._createUI(sidebar);
			
			_main.classList[(_isOpen ? 'remove' : 'add')]('sidebarCollapsed');
		},
		
		/**
		 * Creates the toggle button.
		 * 
		 * @param	{Element}	sidebar		sidebar element
		 */
		_createUI: function(sidebar) {
			var button = document.createElement('a');
			button.href = '#';
			button.className = 'collapsibleButton jsTooltip';
			button.setAttribute('title', Language.get('wcf.global.button.collapsible'));
			
			var span = document.createElement('span');
			span.appendChild(button);
			DomUtil.prepend(span, sidebar);
			
			button.addEventListener('click', this._click.bind(this));
		},
		
		/**
		 * Toggles the sidebar on click.
		 * 
		 * @param	{object}	event		event object
		 */
		_click: function(event) {
			event.preventDefault();
			
			_isOpen = (_isOpen === false);
			
			Ajax.api(this, {
				isOpen: ~~_isOpen
			});
		},
		
		_ajaxSetup: function() {
			return {
				data: {
					actionName: 'toggle',
					className: 'wcf\\system\\user\\collapsible\\content\\UserCollapsibleSidebarHandler',
					sidebarName: _name
				},
				url: 'index.php/AJAXInvoke/?t=' + SECURITY_TOKEN + SID_ARG_2ND
			};
		},
		
		_ajaxSuccess: function(data) {
			_main.classList[(_isOpen ? 'remove' : 'add')]('sidebarCollapsed');
		}
	};
	
	return UiCollapsibleSidebar;
});
