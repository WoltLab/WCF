/**
 * Generates plural phrases for the `plural` template plugin.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright  2001-2020 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/I18n/Plural
 */

import * as StringUtil from "../StringUtil";

const enum Category {
  Few = "few",
  Many = "many",
  One = "one",
  Other = "other",
  Two = "two",
  Zero = "zero",
}

const Languages = {
  // Afrikaans
  af(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Amharic
  am(n: number): Category | undefined {
    const i = Math.floor(Math.abs(n));
    if (n == 1 || i === 0) {
      return Category.One;
    }
  },

  // Arabic
  ar(n: number): Category | undefined {
    if (n == 0) {
      return Category.Zero;
    }
    if (n == 1) {
      return Category.One;
    }
    if (n == 2) {
      return Category.Two;
    }

    const mod100 = n % 100;
    if (mod100 >= 3 && mod100 <= 10) {
      return Category.Few;
    }
    if (mod100 >= 11 && mod100 <= 99) {
      return Category.Many;
    }
  },

  // Assamese
  as(n: number): Category | undefined {
    const i = Math.floor(Math.abs(n));
    if (n == 1 || i === 0) {
      return Category.One;
    }
  },

  // Azerbaijani
  az(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Belarusian
  be(n: number): Category | undefined {
    const mod10 = n % 10;
    const mod100 = n % 100;

    if (mod10 == 1 && mod100 != 11) {
      return Category.One;
    }
    if (mod10 >= 2 && mod10 <= 4 && !(mod100 >= 12 && mod100 <= 14)) {
      return Category.Few;
    }
    if (mod10 == 0 || (mod10 >= 5 && mod10 <= 9) || (mod100 >= 11 && mod100 <= 14)) {
      return Category.Many;
    }
  },

  // Bulgarian
  bg(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Bengali
  bn(n: number): Category | undefined {
    const i = Math.floor(Math.abs(n));
    if (n == 1 || i === 0) {
      return Category.One;
    }
  },

  // Tibetan
  bo(_n: number): Category | undefined {
    return undefined;
  },

  // Bosnian
  bs(n: number): Category | undefined {
    const v = Plural.getV(n);
    const f = Plural.getF(n);
    const mod10 = n % 10;
    const mod100 = n % 100;
    const fMod10 = f % 10;
    const fMod100 = f % 100;

    if ((v == 0 && mod10 == 1 && mod100 != 11) || (fMod10 == 1 && fMod100 != 11)) {
      return Category.One;
    }
    if (
      (v == 0 && mod10 >= 2 && mod10 <= 4 && mod100 >= 12 && mod100 <= 14) ||
      (fMod10 >= 2 && fMod10 <= 4 && fMod100 >= 12 && fMod100 <= 14)
    ) {
      return Category.Few;
    }
  },

  // Czech
  cs(n: number): Category | undefined {
    const v = Plural.getV(n);

    if (n == 1 && v === 0) {
      return Category.One;
    }
    if (n >= 2 && n <= 4 && v === 0) {
      return Category.Few;
    }
    if (v === 0) {
      return Category.Many;
    }
  },

  // Welsh
  cy(n: number): Category | undefined {
    if (n == 0) {
      return Category.Zero;
    }
    if (n == 1) {
      return Category.One;
    }
    if (n == 2) {
      return Category.Two;
    }
    if (n == 3) {
      return Category.Few;
    }
    if (n == 6) {
      return Category.Many;
    }
  },

  // Danish
  da(n: number): Category | undefined {
    if (n > 0 && n < 2) {
      return Category.One;
    }
  },

  // Greek
  el(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
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
  en(n: number): Category | undefined {
    if (n == 1 && Plural.getV(n) === 0) {
      return Category.One;
    }
  },

  // Spanish
  es(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Basque
  eu(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Persian
  fa(n: number): Category | undefined {
    if (n >= 0 && n <= 1) {
      return Category.One;
    }
  },

  // French
  fr(n: number): Category | undefined {
    if (n >= 0 && n < 2) {
      return Category.One;
    }
  },

  // Irish
  ga(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
    if (n == 2) {
      return Category.Two;
    }
    if (n == 3 || n == 4 || n == 5 || n == 6) {
      return Category.Few;
    }
    if (n == 7 || n == 8 || n == 9 || n == 10) {
      return Category.Many;
    }
  },

  // Gujarati
  gu(n: number): Category | undefined {
    if (n >= 0 && n <= 1) {
      return Category.One;
    }
  },

  // Hebrew
  he(n: number): Category | undefined {
    const v = Plural.getV(n);

    if (n == 1 && v === 0) {
      return Category.One;
    }
    if (n == 2 && v === 0) {
      return Category.Two;
    }
    if (n > 10 && v === 0 && n % 10 == 0) {
      return Category.Many;
    }
  },

  // Hindi
  hi(n: number): Category | undefined {
    if (n >= 0 && n <= 1) {
      return Category.One;
    }
  },

  // Croatian
  hr(n: number): Category | undefined {
    // same as Bosnian
    return Plural.bs(n);
  },

  // Hungarian
  hu(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Armenian
  hy(n: number): Category | undefined {
    if (n >= 0 && n < 2) {
      return Category.One;
    }
  },

  // Indonesian
  id(_n: number): Category | undefined {
    return undefined;
  },

  // Icelandic
  is(n: number): Category | undefined {
    const f = Plural.getF(n);

    if ((f === 0 && n % 10 === 1 && !(n % 100 === 11)) || !(f === 0)) {
      return Category.One;
    }
  },

  // Japanese
  ja(_n: number): Category | undefined {
    return undefined;
  },

  // Javanese
  jv(_n: number): Category | undefined {
    return undefined;
  },

  // Georgian
  ka(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Kazakh
  kk(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Khmer
  km(_n: number): Category | undefined {
    return undefined;
  },

  // Kannada
  kn(n: number): Category | undefined {
    if (n >= 0 && n <= 1) {
      return Category.One;
    }
  },

  // Korean
  ko(_n: number): Category | undefined {
    return undefined;
  },

  // Kurdish
  ku(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Kyrgyz
  ky(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Luxembourgish
  lb(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Lao
  lo(_n: number): Category | undefined {
    return undefined;
  },

  // Lithuanian
  lt(n: number): Category | undefined {
    const mod10 = n % 10;
    const mod100 = n % 100;

    if (mod10 == 1 && !(mod100 >= 11 && mod100 <= 19)) {
      return Category.One;
    }
    if (mod10 >= 2 && mod10 <= 9 && !(mod100 >= 11 && mod100 <= 19)) {
      return Category.Few;
    }
    if (Plural.getF(n) != 0) {
      return Category.Many;
    }
  },

  // Latvian
  lv(n: number): Category | undefined {
    const mod10 = n % 10;
    const mod100 = n % 100;
    const v = Plural.getV(n);
    const f = Plural.getF(n);
    const fMod10 = f % 10;
    const fMod100 = f % 100;

    if (mod10 == 0 || (mod100 >= 11 && mod100 <= 19) || (v == 2 && fMod100 >= 11 && fMod100 <= 19)) {
      return Category.Zero;
    }
    if ((mod10 == 1 && mod100 != 11) || (v == 2 && fMod10 == 1 && fMod100 != 11) || (v != 2 && fMod10 == 1)) {
      return Category.One;
    }
  },

  // Macedonian
  mk(n: number): Category | undefined {
    return Plural.bs(n);
  },

  // Malayalam
  ml(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Mongolian
  mn(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Marathi
  mr(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Malay
  ms(_n: number): Category | undefined {
    return undefined;
  },

  // Maltese
  mt(n: number): Category | undefined {
    const mod100 = n % 100;

    if (n == 1) {
      return Category.One;
    }
    if (n == 0 || (mod100 >= 2 && mod100 <= 10)) {
      return Category.Few;
    }
    if (mod100 >= 11 && mod100 <= 19) {
      return Category.Many;
    }
  },

  // Burmese
  my(_n: number): Category | undefined {
    return undefined;
  },

  // Norwegian
  no(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Nepali
  ne(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Odia
  or(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Punjabi
  pa(n: number): Category | undefined {
    if (n == 1 || n == 0) {
      return Category.One;
    }
  },

  // Polish
  pl(n: number): Category | undefined {
    const v = Plural.getV(n);
    const mod10 = n % 10;
    const mod100 = n % 100;

    if (n == 1 && v == 0) {
      return Category.One;
    }
    if (v == 0 && mod10 >= 2 && mod10 <= 4 && !(mod100 >= 12 && mod100 <= 14)) {
      return Category.Few;
    }
    if (
      v == 0 &&
      ((n != 1 && mod10 >= 0 && mod10 <= 1) || (mod10 >= 5 && mod10 <= 9) || (mod100 >= 12 && mod100 <= 14))
    ) {
      return Category.Many;
    }
  },

  // Pashto
  ps(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Portuguese
  pt(n: number): Category | undefined {
    if (n >= 0 && n < 2) {
      return Category.One;
    }
  },

  // Romanian
  ro(n: number): Category | undefined {
    const v = Plural.getV(n);
    const mod100 = n % 100;

    if (n == 1 && v === 0) {
      return Category.One;
    }
    if (v != 0 || n == 0 || (mod100 >= 2 && mod100 <= 19)) {
      return Category.Few;
    }
  },

  // Russian
  ru(n: number): Category | undefined {
    const mod10 = n % 10;
    const mod100 = n % 100;

    if (Plural.getV(n) == 0) {
      if (mod10 == 1 && mod100 != 11) {
        return Category.One;
      }
      if (mod10 >= 2 && mod10 <= 4 && !(mod100 >= 12 && mod100 <= 14)) {
        return Category.Few;
      }
      if (mod10 == 0 || (mod10 >= 5 && mod10 <= 9) || (mod100 >= 11 && mod100 <= 14)) {
        return Category.Many;
      }
    }
  },

  // Sindhi
  sd(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Sinhala
  si(n: number): Category | undefined {
    if (n == 0 || n == 1 || (Math.floor(n) == 0 && Plural.getF(n) == 1)) {
      return Category.One;
    }
  },

  // Slovak
  sk(n: number): Category | undefined {
    // same as Czech
    return Plural.cs(n);
  },

  // Slovenian
  sl(n: number): Category | undefined {
    const v = Plural.getV(n);
    const mod100 = n % 100;

    if (v == 0 && mod100 == 1) {
      return Category.One;
    }
    if (v == 0 && mod100 == 2) {
      return Category.Two;
    }
    if ((v == 0 && (mod100 == 3 || mod100 == 4)) || v != 0) {
      return Category.Few;
    }
  },

  // Albanian
  sq(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Serbian
  sr(n: number): Category | undefined {
    // same as Bosnian
    return Plural.bs(n);
  },

  // Tamil
  ta(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Telugu
  te(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Tajik
  tg(_n: number): Category | undefined {
    return undefined;
  },

  // Thai
  th(_n: number): Category | undefined {
    return undefined;
  },

  // Turkmen
  tk(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Turkish
  tr(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Uyghur
  ug(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Ukrainian
  uk(n: number): Category | undefined {
    // same as Russian
    return Plural.ru(n);
  },

  // Uzbek
  uz(n: number): Category | undefined {
    if (n == 1) {
      return Category.One;
    }
  },

  // Vietnamese
  vi(_n: number): Category | undefined {
    return undefined;
  },

  // Chinese
  zh(_n: number): Category | undefined {
    return undefined;
  },
};

type ValidLanguage = keyof typeof Languages;

// Note: This cannot be an interface due to the computed property.
type Parameters = {
  value: number;
  other: string;
} & {
  [category in Category]?: string;
} & {
  [number: number]: string;
};

const Plural = {
  /**
   * Returns the plural category for the given value.
   */
  getCategory(value: number, languageCode?: ValidLanguage): Category {
    if (!languageCode) {
      languageCode = document.documentElement.lang as ValidLanguage;
    }

    // Fallback: handle unknown languages as English
    if (typeof Plural[languageCode] !== "function") {
      languageCode = "en";
    }

    const category = Plural[languageCode](value);
    if (category) {
      return category;
    }

    return Category.Other;
  },

  /**
   * Returns the value for a `plural` element used in the template.
   *
   * @see    wcf\system\template\plugin\PluralFunctionTemplatePlugin::execute()
   */
  getCategoryFromTemplateParameters(parameters: Parameters): string {
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
    const numericAttribute = Object.keys(parameters).find((key) => {
      return key.toString() === (~~key).toString() && key.toString() === value.toString();
    });

    if (numericAttribute) {
      return numericAttribute;
    }

    let category = Plural.getCategory(value);
    if (!parameters[category]) {
      category = Category.Other;
    }

    const string = parameters[category]!;
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

  ...Languages,
};

export = Plural;
