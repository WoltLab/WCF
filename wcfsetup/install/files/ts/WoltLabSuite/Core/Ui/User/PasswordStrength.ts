/**
 * Adds a password strength meter to a password input and exposes
 * zxcbn's verdict as sibling input.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/User/PasswordStrength
 */

import * as Language from "../../Language";
import DomUtil from "../../Dom/Util";

// zxcvbn is imported for the types only. It is loaded on demand, due to its size.
import zxcvbn from "zxcvbn";

type StaticDictionary = string[];

const STATIC_DICTIONARY: StaticDictionary = [];

const siteName = document.querySelector('meta[property="og:site_name"]')?.getAttribute("content");
if (siteName) {
  STATIC_DICTIONARY.push(siteName);
}

function flatMap<T, U>(array: T[], callback: (x: T) => U[]): U[] {
  return array.map(callback).reduce((carry, item) => {
    return carry.concat(item);
  }, [] as U[]);
}

function splitIntoWords(value: string): string[] {
  return ([] as string[]).concat(value, value.split(/\W+/));
}

function initializeFeedbacker(Feedback: typeof zxcvbn.Feedback): zxcvbn.Feedback {
  const localizedPhrases: typeof Feedback.default_phrases = {} as typeof Feedback.default_phrases;

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
  private zxcvbn: typeof zxcvbn;
  private relatedInputs: HTMLInputElement[];
  private staticDictionary: StaticDictionary;
  private feedbacker: zxcvbn.Feedback;

  private readonly wrapper = document.createElement("div");
  private readonly score = document.createElement("span");
  private readonly verdictResult = document.createElement("input");

  constructor(private readonly input: HTMLInputElement, options: Partial<Options>) {
    void import("zxcvbn").then(({ default: zxcvbn }) => {
      this.zxcvbn = zxcvbn;

      if (options.relatedInputs) {
        this.relatedInputs = options.relatedInputs;
      }
      if (options.staticDictionary) {
        this.staticDictionary = options.staticDictionary;
      }

      this.feedbacker = initializeFeedbacker(zxcvbn.Feedback);

      this.wrapper.className = "inputAddon inputAddonPasswordStrength";
      this.input.parentNode!.insertBefore(this.wrapper, this.input);
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
      this.wrapper.parentNode!.insertBefore(this.verdictResult, this.wrapper);

      this.input.addEventListener("input", (ev) => this.evaluate(ev));
      this.relatedInputs.forEach((input) => input.addEventListener("input", (ev) => this.evaluate(ev)));
      if (this.input.value.trim() !== "") {
        this.evaluate();
      }
    });
  }

  private evaluate(event?: Event) {
    const dictionary = flatMap(
      STATIC_DICTIONARY.concat(
        this.staticDictionary,
        this.relatedInputs.map((input) => input.value.trim())
      ),
      splitIntoWords
    ).filter((value) => value.length > 0);

    const value = this.input.value.trim();

    // To bound runtime latency for really long passwords, consider sending zxcvbn() only
    // the first 100 characters or so of user input.
    const verdict = this.zxcvbn(value.substr(0, 100), dictionary);
    verdict.feedback = this.feedbacker.from_result(verdict);

    this.score.dataset.score = value.length === 0 ? "-1" : verdict.score.toString();

    if (event !== undefined) {
      // Do not overwrite the value on page load.
      DomUtil.innerError(this.wrapper, verdict.feedback.warning);
    }

    this.verdictResult.value = JSON.stringify(verdict);
  }
}

export = PasswordStrength;

interface Options {
  relatedInputs: PasswordStrength["relatedInputs"];
  staticDictionary: PasswordStrength["staticDictionary"];
  feedbacker: PasswordStrength["feedbacker"];
}
