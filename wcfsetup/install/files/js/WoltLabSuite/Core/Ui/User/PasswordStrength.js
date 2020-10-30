/**
 * Adds a password strength meter to a password input and exposes
 * zxcbn's verdict as sibling input.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/User/PasswordStrength
 */
define(["require", "exports", "tslib", "../../Language", "../../Dom/Util"], function (require, exports, tslib_1, Language, Util_1) {
    "use strict";
    var _a;
    Language = tslib_1.__importStar(Language);
    Util_1 = tslib_1.__importDefault(Util_1);
    const STATIC_DICTIONARY = [];
    const siteName = (_a = document.querySelector('meta[property="og:site_name"]')) === null || _a === void 0 ? void 0 : _a.getAttribute("content");
    if (siteName) {
        STATIC_DICTIONARY.push(siteName);
    }
    function flatMap(array, callback) {
        return array.map(callback).reduce((carry, item) => {
            return carry.concat(item);
        }, []);
    }
    function splitIntoWords(value) {
        return [].concat(value, value.split(/\W+/));
    }
    function initializeFeedbacker(Feedback) {
        const localizedPhrases = {};
        Object.entries(Feedback.default_phrases).forEach(([type, phrases]) => {
            localizedPhrases[type] = {};
            Object.entries(phrases).forEach(([identifier, phrase]) => {
                const languageItem = `wcf.user.password.zxcvbn.${type}.${identifier}`;
                const localizedValue = Language.get(languageItem);
                localizedPhrases[type][identifier] = localizedValue !== languageItem ? localizedValue : phrase;
            });
        });
        return new Feedback(localizedPhrases);
    }
    class PasswordStrength {
        constructor(input, options) {
            this.input = input;
            this.wrapper = document.createElement("div");
            this.score = document.createElement("span");
            this.verdictResult = document.createElement("input");
            void new Promise((resolve_1, reject_1) => { require(["zxcvbn"], resolve_1, reject_1); }).then(tslib_1.__importStar).then(({ default: zxcvbn }) => {
                this.zxcvbn = zxcvbn;
                if (options.relatedInputs) {
                    this.relatedInputs = options.relatedInputs;
                }
                if (options.staticDictionary) {
                    this.staticDictionary = options.staticDictionary;
                }
                this.feedbacker = initializeFeedbacker(zxcvbn.Feedback);
                this.wrapper.className = "inputAddon inputAddonPasswordStrength";
                this.input.parentNode.insertBefore(this.wrapper, this.input);
                this.wrapper.appendChild(this.input);
                const rating = document.createElement("div");
                rating.className = "passwordStrengthRating";
                const ratingLabel = document.createElement("small");
                ratingLabel.textContent = Language.get("wcf.user.password.strength");
                rating.appendChild(ratingLabel);
                this.score.className = "passwordStrengthScore";
                this.score.dataset.score = "-1";
                rating.appendChild(this.score);
                this.wrapper.appendChild(rating);
                this.verdictResult.type = "hidden";
                this.verdictResult.name = `${this.input.name}_passwordStrengthVerdict`;
                this.wrapper.parentNode.insertBefore(this.verdictResult, this.wrapper);
                this.input.addEventListener("input", (ev) => this.evaluate(ev));
                this.relatedInputs.forEach((input) => input.addEventListener("input", (ev) => this.evaluate(ev)));
                if (this.input.value.trim() !== "") {
                    this.evaluate();
                }
            });
        }
        evaluate(event) {
            const dictionary = flatMap(STATIC_DICTIONARY.concat(this.staticDictionary, this.relatedInputs.map((input) => input.value.trim())), splitIntoWords).filter((value) => value.length > 0);
            const value = this.input.value.trim();
            // To bound runtime latency for really long passwords, consider sending zxcvbn() only
            // the first 100 characters or so of user input.
            const verdict = this.zxcvbn(value.substr(0, 100), dictionary);
            verdict.feedback = this.feedbacker.from_result(verdict);
            this.score.dataset.score = value.length === 0 ? "-1" : verdict.score.toString();
            if (event !== undefined) {
                // Do not overwrite the value on page load.
                Util_1.default.innerError(this.wrapper, verdict.feedback.warning);
            }
            this.verdictResult.value = JSON.stringify(verdict);
        }
    }
    return PasswordStrength;
});
