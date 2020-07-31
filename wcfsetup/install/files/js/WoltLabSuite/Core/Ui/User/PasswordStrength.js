/**
 * Adds a password strength meter to a password input and exposes
 * zxcbn's verdict as sibling input.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/User/PasswordStrength
 */
define(['zxcvbn', 'Core', 'Language'], function(zxcvbn, Core, Language) {
	"use strict";
	
	var STATIC_DICTIONARY = [];
	if (elBySel('meta[property="og:site_name"]')) {
		STATIC_DICTIONARY.push(elBySel('meta[property="og:site_name"]').getAttribute('content'));
	}
	
	function createMeter(options) {
		var meter = elCreate('meter');
		Object.keys(options).forEach(function (key) {
			meter[key] = options[key];
		});
		return meter;
	}
	
	function flatMap(array, callback) {
		return array.map(callback).reduce(function (carry, item) {
			return carry.concat(item);
		}, [])
	}
	
	function splitIntoWords(value) {
		return [].concat(
			value,
			value.split(/\W+/)
		);
	}
	
	/**
	 * @constructor
	 */
	function PasswordStrength(input, options) { this.init(input, options); }
	PasswordStrength.prototype = {
		/**
		 * @param	{object}	options
		 */
		init: function(input, options) {
			this._input = input;
			
			this._options = Core.extend({
				relatedInputs: [],
				staticDictionary: [],
			}, options);
			
			if (!this._options.feedbacker) {
				var phrases = Core.extend({}, zxcvbn.Feedback.default_phrases);
				for (var type in phrases) {
					for (var phrase in phrases[type]) {
						var languageItem = 'wcf.user.password.zxcvbn.' + type + '.' + phrase;
						var value = Language.get(languageItem);
						if (value !== languageItem) {
							phrases[type][phrase] = value;
						}
					}
				}
				this._options.feedbacker = new zxcvbn.Feedback(phrases);
			}
			
			this._meter = createMeter({
				min: 0,
				max: 4,
				low: 2,
				high: 3,
				optimum: 4
			});
			this._input.parentNode.insertBefore(this._meter, this._input.nextSibling);
			this._verdictResult = elCreate('input');
			this._verdictResult.type = 'hidden';
			this._verdictResult.name = this._input.name + '_passwordStrengthVerdict';
			this._input.parentNode.insertBefore(this._verdictResult, this._input);
			
			this._input.addEventListener('input', this._evalute.bind(this));
			this._options.relatedInputs.forEach(function (input) {
				input.addEventListener('input', this._evalute.bind(this));
			}.bind(this));
			
			this._evalute();
		},
		
		_evalute: function() {
			var dictionary = flatMap(STATIC_DICTIONARY.concat(
				this._options.staticDictionary,
				this._options.relatedInputs.map(function (input) {
					return input.value
				})
			), splitIntoWords).filter(function (value) {
				return value.length > 0;
			});

			// To bound runtime latency for really long passwords, consider sending zxcvbn() only
			// the first 100 characters or so of user input.
			var verdict = zxcvbn(this._input.value.substr(0, 100), dictionary);
			verdict.feedback = this._options.feedbacker.from_result(verdict);
			
			this._meter.value = verdict.score;
			this._verdictResult.value = JSON.stringify(verdict);
		},
	};
	
	return PasswordStrength;
});
