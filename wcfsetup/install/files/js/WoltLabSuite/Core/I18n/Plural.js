/**
 * Generates plural phrases for the `plural` template plugin.
 * 
 * @author	Matthias Schmidt, Marcel Werk
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/I18n/Plural
 */
define(['StringUtil'], function(StringUtil) {
	"use strict";
	
	var PLURAL_FEW = 'few';
	var PLURAL_MANY = 'many';
	var PLURAL_ONE = 'one';
	var PLURAL_OTHER = 'other';
	var PLURAL_TWO = 'two';
	var PLURAL_ZERO = 'zero';
	
	return {
		/**
		 * Returns the plural category for the given value.
		 *
		 * @param	{number}	value
		 * @param	{?string}	languageCode
		 * @return	string
		 */
		getCategory: function(value, languageCode) {
			if (!languageCode) {
				languageCode = LANGUAGE_CODE;
			}
			
			// Fallback: handle unknown languages as English
			if (typeof this[languageCode] !== 'function') {
				languageCode = 'en';
			}
			
			var category = this[languageCode](value);
			if (category) {
				return category;
			}
			
			return PLURAL_OTHER;
		},
		
		/**
		 * Returns the value for a `plural` element used in the template.
		 * 
		 * @param	{object}	parameters
		 * @see		wcf\system\template\plugin\PluralFunctionTemplatePlugin::execute()
		 */
		getCategoryFromTemplateParameters: function(parameters) {
			if (!parameters['value'] ) {
				throw new Error('Missing parameter value');
			}
			if (!parameters['other']) {
				throw new Error('Missing parameter other');
			}
			
			var value = parameters['value'];
			if (Array.isArray(value)) {
				value = value.length;
			}
			
			// handle numeric attributes
			for (var key in parameters) {
				if (objOwns(parameters, key) && key == ~~key && key == value) {
					return parameters[key];
				}
			}
			
			var category = this.getCategory(value);
			if (!parameters[category]) {
				category = PLURAL_OTHER;
			}
			
			var string = parameters[category];
			if (string.indexOf('#') !== -1) {
				return string.replace('#', StringUtil.formatNumeric(value));
			}
			
			return string;
		},
		
		/**
		 * `f` is the fractional number as a whole number (1.234 yields 234)
		 * 
		 * @param	{number}	n
		 * @return	{integer}
		 */
		getF: function(n) {
			n = n.toString();
			var pos = n.indexOf('.');
			if (pos === -1) {
				return 0;
			}
			
			return parseInt(n.substr(pos + 1), 10);
		},
		
		/**
		 * `v` represents the number of digits of the fractional part (1.234 yields 3)
		 * 
		 * @param	{number}	n
		 * @return	{integer}
		 */
		getV: function(n) {
			return n.toString().replace(/^[^.]*\.?/, '').length;
		},
		
		// Afrikaans
		af: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Amharic
		am: function(n) {
			var i = Math.floor(Math.abs(n));
			if (n == 1 || i === 0) return PLURAL_ONE;
		},
		
		// Arabic
		ar: function(n) {
			if (n == 0) return PLURAL_ZERO;
			if (n == 1) return PLURAL_ONE;
			if (n == 2) return PLURAL_TWO;
			
			var mod100 = n % 100;
			if (mod100 >= 3 && mod100 <= 10) return PLURAL_FEW;
			if (mod100 >= 11 && mod100 <= 99) return PLURAL_MANY;
		},
		
		// Assamese
		as: function(n) {
			var i = Math.floor(Math.abs(n));
			if (n == 1 || i === 0) return PLURAL_ONE;
		},
		
		// Azerbaijani
		az: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Belarusian
		be: function(n) {
			var mod10 = n % 10;
			var mod100 = n % 100;
			
			if (mod10 == 1 && mod100 != 11) return PLURAL_ONE;
			if (mod10 >= 2 && mod10 <= 4 && !(mod100 >= 12 && mod100 <= 14)) return PLURAL_FEW;
			if (mod10 == 0 || (mod10 >= 5 && mod10 <= 9) || (mod100 >= 11 && mod100 <= 14)) return PLURAL_MANY;
		},
		
		// Bulgarian
		bg: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Bengali
		bn: function(n) {
			var i = Math.floor(Math.abs(n));
			if (n == 1 || i === 0) return PLURAL_ONE;
		},
		
		// Tibetan
		bo: function(n) {},
		
		// Bosnian
		bs: function(n) {
			var v = this.getV(n);
			var f = this.getF(n);
			var mod10 = n % 10;
			var mod100 = n % 100;
			var fMod10 = f % 10;
			var fMod100 = f % 100;
			
			if ((v == 0 && mod10 == 1 && mod100 != 11) || (fMod10 == 1 && fMod100 != 11)) return PLURAL_ONE;
			if ((v == 0 && mod10 >= 2 && mod10 <= 4 && mod100 >= 12 && mod100 <= 14)
				|| (fMod10 >= 2 && fMod10 <= 4 && fMod100 >= 12 && fMod100 <= 14)) return PLURAL_FEW;
		},
		
		// Czech
		cs: function(n) {
			var v = this.getV(n);
			
			if (n == 1 && v === 0) return PLURAL_ONE;
			if (n >= 2 && n <= 4 && v === 0) return PLURAL_FEW;
			if (v === 0) return PLURAL_MANY;
		},
		
		// Welsh
		cy: function(n) {
			if (n == 0) return PLURAL_ZERO;
			if (n == 1) return PLURAL_ONE;
			if (n == 2) return PLURAL_TWO;
			if (n == 3) return PLURAL_FEW;
			if (n == 6) return PLURAL_MANY;
		},
		
		// Danish
		da: function(n) {
			if (n > 0 && n < 2) return PLURAL_ONE;
		},
		
		// Greek
		el: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Catalan (ca)
		// German (de)
		// English (en)
		// Estonian (et)
		// Finnish (fi)
		// Italian (it)
		// Dutch (nl)
		// Swedish (sv)
		// Swahili (sw)
		// Urdu (ur)
		en: function(n) {
			if (n == 1 && this.getV(n) === 0) return PLURAL_ONE;
		},
		
		// Spanish
		es: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Basque
		eu: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Persian
		fa: function(n) {
			if (n >= 0 && n <= 1) return PLURAL_ONE;
		},
		
		// French
		fr: function(n) {
			if (n >= 0 && n < 2) return PLURAL_ONE;
		},
		
		// Irish
		ga: function(n) {
			if (n == 1) return PLURAL_ONE;
			if (n == 2) return PLURAL_TWO;
			if (n == 3 || n == 4 || n == 5 || n == 6) return PLURAL_FEW;
			if (n == 7 || n == 8 || n == 9 || n == 10) return PLURAL_MANY;
		},
		
		// Gujarati
		gu: function(n) {
			if (n >= 0 && n <= 1) return PLURAL_ONE;
		},
		
		// Hebrew
		he: function(n) {
			var v = this.getV(n);
	
			if (n == 1 && v === 0) return PLURAL_ONE;
			if (n == 2 && v === 0) return PLURAL_TWO;
			if (n > 10 && v === 0 && n % 10 == 0) return PLURAL_MANY;
		},
		
		// Hindi
		hi: function(n) {
			if (n >= 0 && n <= 1) return PLURAL_ONE;
		},
		
		// Croatian
		hr: function(n) {
			// same as Bosnian
			return this.bs(n);
		},
		
		// Hungarian
		hu: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Armenian
		hy: function(n) {
			if (n >= 0 && n < 2) return PLURAL_ONE;
		},
		
		// Indonesian
		id: function(n) {},
		
		// Icelandic
		is: function(n) {
			var f = this.getF(n);
			
			if (f === 0 && n % 10 === 1 && !(n % 100 === 11) || !(f === 0)) return PLURAL_ONE;
		},
		
		// Japanese
		ja: function(n) {},
		
		// Javanese
		jv: function(n) {},
		
		// Georgian
		ka: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Kazakh
		kk: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Khmer
		km: function(n) {},
		
		// Kannada
		kn: function(n) {
			if (n >= 0 && n <= 1) return PLURAL_ONE;
		},
		
		// Korean
		ko: function(n) {},
		
		// Kurdish
		ku: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Kyrgyz
		ky: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Luxembourgish
		lb: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Lao
		lo: function(n) {},
		
		// Lithuanian
		lt: function(n) {
			var mod10 = n % 10;
			var mod100 = n % 100;
			
			if (mod10 == 1 && !(mod100 >= 11 && mod100 <= 19)) return PLURAL_ONE;
			if (mod10 >= 2 && mod10 <= 9 && !(mod100 >= 11 && mod100 <= 19)) return PLURAL_FEW;
			if (this.getF(n) != 0) return PLURAL_MANY;
		},
		
		// Latvian
		lv: function(n) {
			var mod10 = n % 10;
			var mod100 = n % 100;
			var v = this.getV(n);
			var f = this.getF(n);
			var fMod10 = f % 10;
			var fMod100 = f % 100;
			
			if (mod10 == 0 || (mod100 >= 11 && mod100 <= 19) || (v == 2 && fMod100 >= 11 && fMod100 <= 19)) return PLURAL_ZERO;
			if ((mod10 == 1 && mod100 != 11) || (v == 2 && fMod10 == 1 && fMod100 != 11) || (v != 2 && fMod10 == 1)) return PLURAL_ONE;
		},
		
		// Macedonian
		mk: function(n) {
			var v = this.getV(n);
			var f = this.getF(n);
			var mod10 = n % 10;
			var mod100 = n % 100;
			var fMod10 = f % 10;
			var fMod100 = f % 100;
			
			if ((v == 0 && mod10 == 1 && mod100 != 11) || (fMod10 == 1 && fMod100 != 11)) return PLURAL_ONE;
		},
		
		// Malayalam
		ml: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Mongolian 
		mn: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Marathi 
		mr: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Malay 
		ms: function(n) {},
		
		// Maltese 
		mt: function(n) {
			var mod100 = n % 100;
			
			if (n == 1) return PLURAL_ONE;
			if (n == 0 || (mod100 >= 2 && mod100 <= 10)) return PLURAL_FEW;
			if (mod100 >= 11 && mod100 <= 19) return PLURAL_MANY;
		},
		
		// Burmese
		my: function(n) {},
		
		// Norwegian
		no: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Nepali
		ne: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Odia
		or: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Punjabi
		pa: function(n) {
			if (n == 1 || n == 0) return PLURAL_ONE;
		},
		
		// Polish
		pl: function(n) {
			var v = this.getV(n);
			var mod10 = n % 10;
			var mod100 = n % 100;
	
			if (n == 1 && v == 0) return PLURAL_ONE;
			if (v == 0 && mod10 >= 2 && mod10 <= 4 && !(mod100 >= 12 && mod100 <= 14)) return PLURAL_FEW;
			if (v == 0 && ((n != 1 && mod10 >= 0 && mod10 <= 1) || (mod10 >= 5 && mod10 <= 9) || (mod100 >= 12 && mod100 <= 14))) return PLURAL_MANY;
		},
		
		// Pashto
		ps: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Portuguese
		pt: function(n) {
			if (n >= 0 && n < 2) return PLURAL_ONE;
		},
		
		// Romanian
		ro: function(n) {
			var v = this.getV(n);
			var mod100 = n % 100;
			
			if (n == 1 && v === 0) return PLURAL_ONE;
			if (v != 0 || n == 0 || (mod100 >= 2 && mod100 <= 19)) return PLURAL_FEW;
		},
		
		// Russian
		ru: function(n) {
			var mod10 = n % 10;
			var mod100 = n % 100;
			
			if (this.getV(n) == 0) {
				if (mod10 == 1 && mod100 != 11) return PLURAL_ONE;
				if (mod10 >= 2 && mod10 <= 4 && !(mod100 >= 12 && mod100 <= 14)) return PLURAL_FEW;
				if (mod10 == 0 || (mod10 >= 5 && mod10 <= 9) || (mod100 >= 11 && mod100 <= 14)) return PLURAL_MANY;
			}
		},
		
		// Sindhi
		sd: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Sinhala
		si: function(n) {
			if (n == 0 || n == 1 || (Math.floor(n) == 0 && this.getF(n) == 1)) return PLURAL_ONE;
		},
		
		// Slovak
		sk: function(n) {
			// same as Czech
			return this.cs(n);
		},
		
		// Slovenian
		sl: function(n) {
			var v = this.getV(n);
			var mod100 = n % 100;
			
			if (v == 0 && mod100 == 1) return PLURAL_ONE;
			if (v == 0 && mod100 == 2) return PLURAL_TWO;
			if ((v == 0 && (mod100 == 3 || mod100 == 4)) || v != 0) return PLURAL_FEW;
		},
		
		// Albanian
		sq: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Serbian
		sr: function(n) {
			// same as Bosnian
			return this.bs(n);
		},
		
		// Tamil
		ta: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Telugu
		te: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Tajik
		tg: function(n) {},
		
		// Thai
		th: function(n) {},
		
		// Turkmen
		tk: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Turkish
		tr: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Uyghur
		ug: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Ukrainian
		uk: function(n) {
			// same as Russian
			return this.ru(n);
		},
		
		// Uzbek
		uz: function(n) {
			if (n == 1) return PLURAL_ONE;
		},
		
		// Vietnamese
		vi: function(n) {},
		
		// Chinese
		zh: function(n) {}
	};
});