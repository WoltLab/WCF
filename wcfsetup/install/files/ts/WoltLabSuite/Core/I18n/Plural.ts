/**
 * Generates plural phrases for the `plural` template plugin.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright  2001-2020 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/I18n/Plural
 */

import * as StringUtil from "../StringUtil";

const PLURAL_FEW = "few";
const PLURAL_MANY = "many";
const PLURAL_ONE = "one";
const PLURAL_OTHER = "other";
const PLURAL_TWO = "two";
const PLURAL_ZERO = "zero";

const Plural = {
  /**
   * Returns the plural category for the given value.
   */
  getCategory(value: number, languageCode?: string): string {
    if (!languageCode) {
      languageCode = document.documentElement.lang;
    }

    // Fallback: handle unknown languages as English
    if (typeof Plural[languageCode] !== "function") {
      languageCode = "en";
    }

    const category = Plural[languageCode](value);
    if (category) {
      return category;
    }

    return PLURAL_OTHER;
  },

  /**
   * Returns the value for a `plural` element used in the template.
   *
   * @see    wcf\system\template\plugin\PluralFunctionTemplatePlugin::execute()
   */
  getCategoryFromTemplateParameters(parameters: object): string {
    if (!parameters["value"]) {
      throw new Error("Missing parameter value");
    }
    if (!parameters["other"]) {
      throw new Error("Missing parameter other");
    }

    let value = parameters["value"];
    if (Array.isArray(value)) {
      value = value.length;
    }

    // handle numeric attributes
    for (const key in parameters) {
      if (parameters.hasOwnProperty(key) && key.toString() === (~~key).toString() && key == value) {
        return parameters[key];
      }
    }

    let category = Plural.getCategory(value);
    if (!parameters[category]) {
      category = PLURAL_OTHER;
    }

    const string = parameters[category];
    if (string.indexOf("#") !== -1) {
      return string.replace("#", StringUtil.formatNumeric(value));
    }

    return string;
  },

  /**
   * `f` is the fractional number as a whole number (1.234 yields 234)
   */
  getF(n: number): number {
    const tmp = n.toString();
    const pos = tmp.indexOf(".");
    if (pos === -1) {
      return 0;
    }

    return parseInt(tmp.substr(pos + 1), 10);
  },

  /**
   * `v` represents the number of digits of the fractional part (1.234 yields 3)
   */
  getV(n: number): number {
    return n.toString().replace(/^[^.]*\.?/, "").length;
  },

  // Afrikaans
  af(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Amharic
  am(n: number): string | undefined {
    const i = Math.floor(Math.abs(n));
    if (n == 1 || i === 0) return PLURAL_ONE;
  },

  // Arabic
  ar(n: number): string | undefined {
    if (n == 0) return PLURAL_ZERO;
    if (n == 1) return PLURAL_ONE;
    if (n == 2) return PLURAL_TWO;

    const mod100 = n % 100;
    if (mod100 >= 3 && mod100 <= 10) return PLURAL_FEW;
    if (mod100 >= 11 && mod100 <= 99) return PLURAL_MANY;
  },

  // Assamese
  as(n: number): string | undefined {
    const i = Math.floor(Math.abs(n));
    if (n == 1 || i === 0) return PLURAL_ONE;
  },

  // Azerbaijani
  az(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Belarusian
  be(n: number): string | undefined {
    const mod10 = n % 10;
    const mod100 = n % 100;

    if (mod10 == 1 && mod100 != 11) return PLURAL_ONE;
    if (mod10 >= 2 && mod10 <= 4 && !(mod100 >= 12 && mod100 <= 14)) return PLURAL_FEW;
    if (mod10 == 0 || (mod10 >= 5 && mod10 <= 9) || (mod100 >= 11 && mod100 <= 14)) return PLURAL_MANY;
  },

  // Bulgarian
  bg(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Bengali
  bn(n: number): string | undefined {
    const i = Math.floor(Math.abs(n));
    if (n == 1 || i === 0) return PLURAL_ONE;
  },

  // Tibetan
  bo(n: number) {},

  // Bosnian
  bs(n: number): string | undefined {
    const v = Plural.getV(n);
    const f = Plural.getF(n);
    const mod10 = n % 10;
    const mod100 = n % 100;
    const fMod10 = f % 10;
    const fMod100 = f % 100;

    if ((v == 0 && mod10 == 1 && mod100 != 11) || (fMod10 == 1 && fMod100 != 11)) return PLURAL_ONE;
    if (
      (v == 0 && mod10 >= 2 && mod10 <= 4 && mod100 >= 12 && mod100 <= 14) ||
      (fMod10 >= 2 && fMod10 <= 4 && fMod100 >= 12 && fMod100 <= 14)
    )
      return PLURAL_FEW;
  },

  // Czech
  cs(n: number): string | undefined {
    const v = Plural.getV(n);

    if (n == 1 && v === 0) return PLURAL_ONE;
    if (n >= 2 && n <= 4 && v === 0) return PLURAL_FEW;
    if (v === 0) return PLURAL_MANY;
  },

  // Welsh
  cy(n: number): string | undefined {
    if (n == 0) return PLURAL_ZERO;
    if (n == 1) return PLURAL_ONE;
    if (n == 2) return PLURAL_TWO;
    if (n == 3) return PLURAL_FEW;
    if (n == 6) return PLURAL_MANY;
  },

  // Danish
  da(n: number): string | undefined {
    if (n > 0 && n < 2) return PLURAL_ONE;
  },

  // Greek
  el(n: number): string | undefined {
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
  en(n: number): string | undefined {
    if (n == 1 && Plural.getV(n) === 0) return PLURAL_ONE;
  },

  // Spanish
  es(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Basque
  eu(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Persian
  fa(n: number): string | undefined {
    if (n >= 0 && n <= 1) return PLURAL_ONE;
  },

  // French
  fr(n: number): string | undefined {
    if (n >= 0 && n < 2) return PLURAL_ONE;
  },

  // Irish
  ga(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
    if (n == 2) return PLURAL_TWO;
    if (n == 3 || n == 4 || n == 5 || n == 6) return PLURAL_FEW;
    if (n == 7 || n == 8 || n == 9 || n == 10) return PLURAL_MANY;
  },

  // Gujarati
  gu(n: number): string | undefined {
    if (n >= 0 && n <= 1) return PLURAL_ONE;
  },

  // Hebrew
  he(n: number): string | undefined {
    const v = Plural.getV(n);

    if (n == 1 && v === 0) return PLURAL_ONE;
    if (n == 2 && v === 0) return PLURAL_TWO;
    if (n > 10 && v === 0 && n % 10 == 0) return PLURAL_MANY;
  },

  // Hindi
  hi(n: number): string | undefined {
    if (n >= 0 && n <= 1) return PLURAL_ONE;
  },

  // Croatian
  hr(n: number): string | undefined {
    // same as Bosnian
    return Plural.bs(n);
  },

  // Hungarian
  hu(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Armenian
  hy(n: number): string | undefined {
    if (n >= 0 && n < 2) return PLURAL_ONE;
  },

  // Indonesian
  id(n: number) {},

  // Icelandic
  is(n: number): string | undefined {
    const f = Plural.getF(n);

    if ((f === 0 && n % 10 === 1 && !(n % 100 === 11)) || !(f === 0)) return PLURAL_ONE;
  },

  // Japanese
  ja(n: number) {},

  // Javanese
  jv(n: number) {},

  // Georgian
  ka(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Kazakh
  kk(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Khmer
  km(n: number) {},

  // Kannada
  kn(n: number): string | undefined {
    if (n >= 0 && n <= 1) return PLURAL_ONE;
  },

  // Korean
  ko(n: number) {},

  // Kurdish
  ku(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Kyrgyz
  ky(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Luxembourgish
  lb(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Lao
  lo(n: number) {},

  // Lithuanian
  lt(n: number): string | undefined {
    const mod10 = n % 10;
    const mod100 = n % 100;

    if (mod10 == 1 && !(mod100 >= 11 && mod100 <= 19)) return PLURAL_ONE;
    if (mod10 >= 2 && mod10 <= 9 && !(mod100 >= 11 && mod100 <= 19)) return PLURAL_FEW;
    if (Plural.getF(n) != 0) return PLURAL_MANY;
  },

  // Latvian
  lv(n: number): string | undefined {
    const mod10 = n % 10;
    const mod100 = n % 100;
    const v = Plural.getV(n);
    const f = Plural.getF(n);
    const fMod10 = f % 10;
    const fMod100 = f % 100;

    if (mod10 == 0 || (mod100 >= 11 && mod100 <= 19) || (v == 2 && fMod100 >= 11 && fMod100 <= 19)) return PLURAL_ZERO;
    if ((mod10 == 1 && mod100 != 11) || (v == 2 && fMod10 == 1 && fMod100 != 11) || (v != 2 && fMod10 == 1))
      return PLURAL_ONE;
  },

  // Macedonian
  mk(n: number): string | undefined {
    return Plural.bs(n);
  },

  // Malayalam
  ml(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Mongolian
  mn(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Marathi
  mr(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Malay
  ms(n: number) {},

  // Maltese
  mt(n: number): string | undefined {
    const mod100 = n % 100;

    if (n == 1) return PLURAL_ONE;
    if (n == 0 || (mod100 >= 2 && mod100 <= 10)) return PLURAL_FEW;
    if (mod100 >= 11 && mod100 <= 19) return PLURAL_MANY;
  },

  // Burmese
  my(n: number) {},

  // Norwegian
  no(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Nepali
  ne(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Odia
  or(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Punjabi
  pa(n: number): string | undefined {
    if (n == 1 || n == 0) return PLURAL_ONE;
  },

  // Polish
  pl(n: number): string | undefined {
    const v = Plural.getV(n);
    const mod10 = n % 10;
    const mod100 = n % 100;

    if (n == 1 && v == 0) return PLURAL_ONE;
    if (v == 0 && mod10 >= 2 && mod10 <= 4 && !(mod100 >= 12 && mod100 <= 14)) return PLURAL_FEW;
    if (
      v == 0 &&
      ((n != 1 && mod10 >= 0 && mod10 <= 1) || (mod10 >= 5 && mod10 <= 9) || (mod100 >= 12 && mod100 <= 14))
    )
      return PLURAL_MANY;
  },

  // Pashto
  ps(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Portuguese
  pt(n: number): string | undefined {
    if (n >= 0 && n < 2) return PLURAL_ONE;
  },

  // Romanian
  ro(n: number): string | undefined {
    const v = Plural.getV(n);
    const mod100 = n % 100;

    if (n == 1 && v === 0) return PLURAL_ONE;
    if (v != 0 || n == 0 || (mod100 >= 2 && mod100 <= 19)) return PLURAL_FEW;
  },

  // Russian
  ru(n: number): string | undefined {
    const mod10 = n % 10;
    const mod100 = n % 100;

    if (Plural.getV(n) == 0) {
      if (mod10 == 1 && mod100 != 11) return PLURAL_ONE;
      if (mod10 >= 2 && mod10 <= 4 && !(mod100 >= 12 && mod100 <= 14)) return PLURAL_FEW;
      if (mod10 == 0 || (mod10 >= 5 && mod10 <= 9) || (mod100 >= 11 && mod100 <= 14)) return PLURAL_MANY;
    }
  },

  // Sindhi
  sd(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Sinhala
  si(n: number): string | undefined {
    if (n == 0 || n == 1 || (Math.floor(n) == 0 && Plural.getF(n) == 1)) return PLURAL_ONE;
  },

  // Slovak
  sk(n: number): string | undefined {
    // same as Czech
    return Plural.cs(n);
  },

  // Slovenian
  sl(n: number): string | undefined {
    const v = Plural.getV(n);
    const mod100 = n % 100;

    if (v == 0 && mod100 == 1) return PLURAL_ONE;
    if (v == 0 && mod100 == 2) return PLURAL_TWO;
    if ((v == 0 && (mod100 == 3 || mod100 == 4)) || v != 0) return PLURAL_FEW;
  },

  // Albanian
  sq(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Serbian
  sr(n: number): string | undefined {
    // same as Bosnian
    return Plural.bs(n);
  },

  // Tamil
  ta(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Telugu
  te(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Tajik
  tg(n: number) {},

  // Thai
  th(n: number) {},

  // Turkmen
  tk(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Turkish
  tr(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Uyghur
  ug(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Ukrainian
  uk(n: number): string | undefined {
    // same as Russian
    return Plural.ru(n);
  },

  // Uzbek
  uz(n: number): string | undefined {
    if (n == 1) return PLURAL_ONE;
  },

  // Vietnamese
  vi(n: number) {},

  // Chinese
  zh(n: number) {},
};

export = Plural;
