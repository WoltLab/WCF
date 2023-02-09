/**
 * Data handler for a wysiwyg attachment form builder field that stores the temporary hash.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.2
 */
define(["require", "exports", "tslib", "../Value"], function (require, exports, tslib_1, Value_1) {
    "use strict";
    Value_1 = tslib_1.__importDefault(Value_1);
    class Attachment extends Value_1.default {
        constructor(fieldId) {
            super(fieldId + "_tmpHash");
        }
    }
    return Attachment;
});
