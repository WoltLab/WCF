/**
 * Provides the basic core functionality.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Core
 */
define(['Ajax', 'EventHandler'], function(Ajax, EventHandler) {
	"use strict";
	
	/**
	 * @module	WoltLab/WCF/Acp/Ui/Style/Editor
	 */
	var AcpUiStyleEditor = {
		/**
		 * Sets up dynamic style options.
		 */
		setup: function(options) {
			this._handleLayoutWidth();
			this._handleLess(options.isTainted);
			
			if (!options.isTainted) {
				this._handleProtection(options.styleId);
			}
		},
		
		/**
		 * Handles the switch between static and fluid layout.
		 */
		_handleLayoutWidth: function() {
			var useFluidLayout = elById('useFluidLayout');
			var fluidLayoutMinWidth = elById('fluidLayoutMinWidth');
			var fluidLayoutMaxWidth = elById('fluidLayoutMaxWidth');
			var fixedLayoutVariables = elById('fixedLayoutVariables');
			
			function change() {
				var checked = useFluidLayout.checked;
				
				fluidLayoutMinWidth.style[(checked ? 'remove' : 'set') + 'Property']('display', 'none');
				fluidLayoutMaxWidth.style[(checked ? 'remove' : 'set') + 'Property']('display', 'none');
				fixedLayoutVariables.style[(checked ? 'set' : 'remove') + 'Property']('display', 'none');
			}
			
			useFluidLayout.addEventListener('change', change);
			
			change();
		},
		
		/**
		 * Handles LESS input fields.
		 * 
		 * @param	{boolean}	isTainted	false if style is in protected mode
		 */
		_handleLess: function(isTainted) {
			var individualLess = elById('individualLess');
			var overrideLess = elById('overrideLess');
			
			if (isTainted) {
				EventHandler.add('com.woltlab.wcf.simpleTabMenu_styleTabMenuContainer', 'select', function(data) {
					individualLess.codemirror.refresh();
					overrideLess.codemirror.refresh();
				});
			}
			else {
				EventHandler.add('com.woltlab.wcf.simpleTabMenu_advanced', 'select', function(data) {
					if (data.activeName === 'advanced-custom') {
						elById('individualLessCustom').codemirror.refresh();
						elById('overrideLessCustom').codemirror.refresh();
					}
					else if (data.activeName === 'advanced-original') {
						individualLess.codemirror.refresh();
						overrideLess.codemirror.refresh();
					}
				});
			}
		},
		
		_handleProtection: function(styleId) {
			var button = elById('styleDisableProtectionSubmit');
			var checkbox = elById('styleDisableProtectionConfirm');
			
			checkbox.addEventListener('change', function() {
				button.disabled = !checkbox.checked;
			});
			
			button.addEventListener('click', function() {
				Ajax.apiOnce({
					data: {
						actionName: 'markAsTainted',
						className: 'wcf\\data\\style\\StyleAction',
						objectIDs: [styleId]
					},
					success: function() {
						window.location.reload();
					}
				});
			});
		}
	};
	
	return AcpUiStyleEditor;
});
