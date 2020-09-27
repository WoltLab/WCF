/**
 * Provides the style editor.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/Style/Editor
 */
define(['Ajax', 'Core', 'Dictionary', 'Dom/Util', 'EventHandler', 'Ui/Screen'], function(Ajax, Core, Dictionary, DomUtil, EventHandler, UiScreen) {
	"use strict";
	
	var _stylePreviewRegions = new Dictionary();
	var _stylePreviewRegionMarker = null;
	var _stylePreviewWindow = elById('spWindow');
	
	var _isVisible = true;
	var _isSmartphone = false;
	var _updateRegionMarker = null;
	
	/**
	 * @module	WoltLabSuite/Core/Acp/Ui/Style/Editor
	 */
	return {
		/**
		 * Sets up dynamic style options.
		 */
		setup: function(options) {
			this._handleLayoutWidth();
			this._handleScss(options.isTainted);
			
			if (!options.isTainted) {
				this._handleProtection(options.styleId);
			}
			
			this._initVisualEditor(options.styleRuleMap);
			
			UiScreen.on('screen-sm-down', {
				match: this.hideVisualEditor.bind(this),
				unmatch: this.showVisualEditor.bind(this),
				setup: this.hideVisualEditor.bind(this)
			});
			
			var callbackRegionMarker = function () {
				if (_isVisible) _updateRegionMarker();
			};
			window.addEventListener('resize', callbackRegionMarker);
			EventHandler.add('com.woltlab.wcf.AcpMenu', 'resize', callbackRegionMarker);
			EventHandler.add('com.woltlab.wcf.simpleTabMenu_styleTabMenuContainer', 'select', function (data) {
				_isVisible = (data.activeName === 'colors');
				callbackRegionMarker();
			});
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
		 * Handles SCSS input fields.
		 * 
		 * @param	{boolean}	isTainted	false if style is in protected mode
		 */
		_handleScss: function(isTainted) {
			var individualScss = elById('individualScss');
			var overrideScss = elById('overrideScss');
			
			if (isTainted) {
				EventHandler.add('com.woltlab.wcf.simpleTabMenu_styleTabMenuContainer', 'select', function(data) {
					individualScss.codemirror.refresh();
					overrideScss.codemirror.refresh();
				});
			}
			else {
				EventHandler.add('com.woltlab.wcf.simpleTabMenu_advanced', 'select', function(data) {
					if (data.activeName === 'advanced-custom') {
						elById('individualScssCustom').codemirror.refresh();
						elById('overrideScssCustom').codemirror.refresh();
					}
					else if (data.activeName === 'advanced-original') {
						individualScss.codemirror.refresh();
						overrideScss.codemirror.refresh();
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
			
			button.addEventListener(WCF_CLICK_EVENT, function() {
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
			elBySelAll('[data-region]', _stylePreviewWindow, function(region) {
				_stylePreviewRegions.set(elData(region, 'region'), region);
			});
			
			_stylePreviewRegionMarker = elCreate('div');
			_stylePreviewRegionMarker.id = 'stylePreviewRegionMarker';
			_stylePreviewRegionMarker.innerHTML = '<div id="stylePreviewRegionMarkerBottom"></div>';
			elHide(_stylePreviewRegionMarker);
			elById('colors').appendChild(_stylePreviewRegionMarker);
			
			var container = elById('spSidebar');
			var select = elById('spCategories');
			var lastValue = select.value;
			
			_updateRegionMarker = function() {
				if (_isSmartphone) {
					return;
				}
				
				if (lastValue === 'none') {
					elHide(_stylePreviewRegionMarker);
					return;
				}
				
				var region = _stylePreviewRegions.get(lastValue);
				var rect = region.getBoundingClientRect();
				
				var top = rect.top + (window.scrollY || window.pageYOffset);
				
				DomUtil.setStyles(_stylePreviewRegionMarker, {
					height: (region.clientHeight + 20) + 'px',
					left: (rect.left + document.body.scrollLeft - 10) + 'px',
					top: (top - 10) + 'px',
					width: (region.clientWidth + 20) + 'px'
				});
				
				elShow(_stylePreviewRegionMarker);
				
				top = DomUtil.offset(region).top;
				// `+ 80` = account for sticky header + selection markers (20px)
				var firstVisiblePixel = (window.pageYOffset || window.scrollY) + 80;
				if (firstVisiblePixel > top) {
					window.scrollTo(0, Math.max(top - 80, 0));
				}
				else {
					var lastVisiblePixel = window.innerHeight + (window.pageYOffset || window.scrollY);
					if (lastVisiblePixel < top) {
						window.scrollTo(0, top);
					}
					else {
						var bottom = top + region.offsetHeight + 20;
						if (lastVisiblePixel < bottom) {
							window.scrollBy(0, bottom - top);
						}
					}
				}
			};
			
			var apiVersions = elBySel('.spSidebarBox[data-category="apiVersion"]', container);
			var element;
			var callbackChange = function() {
				element = elBySel('.spSidebarBox[data-category="' + lastValue + '"]', container);
				elHide(element);
				
				lastValue = select.value;
				element = elBySel('.spSidebarBox[data-category="' + lastValue + '"]', container);
				elShow(element);
				
				var showCompatibilityNotice = elBySel('.spApiVersion', element) !== null;
				window[showCompatibilityNotice ? 'elShow' : 'elHide'](apiVersions);
				
				// set region marker
				_updateRegionMarker();
			};
			select.addEventListener('change', callbackChange);
			
			// apply CSS rules
			var style = elCreate('style');
			style.appendChild(document.createTextNode(''));
			elData(style, 'created-by', 'WoltLab/Acp/Ui/Style/Editor');
			document.head.appendChild(style);
			
			function updateCSSRule(identifier, value) {
				if (styleRuleMap[identifier] === undefined) {
					return;
				}
				
				var rule = styleRuleMap[identifier].replace(/VALUE/g, value + ' !important');
				if (!rule) {
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
					try {
						style.sheet.insertRule(rules[i], style.sheet.cssRules.length);
					}
					catch (e) {
						// ignore errors for unknown placeholder selectors
						if (!/[a-z]+\-placeholder/.test(rules[i])) {
							console.debug(e.message);
						}
					}
				}
			}
			
			var elements = elByClass('styleVariableColor', elById('spVariablesWrapper'));
			[].forEach.call(elements, function(colorField) {
				var variableName = elData(colorField, 'store').replace(/_value$/, '');
				
				var observer = new MutationObserver(function(mutations) {
					mutations.forEach(function(mutation) {
						if (mutation.attributeName === 'style') {
							updateCSSRule(variableName, colorField.style.getPropertyValue('background-color'));
						}
					});
				});
				
				observer.observe(colorField, {
					attributes: true
				});
				
				updateCSSRule(variableName, colorField.style.getPropertyValue('background-color'));
			});
			
			// category selection by clicking on the area
			var buttonToggleColorPalette = elBySel('.jsButtonToggleColorPalette');
			var buttonSelectCategoryByClick = elBySel('.jsButtonSelectCategoryByClick');
			var toggleSelectionMode = function() {
				buttonSelectCategoryByClick.classList.toggle('active');
				buttonToggleColorPalette.classList.toggle('disabled');
				_stylePreviewWindow.classList.toggle('spShowRegions');
				_stylePreviewRegionMarker.classList.toggle('forceHide');
				select.disabled = !select.disabled;
			};
			
			buttonSelectCategoryByClick.addEventListener(WCF_CLICK_EVENT, function (event) {
				event.preventDefault();
				
				toggleSelectionMode();
			});
			
			elBySelAll('[data-region]', _stylePreviewWindow, function (region) {
				region.addEventListener(WCF_CLICK_EVENT, function (event) {
					if (!_stylePreviewWindow.classList.contains('spShowRegions')) {
						return;
					}
					
					event.preventDefault();
					event.stopPropagation();
					
					toggleSelectionMode();
					
					select.value = elData(region, 'region');
					
					// Programmatically trigger the change event handler, rather than dispatching an event,
					// because Firefox fails to execute the event if it has previously been disabled.
					// See https://bugzilla.mozilla.org/show_bug.cgi?id=1426856
					callbackChange();
				});
			});
			
			// toggle view
			var spSelectCategory = elById('spSelectCategory');
			buttonToggleColorPalette.addEventListener(WCF_CLICK_EVENT, function (event) {
				event.preventDefault();
				
				buttonSelectCategoryByClick.classList.toggle('disabled');
				elToggle(spSelectCategory);
				buttonToggleColorPalette.classList.toggle('active');
				_stylePreviewWindow.classList.toggle('spColorPalette');
				_stylePreviewRegionMarker.classList.toggle('forceHide');
				select.disabled = !select.disabled;
			});
		},
		
		hideVisualEditor: function() {
			elHide(_stylePreviewWindow);
			elById('spVariablesWrapper').style.removeProperty('transform');
			elHide(elById('stylePreviewRegionMarker'));
			
			_isSmartphone = true;
		},
		
		showVisualEditor: function() {
			elShow(_stylePreviewWindow);
			
			window.setTimeout(function() {
				Core.triggerEvent(elById('spCategories'), 'change');
			}, 100);
			
			_isSmartphone = false;
		}
	};
});
