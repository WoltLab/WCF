/**
 * Data handler for a wysiwyg attachment form builder field that stores the temporary hash.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.2
 */
define(["require", "exports", "tslib", "../Field"], function (require, exports, tslib_1, Field_1) {
    "use strict";
    Field_1 = tslib_1.__importDefault(Field_1);
    class Attachment extends Field_1.default {
        _getData() {
            const tmpHashes = Array.from(document.querySelectorAll(`input[name="${this._fieldId}_tmpHash[]"]`), (input) => input.value);
            if (tmpHashes.length === 0) {
                return {};
            }
            return {
                [this._fieldId + "_tmpHash"]: tmpHashes,
            };
        }
        _readField() {
            // The temporary hashes are stored in multiple hidden inputs that share the same name.
        }
    }
    return Attachment;
});
