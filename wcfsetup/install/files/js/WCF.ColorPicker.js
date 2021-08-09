"use strict";

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * Color picker for WCF
	 *
	 * @author        Alexander Ebert
	 * @copyright	2001-2019 WoltLab GmbH
	 * @license        GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
	 * @deprecated  5.5, use `WoltLabSuite/Core/Ui/Color/Picker` instead
	 */
	WCF.ColorPicker = Class.extend({
		/**
		 * Initializes the WCF.ColorPicker class.
		 *
		 * @param        string                selector
		 */
		init: function (selector) {
			this.colorPickers = [];
			
			require(['WoltLabSuite/Core/Ui/Color/Picker'], (UiColorPicker) => {
				const elements = document.querySelectorAll(selector);
				if (!elements.length) {
					console.debug("[WCF.ColorPicker] Selector does not match any element, aborting.");
					return;
				}
				
				elements.forEach((element) => {
					this.colorPickers.push(new UiColorPicker(element));
				})
			});
		},
		
		/**
		 * Sets an optional submit callback.
		 *
		 * @param        {Function}        callback
		 */
		setCallbackSubmit: function (callback) {
			this.colorPickers.forEach((colorPicker) => {
				colorPicker.setCallbackSubmit(callback);
			});
		},
		
		/**
		 * @see WoltLabSuite/Core/ColorUtil.hsvToRgb()
		 */
		hsvToRgb: function (h, s, v) {
			return window.__wcf_bc_colorUtil.hsvToRgb(h, s, v);
		},
		
		/**
		 * @see WoltLabSuite/Core/ColorUtil.rgbToHsv()
		 */
		rgbToHsv: function (r, g, b) {
			return window.__wcf_bc_colorUtil.rgbToHsv(r, g, b);
		},
		
		/**
		 * @see WoltLabSuite/Core/ColorUtil.hexToRgb()
		 */
		hexToRgb: function (hex) {
			return window.__wcf_bc_colorUtil.hexToRgb(hex);
		},
		
		/**
		 * @see WoltLabSuite/Core/ColorUtil.rgbToHex()
		 */
		rgbToHex: function (r, g, b) {
			return window.__wcf_bc_colorUtil.rgbToHex(r, g, b);
		}
	});
	
	(function () {
		if (window.__wcf_bc_colorUtil === undefined) {
			require(['ColorUtil'], function (ColorUtil) {
				// void call to force module evaluation
			});
		}
		
		if (typeof window.__wcf_bc_colorPickerInit === 'function') {
			window.__wcf_bc_colorPickerInit();
		}
	})();
}
else {
	WCF.ColorPicker = Class.extend({
		init: function() {},
		hsvToRgb: function (h, s, v) { return window.__wcf_bc_colorUtil.hsvToRgb(h, s, v); },
		rgbToHsv: function (r, g, b) { return window.__wcf_bc_colorUtil.rgbToHsv(r, g, b); },
		hexToRgb: function (hex) { return window.__wcf_bc_colorUtil.hexToRgb(hex); },
		rgbToHex: function (r, g, b) { return window.__wcf_bc_colorUtil.rgbToHex(r, g, b); }
	});
}