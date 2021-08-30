/**
 * Data handler for a captcha form builder field in an Ajax form.
 *
 * @author  Matthias Schmidt
 * @copyright	2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Captcha
 * @since 5.2
 */
define(["require", "exports", "tslib", "./Field", "../../../Controller/Captcha", "../../../Core"], function (require, exports, tslib_1, Field_1, Captcha_1, Core) {
    "use strict";
    Field_1 = (0, tslib_1.__importDefault)(Field_1);
    Captcha_1 = (0, tslib_1.__importDefault)(Captcha_1);
    Core = (0, tslib_1.__importStar)(Core);
    class Captcha extends Field_1.default {
        _getData() {
            if (Captcha_1.default.has(this._fieldId)) {
                return Captcha_1.default.getData(this._fieldId);
            }
            return {};
        }
        _readField() {
            // does nothing
        }
        destroy() {
            if (Captcha_1.default.has(this._fieldId)) {
                Captcha_1.default.delete(this._fieldId);
            }
        }
    }
    Core.enableLegacyInheritance(Captcha);
    return Captcha;
});
