/**
 * Data handler for a file processor form builder field in an Ajax form.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Form/Builder/Field/Field", "WoltLabSuite/Core/Form/Builder/Field/Controller/FileProcessor"], function (require, exports, tslib_1, Field_1, FileProcessor_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    Field_1 = tslib_1.__importDefault(Field_1);
    class FileProcessor extends Field_1.default {
        _getData() {
            const value = (0, FileProcessor_1.getValues)(this._fieldId);
            if (value === undefined) {
                return {};
            }
            const data = {
                [this._fieldId]: value,
            };
            // The files were uploaded with the token, the server must receive it again.
            const uploaderToken = document.getElementById(`${this._fieldId}_uploaderToken`);
            if (uploaderToken !== null) {
                data[`${this._fieldId}_uploaderToken`] = uploaderToken.value;
            }
            return data;
        }
        _readField() {
            // does nothing
        }
    }
    exports.default = FileProcessor;
});
