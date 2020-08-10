/**
 * Adds a password strength meter to a password input and exposes
 * zxcbn's verdict as sibling input.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/User/PasswordStrength
 */
define(['zxcvbn', 'Core', 'Language', 'StringUtil'], function (zxcvbn, Core, Language, StringUtil) {
	'use strict';
	
	var STATIC_DICTIONARY = [];
	if (elBySel('meta[property="og:site_name"]')) {
		STATIC_DICTIONARY.push(elBySel('meta[property="og:site_name"]').getAttribute('content'));
	}
	
	function flatMap(array, callback) {
		return array.map(callback).reduce(function (carry, item) {
			return carry.concat(item);
		}, []);
	}
	
	function splitIntoWords(value) {
		return [].concat(value, value.split(/\W+/));
	}
	
	/**
	 * @constructor
	 */
	function PasswordStrength(input, options) { this.init(input, options); }
	
	PasswordStrength.prototype = {
		/**
		 * @param       {Element}       input
		 * @param	{object}	options
		 */
		init: function (input, options) {
			this._input = input;
			
			this._options = Core.extend({
				relatedInputs: [],
				staticDictionary: []
			}, options);
			
			if (!this._options.feedbacker) {
				var phrases = Core.extend({}, zxcvbn.Feedback.default_phrases);
				for (var type in phrases) {
					if (phrases.hasOwnProperty(type)) {
						for (var phrase in phrases[type]) {
							if (phrases[type].hasOwnProperty(phrase)) {
								var languageItem = 'wcf.user.password.zxcvbn.' + type + '.' + phrase;
								var value = Language.get(languageItem);
								if (value !== languageItem) {
									phrases[type][phrase] = value;
								}
							}
						}
					}
				}
				this._options.feedbacker = new zxcvbn.Feedback(phrases);
			}
			
			this._wrapper = elCreate('div');
			this._wrapper.className = 'inputAddon inputAddonPasswordStrength';
			this._input.parentNode.insertBefore(this._wrapper, this._input);
			this._wrapper.appendChild(this._input);
			
			var passwordStrengthWrapper = elCreate('div');
			passwordStrengthWrapper.className = 'passwordStrengthWrapper';
			
			var rating = elCreate('div');
			rating.className = 'passwordStrengthRating';
			
			var ratingLabel = elCreate('small');
			ratingLabel.textContent = Language.get('wcf.user.password.strength');
			rating.appendChild(ratingLabel);
			
			this._score = elCreate('span');
			this._score.className = 'passwordStrengthScore';
			elData(this._score, 'score', '-1');
			rating.appendChild(this._score);
			
			this._wrapper.appendChild(rating);
			
			this._feedback = elCreate('div');
			this._feedback.className = 'passwordStrengthFeedback';
			this._wrapper.appendChild(this._feedback);
			
			this._verdictResult = elCreate('input');
			this._verdictResult.type = 'hidden';
			this._verdictResult.name = this._input.name + '_passwordStrengthVerdict';
			this._wrapper.parentNode.insertBefore(this._verdictResult, this._wrapper);
			
			var callback = this._evaluate.bind(this);
			this._input.addEventListener('input', callback);
			this._options.relatedInputs.forEach(function (input) {
				input.addEventListener('input', callback);
			});
			
			if (this._input.value.trim() !== '') {
				this._evaluate();
			}
		},
		
		/**
		 * @param {Event=} event
		 */
		_evaluate: function (event) {
			var dictionary = flatMap(STATIC_DICTIONARY.concat(this._options.staticDictionary,
				this._options.relatedInputs.map(function (input) {
					return input.value.trim();
				})
			), splitIntoWords).filter(function (value) {
				return value.length > 0;
			});
			
			var value = this._input.value.trim();
			
			// To bound runtime latency for really long passwords, consider sending zxcvbn() only
			// the first 100 characters or so of user input.
			var verdict = zxcvbn(value.substr(0, 100), dictionary);
			verdict.feedback = this._options.feedbacker.from_result(verdict);
			
			elData(this._score, 'score', value.length === 0 ? '-1' : verdict.score);
			
			if (event !== undefined) {
				// Do not overwrite the value on page load.
				elInnerError(this._wrapper, verdict.feedback.warning);
			}
			
			this._verdictResult.value = JSON.stringify(verdict);
		}
	};
	
	return PasswordStrength;
});
