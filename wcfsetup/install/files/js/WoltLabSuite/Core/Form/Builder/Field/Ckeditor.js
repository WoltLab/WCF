/**
 * Data handler for CKEditor.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports", "tslib", "./Field", "WoltLabSuite/Core/Component/Ckeditor/Event", "WoltLabSuite/Core/Component/Ckeditor"], function (require, exports, tslib_1, Field_1, Event_1, Ckeditor_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Ckeditor = void 0;
    Field_1 = tslib_1.__importDefault(Field_1);
    class Ckeditor extends Field_1.default {
        _getData() {
            const ckeditor = (0, Ckeditor_1.getCkeditorById)(this._fieldId);
            return {
                [this._fieldId]: ckeditor.getHtml(),
            };
        }
        destroy() {
            (0, Event_1.dispatchToCkeditor)(this._field).destroy();
        }
    }
    exports.Ckeditor = Ckeditor;
    exports.default = Ckeditor;
});
