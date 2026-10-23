/**
 * Data handler for the options of a poll.
 *
 * @author  Marcel Werk
 * @copyright  2001-2026 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "tslib", "./Field", "WoltLabSuite/Core/Component/Poll/OptionList"], function (require, exports, tslib_1, Field_1, OptionList_1) {
    "use strict";
    Field_1 = tslib_1.__importDefault(Field_1);
    class PollOptionList extends Field_1.default {
        _getData() {
            return {
                [this._fieldId]: (0, OptionList_1.getOptionListValue)(this._fieldId),
            };
        }
        destroy() {
            (0, OptionList_1.removeOptionList)(this._fieldId);
        }
    }
    return PollOptionList;
});
