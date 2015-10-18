/**
 * Provides the basic core functionality.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Core
 */
define(['Ajax', 'Dictionary', 'Dom/Util', 'EventHandler'], function(Ajax, Dictionary, DomUtil, EventHandler) {
	"use strict";
	
	var _stylePreviewRegions = new Dictionary();
	var _stylePreviewRegionMarker = null;
	
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
			
			this._initVisualEditor(options.styleRuleMap);
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
		},
		
		_initVisualEditor: function(styleRuleMap) {
			var regions = elBySelAll('#spWindow [data-region]');
			for (var i = 0, length = regions.length; i < length; i++) {
				_stylePreviewRegions.set(elData(regions[i], 'region'), regions[i]);
			}
			
			_stylePreviewRegionMarker = elCreate('div');
			_stylePreviewRegionMarker.id = 'stylePreviewRegionMarker';
			_stylePreviewRegionMarker.innerHTML = '<div id="stylePreviewRegionMarkerBottom"></div>';
			elHide(_stylePreviewRegionMarker);
			elById('colors').appendChild(_stylePreviewRegionMarker);
			
			var container = elById('spSidebar');
			var select = elById('spCategories');
			var lastValue = select.value;
			
			function updateRegionMarker() {
				if (lastValue === 'none') {
					elHide(_stylePreviewRegionMarker);
					updateWrapperPosition(null);
					scrollToRegion(null);
					return;
				}
				
				var region = _stylePreviewRegions.get(lastValue);
				var rect = region.getBoundingClientRect();
				
				var top = rect.top + document.body.scrollTop;
				
				DomUtil.setStyles(_stylePreviewRegionMarker, {
					height: (region.clientHeight + 20) + 'px',
					left: (rect.left + document.body.scrollLeft - 10) + 'px',
					top: (top - 10) + 'px',
					width: (region.clientWidth + 20) + 'px'
				});
				
				elShow(_stylePreviewRegionMarker);
				
				updateWrapperPosition(region);
				scrollToRegion(top);
			}
			
			var variablesWrapper = elById('stylePreviewVariablesWrapper');
			function updateWrapperPosition(region) {
				var fromTop = 0;
				if (region !== null) {
					fromTop = (region.offsetTop - variablesWrapper.offsetTop) - 10;
					
					var styles = window.getComputedStyle(region);
					if (styles.getPropertyValue('position') === 'absolute') {
						fromTop += region.offsetParent.offsetTop;
					}
				}
				
				if (fromTop <= 0) {
					variablesWrapper.style.removeProperty('transform');
				}
				else {
					// ensure that the wrapper does not exceed the bottom boundary
					var maxHeight = variablesWrapper.parentNode.clientHeight;
					var wrapperHeight = variablesWrapper.clientHeight;
					if (wrapperHeight + fromTop > maxHeight) {
						fromTop = maxHeight - wrapperHeight;
					}
					
					variablesWrapper.style.setProperty('transform', 'translateY(' + fromTop + 'px)');
				}
			}
			
			var pageHeader = elById('pageHeader');
			function scrollToRegion(top) {
				if (top === null) {
					top = variablesWrapper.offsetTop - 60;
				}
				else {
					// use the region marker as an offset
					top -= 60;
				}
				
				// account for sticky header
				top -= 60;
				
				window.scrollTo(0, top);
			}
			
			var selectContainer = elBySel('.stylePreviewVariablesContainer:first-child');
			var element;
			select.addEventListener('change', function() {
				element = elBySel('.spSidebarBox[data-category="' + lastValue + '"]', container);
				elHide(element);
				
				lastValue = select.value;
				element = elBySel('.spSidebarBox[data-category="' + lastValue + '"]', container);
				elShow(element);
				
				// set region marker
				updateRegionMarker();
				
				selectContainer.classList[(lastValue === 'none' ? 'remove' : 'add')]('pointer');
			});
			
			
			// apply CSS rules
			var style = document.createElement('style');
			style.appendChild(document.createTextNode(''));
			elData(style, 'created-by', 'WoltLab/Acp/Ui/Style/Editor');
			document.head.appendChild(style);
			
			function updateCSSRule(identifier, value, isInit) {
				if (styleRuleMap[identifier] === undefined) {
					console.debug(identifier);
					return;
				}
				
				var rule = styleRuleMap[identifier].replace(/VALUE/g, value + ' !important');
				if (!rule) {
					console.debug(identifier);
					return;
				}
				
				var rules = [];
				if (rule.indexOf('__COMBO_RULE__')) {
					rules = rule.split('__COMBO_RULE__');
				}
				else {
					rules = [rule];
				}
				
				for (var i = 0, length = rules.length; i < length; i++) {
					style.sheet.insertRule(rules[i], style.sheet.cssRules.length);
				}
			}
			console.debug(styleRuleMap);
			var elements = elByClass('styleVariableColor', variablesWrapper);
			[].forEach.call(elements, function(colorField) {
				var variableName = elData(colorField, 'store').replace(/_value$/, '');
				console.debug(variableName);
				var observer = new MutationObserver(function(mutations) {
					mutations.forEach(function(mutation) {
						if (mutation.attributeName === 'style') {
							updateCSSRule(variableName, colorField.style.getPropertyValue('background-color'));
							console.debug(mutation);
						}
					});
				});
				
				observer.observe(colorField, {
					attributes: true
				});
				
				updateCSSRule(variableName, colorField.style.getPropertyValue('background-color'));
			});
		}
	};
	
	return AcpUiStyleEditor;
});
