/**
 * Data handler for CKEditor with l10n support.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "tslib", "./ValueI18n", "WoltLabSuite/Core/Component/Ckeditor/Event", "WoltLabSuite/Core/Component/Ckeditor"], function (require, exports, tslib_1, ValueI18n_1, Event_1, Ckeditor_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.CkeditorI18n = void 0;
    ValueI18n_1 = tslib_1.__importDefault(ValueI18n_1);
    class CkeditorI18n extends ValueI18n_1.default {
        _getData() {
            // CKEditor does not permanently mirror the contents to the <textarea>.
            this._field.value = (0, Ckeditor_1.getCkeditorById)(this._fieldId).getHtml();
            return super._getData();
        }
        destroy() {
            super.destroy();
            (0, Event_1.dispatchToCkeditor)(this._field).destroy();
        }
    }
    exports.CkeditorI18n = CkeditorI18n;
    exports.default = CkeditorI18n;
});
