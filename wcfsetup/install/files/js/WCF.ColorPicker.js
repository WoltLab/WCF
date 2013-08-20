/**
 * Color picker for WCF
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.ColorPicker = Class.extend({
	/**
	 * hue bar element
	 * @var	jQuery
	 */
	_bar: null,
	
	/**
	 * bar selector is being moved
	 * @var	boolean
	 */
	_barActive: false,
	
	/**
	 * bar selector element
	 * @var	jQuery
	 */
	_barSelector: null,
	
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * initialization state
	 * @var	boolean
	 */
	_didInit: false,
	
	/**
	 * active element id
	 * @var	string
	 */
	_elementID: '',
	
	/**
	 * saturation and value gradient element
	 * @var	jQuery
	 */
	_gradient: null,
	
	/**
	 * gradient selector is being moved
	 * @var	boolean
	 */
	_gradientActive: false,
	
	/**
	 * gradient selector element
	 * @var	jQuery
	 */
	_gradientSelector: null,
	
	/**
	 * HEX input element
	 * @var	jQuery
	 */
	_hex: null,
	
	/**
	 * HSV representation
	 * @var	object
	 */
	_hsv: { },
	
	/**
	 * visual new color element
	 * @var	jQuery
	 */
	_newColor: null,
	
	/**
	 * visual previous color element
	 * @var	jQuery
	 */
	_oldColor: null,
	
	/**
	 * list of RGBa input elements
	 * @var	object
	 */
	_rgba: { },
	
	/**
	 * RegExp to parse rgba()
	 * @var	RegExp
	 */
	_rgbaRegExp: null,
	
	/**
	 * Initializes the WCF.ColorPicker class.
	 * 
	 * @param	string		selector
	 */
	init: function(selector) {
		this._elementID = '';
		this._hsv = { h: 0, s: 100, v: 100 };
		this._position = { };
		
		var $elements = $(selector);
		if (!$elements.length) {
			console.debug("[WCF.ColorPicker] Selector does not match any element, aborting.");
			return;
		}
		
		$elements.click($.proxy(this._open, this));
	},
	
	/**
	 * Opens the color picker overlay.
	 * 
	 * @param	object		event
	 */
	_open: function(event) {
		if (!this._didInit) {
			// init color picker on first usage
			this._initColorPicker();
			this._didInit = true;
		}
		
		// load values from element
		var $element = $(event.currentTarget);
		this._elementID = $element.wcfIdentify();
		this._parseColor($element);
		
		// set 'current' color
		var $rgb = this.hsvToRgb(this._hsv.h, this._hsv.s, this._hsv.v);
		this._oldColor.css({ backgroundColor: 'rgb(' + $rgb.r + ', ' + $rgb.g + ', ' + $rgb.b + ')' });
		
		this._dialog.wcfDialog({
			'title': WCF.Language.get('wcf.style.colorPicker')
		});
	},
	
	/**
	 * Parses the color of an element.
	 * 
	 * @param	jQuery		element
	 */
	_parseColor: function(element) {
		if (element.data('hsv') && element.data('rgb')) {
			// create an explicit copy here, otherwise it would be only a reference
			var $hsv = element.data('hsv');
			for (var $type in $hsv) {
				this._hsv[$type] = $hsv[$type];
			}
			this._updateValues(element.data('rgb'), true, true);
			this._rgba.a.val(parseInt(element.data('alpha')));
		}
		else {
			if (this._rgbaRegExp === null) {
				this._rgbaRegExp = new RegExp("^rgba\\((\\d{1,3}), ?(\\d{1,3}), ?(\\d{1,3}), ?(1|1\\.00?|0|0?\\.[0-9]{1,2})\\)$");
			}
			
			// parse value
			this._rgbaRegExp.exec(element.data('color'));
			var $alpha = RegExp.$4;
			// convert into x.yz
			if ($alpha.indexOf('.') === 0) {
				$alpha = "0" + $alpha;
			}
			$alpha *= 100;
			
			this._updateValues({
				r: RegExp.$1,
				g: RegExp.$2,
				b: RegExp.$3,
				a: Math.round($alpha)
			}, true, true);
		}
	},
	
	/**
	 * Initializes the color picker upon first usage.
	 */
	_initColorPicker: function() {
		this._dialog = $('<div id="colorPickerContainer" />').hide().appendTo(document.body);
		
		// create gradient
		this._gradient = $('<div id="colorPickerGradient" />').appendTo(this._dialog);
		this._gradientSelector = $('<span id="colorPickerGradientSelector"><span></span></span>').appendTo(this._gradient);
		
		// create bar
		this._bar = $('<div id="colorPickerBar" />').appendTo(this._dialog);
		this._barSelector = $('<span id="colorPickerBarSelector" />').appendTo(this._bar);
		
		// bind event listener
		this._gradient.mousedown($.proxy(this._mouseDownGradient, this));
		this._bar.mousedown($.proxy(this._mouseDownBar, this));
		
		var self = this;
		$(document).mouseup(function(event) {
			if (self._barActive) {
				self._barActive = false;
				self._mouseBar(event);
			}
			else if (self._gradientActive) {
				self._gradientActive = false;
				self._mouseGradient(event);
			}
		}).mousemove(function(event) {
			if (self._barActive) {
				self._mouseBar(event);
			}
			else if (self._gradientActive) {
				self._mouseGradient(event);
			}
		});
		
		this._initColorPickerForm();
	},
	
	/**
	 * Initializes the color picker input elements upon first usage.
	 */
	_initColorPickerForm: function() {
		var $form = $('<div id="colorPickerForm" />').appendTo(this._dialog);
		
		// new and current color
		$('<small>' + WCF.Language.get('wcf.style.colorPicker.new') + '</small>').appendTo($form);
		var $colors = $('<ul class="colors" />').appendTo($form);
		this._newColor = $('<li class="new" />').appendTo($colors);
		this._oldColor = $('<li class="old" />').appendTo($colors);
		$('<small>' + WCF.Language.get('wcf.style.colorPicker.current') + '</small>').appendTo($form);
		
		// RGBa input
		var $rgba = $('<ul class="rgba" />').appendTo($form);
		this._createInputElement('r', 'R', 0, 255).appendTo($rgba);
		this._createInputElement('g', 'G', 0, 255).appendTo($rgba);
		this._createInputElement('b', 'B', 0, 255).appendTo($rgba);
		this._createInputElement('a', 'a', 0, 100).appendTo($rgba);
		
		// HEX input
		var $hex = $('<ul class="hex"><li><label><span>#</span></label></li></ul>').appendTo($form);
		this._hex = $('<input type="text" maxlength="6" />').appendTo($hex.find('label'));
		
		// bind event listener
		this._rgba.r.blur($.proxy(this._blurRgba, this)).keyup($.proxy(this._keyUpRGBA, this));
		this._rgba.g.blur($.proxy(this._blurRgba, this)).keyup($.proxy(this._keyUpRGBA, this));
		this._rgba.b.blur($.proxy(this._blurRgba, this)).keyup($.proxy(this._keyUpRGBA, this));
		this._rgba.a.blur($.proxy(this._blurRgba, this)).keyup($.proxy(this._keyUpRGBA, this));
		this._hex.blur($.proxy(this._blurHex, this)).keyup($.proxy(this._keyUpHex, this));
		
		// submit button
		var $submitForm = $('<div class="formSubmit" />').appendTo(this._dialog);
		$('<button class="buttonPrimary">' + WCF.Language.get('wcf.style.colorPicker.button.apply') + '</button>').appendTo($submitForm).click($.proxy(this._submit, this));
		
		// allow pasting of colors like '#888888'
		var self = this;
		this._hex.on('paste', function() {
			self._hex.attr('maxlength', '7');
			
			setTimeout(function() {
				var $value = self._hex.val();
				if ($value.substring(0, 1) == '#') {
					$value = $value.substr(1);
				}
				
				if ($value.length > 6) {
					$value = $value.substring(0, 6);
				}
				
				self._hex.attr('maxlength', '6').val($value);
			}, 50);
		});
	},
	
	/**
	 * Submits form on enter.
	 */
	_keyUpRGBA: function(event) {
		if (event.which == 13) {
			this._blurRgba();
			this._submit();
		}
	},
	
	/**
	 * Submits form on enter.
	 */
	_keyUpHex: function(event) {
		if (event.which == 13) {
			this._blurHex();
			this._submit();
		}
	},
	
	/**
	 * Assigns the new color for active element.
	 */
	_submit: function() {
		var $rgb = this.hsvToRgb(this._hsv.h, this._hsv.s, this._hsv.v);
		
		// create an explicit copy here, otherwise it would be only a reference
		var $hsv = { };
		for (var $type in this._hsv) {
			$hsv[$type] = this._hsv[$type];
		}
		
		var $element = $('#' + this._elementID);
		$element.data('hsv', $hsv).css({ backgroundColor: 'rgb(' + $rgb.r + ', ' + $rgb.g + ', ' + $rgb.b + ')' }).data('alpha', parseInt(this._rgba.a.val()));
		$element.data('rgb', {
			r: this._rgba.r.val(),
			g: this._rgba.g.val(),
			b: this._rgba.b.val()
		});
		$('#' + $element.data('store')).val('rgba(' + this._rgba.r.val() + ', ' + this._rgba.g.val() + ', ' + this._rgba.b.val() + ', ' + (this._rgba.a.val() / 100) + ')').trigger('change');
		
		this._dialog.wcfDialog('close');
	},
	
	/**
	 * Creates an input element.
	 * 
	 * @param	string		type
	 * @param	string		label
	 * @param	integer		min
	 * @param	integer		max
	 * @return	jQuery
	 */
	_createInputElement: function(type, label, min, max) {
		// create elements
		var $listItem = $('<li class="' + type + '" />');
		var $label = $('<label />').appendTo($listItem);
		$('<span>' + label + '</span>').appendTo($label);
		this._rgba[type] = $('<input type="number" value="0" min="' + min + '" max="' + max + '" step="1" />').appendTo($label);
		
		return $listItem;
	},
	
	/**
	 * Handles the mouse down event on the gradient.
	 * 
	 * @param	object		event
	 */
	_mouseDownGradient: function(event) {
		this._gradientActive = true;
		this._mouseGradient(event);
	},
	
	/**
	 * Handles updates of gradient selector position.
	 * 
	 * @param	object		event
	 */
	_mouseGradient: function(event) {
		var $position = this._gradient.getOffsets('offset');
		
		var $left = Math.max(Math.min(event.pageX - $position.left, 255), 0);
		var $top = Math.max(Math.min(event.pageY - $position.top, 255), 0);
		
		// calculate saturation and value
		this._hsv.s = Math.max(0, Math.min(1, $left / 255)) * 100;
		this._hsv.v = Math.max(0, Math.min(1, (255 - $top) / 255)) * 100;
		
		// update color
		this._updateValues(null);
	},
	
	/**
	 * Handles the mouse down event on the bar.
	 * 
	 * @param	object		event
	 */
	_mouseDownBar: function(event) {
		this._barActive = true;
		this._mouseBar(event);
	},
	
	/**
	 * Handles updates of the bar selector position.
	 * 
	 * @param	object		event
	 */
	_mouseBar: function(event) {
		var $position = this._bar.getOffsets('offset');
		var $top = Math.max(Math.min(event.pageY - $position.top, 255), 0);
		this._barSelector.css({ top: $top + 'px' });
		
		// calculate hue
		this._hsv.h = Math.max(0, Math.min(359, Math.round((255 - $top) / 255 * 360)));
		
		// update color
		this._updateValues(null);
	},
	
	/**
	 * Handles changes of RGBa input fields.
	 */
	_blurRgba: function() {
		for (var $type in this._rgba) {
			var $value = parseInt(this._rgba[$type].val()) || 0;
			
			// alpha
			if ($type === 'a') {
				this._rgba[$type].val(Math.max(0, Math.min(100, $value)));
			}
			else {
				// rgb
				this._rgba[$type].val(Math.max(0, Math.min(255, $value)));
			}
		}
		
		this._updateValues({
			r: this._rgba.r.val(),
			g: this._rgba.g.val(),
			b: this._rgba.b.val()
		}, true, true);
	},
	
	/**
	 * Handles change of HEX value.
	 */
	_blurHex: function() {
		var $value = this.hexToRgb(this._hex.val());
		if ($value !== Number.NaN) {
			this._updateValues($value, true, true);
		}
	},
	
	/**
	 * Updates the values of all elements, including color picker and
	 * input elements. Argument 'rgb' may be null.
	 * 
	 * @param	object		rgb
	 * @param	boolean		changeH
	 * @param	boolean		changeSV
	 */
	_updateValues: function(rgb, changeH, changeSV) {
		changeH = (changeH === true) ? true : false;
		changeSV = (changeSV === true) ? true : false;
		
		// calculate RGB values from HSV
		if (rgb === null) {
			rgb = this.hsvToRgb(this._hsv.h, this._hsv.s, this._hsv.v);
		}
		
		// add alpha channel
		if (rgb.a === undefined) {
			rgb.a = this._rgba.a.val();
		}
		
		// adjust RGBa input
		for (var $type in rgb) {
			this._rgba[$type].val(rgb[$type]);
		}
		
		// set hex input
		this._hex.val(this.rgbToHex(rgb.r, rgb.g, rgb.b));
		
		// calculate HSV to adjust selectors
		if (changeH || changeSV) {
			var $hsv = this.rgbToHsv(rgb.r, rgb.g, rgb.b);
			
			// adjust hue
			if (changeH) {
				this._hsv.h = $hsv.h;
			}
			
			// adjust saturation and value
			if (changeSV) {
				this._hsv.s = $hsv.s;
				this._hsv.v = $hsv.v;
			}
		}
		
		// adjust bar selector
		var $top = Math.max(0, Math.min(255, 255 - (this._hsv.h / 360) * 255));
		this._barSelector.css({ top: $top + 'px' });
		
		// adjust gradient selector
		var $left = Math.max(0, Math.min(255, (this._hsv.s / 100) * 255));
		var $top = Math.max(0, Math.min(255, 255 - ((this._hsv.v / 100) * 255)));
		this._gradientSelector.css({
			left: ($left - 6) + 'px',
			top: ($top - 6) + 'px'
		});
				
		// update 'new' color
		this._newColor.css({ backgroundColor: 'rgb(' + rgb.r + ', ' + rgb.g + ', ' + rgb.b + ')' });
		
		// adjust gradient color
		var $rgb = this.hsvToRgb(this._hsv.h, 100, 100);
		this._gradient.css({ backgroundColor: 'rgb(' + $rgb.r + ', ' + $rgb.g + ', ' + $rgb.b + ')' });
	},
	
	/**
	 * Converts a HSV color into RGB.
	 * 
	 * @see	https://secure.wikimedia.org/wikipedia/de/wiki/HSV-Farbraum#Transformation_von_RGB_und_HSV
	 * 
	 * @param	integer		h
	 * @param	integer		s
	 * @param	integer		v
	 * @return	object
	 */
	hsvToRgb: function(h, s, v) {
		var $rgb = { r: 0, g: 0, b: 0 };
		var $h, $f, $p, $q, $t;
		
		$h = Math.floor(h / 60);
		$f = h / 60 - $h;
		
		s /= 100;
		v /= 100;
		
		$p = v * (1 - s);
		$q = v * (1 - s * $f);
		$t = v * (1 - s * (1 - $f));
		
		if (s == 0) {
			$rgb.r = $rgb.g = $rgb.b = v;
		}
		else {
			switch ($h) {
				case 1:
					$rgb.r = $q;
					$rgb.g = v;
					$rgb.b = $p;
				break;
				
				case 2:
					$rgb.r = $p;
					$rgb.g = v;
					$rgb.b = $t;
				break;
				
				case 3:
					$rgb.r = $p;
					$rgb.g = $q;
					$rgb.b = v;
				break;
				
				case 4:
					$rgb.r = $t;
					$rgb.g = $p;
					$rgb.b = v;
				break;
				
				case 5:
					$rgb.r = v;
					$rgb.g = $p;
					$rgb.b = $q;
				break;
				
				case 0:
				case 6:
					$rgb.r = v;
					$rgb.g = $t;
					$rgb.b = $p;
				break;
			}
		}
		
		return {
			r: Math.round($rgb.r * 255),
			g: Math.round($rgb.g * 255),
			b: Math.round($rgb.b * 255)
		};
	},
	
	/**
	 * Converts a RGB color into HSV.
	 * 
	 * @see	https://secure.wikimedia.org/wikipedia/de/wiki/HSV-Farbraum#Transformation_von_RGB_und_HSV
	 * 
	 * @param	integer		r
	 * @param	integer		g
	 * @param	integer		b
	 * @return	object
	 */
	rgbToHsv: function(r, g, b) {
		var $h, $s, $v;
		var $max, $min, $diff;
		
		r /= 255;
		g /= 255;
		b /= 255;
		
		$max = Math.max(Math.max(r, g), b);
		$min = Math.min(Math.min(r, g), b);
		$diff = $max - $min;
		
		$h = 0;
		if ($max !== $min) {
			switch ($max) {
				case r:
					$h = 60 * (0 + (g - b) / $diff);
				break;
				
				case g:
					$h = 60 * (2 + (b - r) / $diff);
				break;
				
				case b:
					$h = 60 * (4 + (r - g) / $diff);
				break;
			}
			
			if ($h < 0) {
				$h += 360;
			}
		}
		
		if ($max === 0) {
			$s = 0;
		}
		else {
			$s = $diff / $max;
		}
		
		$v = $max;
		
		return {
			h: Math.round($h),
			s: Math.round($s * 100),
			v: Math.round($v * 100)
		};
	},
	
	/**
	 * Converts HEX into RGB.
	 * 
	 * @param	string		hex
	 * @return	object
	 */
	hexToRgb: function(hex) {
		if (/^#?([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(hex)) {
			// only convert #abc and #abcdef
			hex = hex.split('');
			
			// drop the hashtag
			if (hex[0] === '#') {
				hex.shift();
			}
			
			// parse shorthand #xyz
			if (hex.length === 3) {
				return {
					r: parseInt(hex[0] + '' + hex[0], 16),
					g: parseInt(hex[1] + '' + hex[1], 16),
					b: parseInt(hex[2] + '' + hex[2], 16)
				};
			}
			else {
				return {
					r: parseInt(hex[0] + '' + hex[1], 16),
					g: parseInt(hex[2] + '' + hex[3], 16),
					b: parseInt(hex[4] + '' + hex[5], 16)
				};
			}
		}
		
		return Number.NaN;
	},
	
	/**
	 * Converts a RGB into HEX.
	 * 
	 * @see	http://www.linuxtopia.org/online_books/javascript_guides/javascript_faq/rgbtohex.htm
	 * 
	 * @param	integer		r
	 * @param	integer		g
	 * @param	integer		b
	 * @return	string
	 */
	rgbToHex: function(r, g, b) {
		return ("0123456789ABCDEF".charAt((r - r % 16) / 16) + '' + "0123456789ABCDEF".charAt(r % 16)) + '' + ("0123456789ABCDEF".charAt((g - g % 16) / 16) + '' + "0123456789ABCDEF".charAt(g % 16)) + '' + ("0123456789ABCDEF".charAt((b - b % 16) / 16) + '' + "0123456789ABCDEF".charAt(b % 16));
	}
});