/**
 * Generates plural phrases for the `plural` template plugin.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright  2001-2020 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/I18n/Plural
 */
define(["require", "exports", "tslib", "../StringUtil"], function (require, exports, tslib_1, StringUtil) {
    "use strict";
    StringUtil = tslib_1.__importStar(StringUtil);
    const PLURAL_FEW = 'few';
    const PLURAL_MANY = 'many';
    const PLURAL_ONE = 'one';
    const PLURAL_OTHER = 'other';
    const PLURAL_TWO = 'two';
    const PLURAL_ZERO = 'zero';
    const Plural = {
        /**
         * Returns the plural category for the given value.
         */
        getCategory(value, languageCode) {
            if (!languageCode) {
                languageCode = document.documentElement.lang;
            }
            // Fallback: handle unknown languages as English
            if (typeof Plural[languageCode] !== 'function') {
                languageCode = 'en';
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
        getCategoryFromTemplateParameters(parameters) {
            if (!parameters['value']) {
                throw new Error('Missing parameter value');
            }
            if (!parameters['other']) {
                throw new Error('Missing parameter other');
            }
            let value = parameters['value'];
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
            if (string.indexOf('#') !== -1) {
                return string.replace('#', StringUtil.formatNumeric(value));
            }
            return string;
        },
        /**
         * `f` is the fractional number as a whole number (1.234 yields 234)
         */
        getF(n) {
            const tmp = n.toString();
            const pos = tmp.indexOf('.');
            if (pos === -1) {
                return 0;
            }
            return parseInt(tmp.substr(pos + 1), 10);
        },
        /**
         * `v` represents the number of digits of the fractional part (1.234 yields 3)
         */
        getV(n) {
            return n.toString().replace(/^[^.]*\.?/, '').length;
        },
        // Afrikaans
        af(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Amharic
        am(n) {
            const i = Math.floor(Math.abs(n));
            if (n == 1 || i === 0)
                return PLURAL_ONE;
        },
        // Arabic
        ar(n) {
            if (n == 0)
                return PLURAL_ZERO;
            if (n == 1)
                return PLURAL_ONE;
            if (n == 2)
                return PLURAL_TWO;
            const mod100 = n % 100;
            if (mod100 >= 3 && mod100 <= 10)
                return PLURAL_FEW;
            if (mod100 >= 11 && mod100 <= 99)
                return PLURAL_MANY;
        },
        // Assamese
        as(n) {
            const i = Math.floor(Math.abs(n));
            if (n == 1 || i === 0)
                return PLURAL_ONE;
        },
        // Azerbaijani
        az(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Belarusian
        be(n) {
            const mod10 = n % 10;
            const mod100 = n % 100;
            if (mod10 == 1 && mod100 != 11)
                return PLURAL_ONE;
            if (mod10 >= 2 && mod10 <= 4 && !(mod100 >= 12 && mod100 <= 14))
                return PLURAL_FEW;
            if (mod10 == 0 || (mod10 >= 5 && mod10 <= 9) || (mod100 >= 11 && mod100 <= 14))
                return PLURAL_MANY;
        },
        // Bulgarian
        bg(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Bengali
        bn(n) {
            const i = Math.floor(Math.abs(n));
            if (n == 1 || i === 0)
                return PLURAL_ONE;
        },
        // Tibetan
        bo(n) {
        },
        // Bosnian
        bs(n) {
            const v = Plural.getV(n);
            const f = Plural.getF(n);
            const mod10 = n % 10;
            const mod100 = n % 100;
            const fMod10 = f % 10;
            const fMod100 = f % 100;
            if ((v == 0 && mod10 == 1 && mod100 != 11) || (fMod10 == 1 && fMod100 != 11))
                return PLURAL_ONE;
            if ((v == 0 && mod10 >= 2 && mod10 <= 4 && mod100 >= 12 && mod100 <= 14)
                || (fMod10 >= 2 && fMod10 <= 4 && fMod100 >= 12 && fMod100 <= 14))
                return PLURAL_FEW;
        },
        // Czech
        cs(n) {
            const v = Plural.getV(n);
            if (n == 1 && v === 0)
                return PLURAL_ONE;
            if (n >= 2 && n <= 4 && v === 0)
                return PLURAL_FEW;
            if (v === 0)
                return PLURAL_MANY;
        },
        // Welsh
        cy(n) {
            if (n == 0)
                return PLURAL_ZERO;
            if (n == 1)
                return PLURAL_ONE;
            if (n == 2)
                return PLURAL_TWO;
            if (n == 3)
                return PLURAL_FEW;
            if (n == 6)
                return PLURAL_MANY;
        },
        // Danish
        da(n) {
            if (n > 0 && n < 2)
                return PLURAL_ONE;
        },
        // Greek
        el(n) {
            if (n == 1)
                return PLURAL_ONE;
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
        en(n) {
            if (n == 1 && Plural.getV(n) === 0)
                return PLURAL_ONE;
        },
        // Spanish
        es(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Basque
        eu(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Persian
        fa(n) {
            if (n >= 0 && n <= 1)
                return PLURAL_ONE;
        },
        // French
        fr(n) {
            if (n >= 0 && n < 2)
                return PLURAL_ONE;
        },
        // Irish
        ga(n) {
            if (n == 1)
                return PLURAL_ONE;
            if (n == 2)
                return PLURAL_TWO;
            if (n == 3 || n == 4 || n == 5 || n == 6)
                return PLURAL_FEW;
            if (n == 7 || n == 8 || n == 9 || n == 10)
                return PLURAL_MANY;
        },
        // Gujarati
        gu(n) {
            if (n >= 0 && n <= 1)
                return PLURAL_ONE;
        },
        // Hebrew
        he(n) {
            const v = Plural.getV(n);
            if (n == 1 && v === 0)
                return PLURAL_ONE;
            if (n == 2 && v === 0)
                return PLURAL_TWO;
            if (n > 10 && v === 0 && n % 10 == 0)
                return PLURAL_MANY;
        },
        // Hindi
        hi(n) {
            if (n >= 0 && n <= 1)
                return PLURAL_ONE;
        },
        // Croatian
        hr(n) {
            // same as Bosnian
            return Plural.bs(n);
        },
        // Hungarian
        hu(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Armenian
        hy(n) {
            if (n >= 0 && n < 2)
                return PLURAL_ONE;
        },
        // Indonesian
        id(n) {
        },
        // Icelandic
        is(n) {
            const f = Plural.getF(n);
            if (f === 0 && n % 10 === 1 && !(n % 100 === 11) || !(f === 0))
                return PLURAL_ONE;
        },
        // Japanese
        ja(n) {
        },
        // Javanese
        jv(n) {
        },
        // Georgian
        ka(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Kazakh
        kk(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Khmer
        km(n) {
        },
        // Kannada
        kn(n) {
            if (n >= 0 && n <= 1)
                return PLURAL_ONE;
        },
        // Korean
        ko(n) {
        },
        // Kurdish
        ku(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Kyrgyz
        ky(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Luxembourgish
        lb(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Lao
        lo(n) {
        },
        // Lithuanian
        lt(n) {
            const mod10 = n % 10;
            const mod100 = n % 100;
            if (mod10 == 1 && !(mod100 >= 11 && mod100 <= 19))
                return PLURAL_ONE;
            if (mod10 >= 2 && mod10 <= 9 && !(mod100 >= 11 && mod100 <= 19))
                return PLURAL_FEW;
            if (Plural.getF(n) != 0)
                return PLURAL_MANY;
        },
        // Latvian
        lv(n) {
            const mod10 = n % 10;
            const mod100 = n % 100;
            const v = Plural.getV(n);
            const f = Plural.getF(n);
            const fMod10 = f % 10;
            const fMod100 = f % 100;
            if (mod10 == 0 || (mod100 >= 11 && mod100 <= 19) || (v == 2 && fMod100 >= 11 && fMod100 <= 19))
                return PLURAL_ZERO;
            if ((mod10 == 1 && mod100 != 11) || (v == 2 && fMod10 == 1 && fMod100 != 11) || (v != 2 && fMod10 == 1))
                return PLURAL_ONE;
        },
        // Macedonian
        mk(n) {
            return Plural.bs(n);
        },
        // Malayalam
        ml(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Mongolian 
        mn(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Marathi 
        mr(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Malay 
        ms(n) {
        },
        // Maltese 
        mt(n) {
            const mod100 = n % 100;
            if (n == 1)
                return PLURAL_ONE;
            if (n == 0 || (mod100 >= 2 && mod100 <= 10))
                return PLURAL_FEW;
            if (mod100 >= 11 && mod100 <= 19)
                return PLURAL_MANY;
        },
        // Burmese
        my(n) {
        },
        // Norwegian
        no(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Nepali
        ne(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Odia
        or(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Punjabi
        pa(n) {
            if (n == 1 || n == 0)
                return PLURAL_ONE;
        },
        // Polish
        pl(n) {
            const v = Plural.getV(n);
            const mod10 = n % 10;
            const mod100 = n % 100;
            if (n == 1 && v == 0)
                return PLURAL_ONE;
            if (v == 0 && mod10 >= 2 && mod10 <= 4 && !(mod100 >= 12 && mod100 <= 14))
                return PLURAL_FEW;
            if (v == 0 && ((n != 1 && mod10 >= 0 && mod10 <= 1) || (mod10 >= 5 && mod10 <= 9) || (mod100 >= 12 && mod100 <= 14)))
                return PLURAL_MANY;
        },
        // Pashto
        ps(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Portuguese
        pt(n) {
            if (n >= 0 && n < 2)
                return PLURAL_ONE;
        },
        // Romanian
        ro(n) {
            const v = Plural.getV(n);
            const mod100 = n % 100;
            if (n == 1 && v === 0)
                return PLURAL_ONE;
            if (v != 0 || n == 0 || (mod100 >= 2 && mod100 <= 19))
                return PLURAL_FEW;
        },
        // Russian
        ru(n) {
            const mod10 = n % 10;
            const mod100 = n % 100;
            if (Plural.getV(n) == 0) {
                if (mod10 == 1 && mod100 != 11)
                    return PLURAL_ONE;
                if (mod10 >= 2 && mod10 <= 4 && !(mod100 >= 12 && mod100 <= 14))
                    return PLURAL_FEW;
                if (mod10 == 0 || (mod10 >= 5 && mod10 <= 9) || (mod100 >= 11 && mod100 <= 14))
                    return PLURAL_MANY;
            }
        },
        // Sindhi
        sd(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Sinhala
        si(n) {
            if (n == 0 || n == 1 || (Math.floor(n) == 0 && Plural.getF(n) == 1))
                return PLURAL_ONE;
        },
        // Slovak
        sk(n) {
            // same as Czech
            return Plural.cs(n);
        },
        // Slovenian
        sl(n) {
            const v = Plural.getV(n);
            const mod100 = n % 100;
            if (v == 0 && mod100 == 1)
                return PLURAL_ONE;
            if (v == 0 && mod100 == 2)
                return PLURAL_TWO;
            if ((v == 0 && (mod100 == 3 || mod100 == 4)) || v != 0)
                return PLURAL_FEW;
        },
        // Albanian
        sq(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Serbian
        sr(n) {
            // same as Bosnian
            return Plural.bs(n);
        },
        // Tamil
        ta(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Telugu
        te(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Tajik
        tg(n) {
        },
        // Thai
        th(n) {
        },
        // Turkmen
        tk(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Turkish
        tr(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Uyghur
        ug(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Ukrainian
        uk(n) {
            // same as Russian
            return Plural.ru(n);
        },
        // Uzbek
        uz(n) {
            if (n == 1)
                return PLURAL_ONE;
        },
        // Vietnamese
        vi(n) {
        },
        // Chinese
        zh(n) {
        },
    };
    return Plural;
});
