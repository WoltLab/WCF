/**
 * Data handler for the poll options.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Wysiwyg/Poll
 * @since 5.2
 */
define(["require", "exports", "tslib", "../Field", "../../../../Core"], function (require, exports, tslib_1, Field_1, Core) {
    "use strict";
    Field_1 = (0, tslib_1.__importDefault)(Field_1);
    Core = (0, tslib_1.__importStar)(Core);
    class Poll extends Field_1.default {
        _getData() {
            return this._pollEditor.getData();
        }
        _readField() {
            // does nothing
        }
        setPollEditor(pollEditor) {
            this._pollEditor = pollEditor;
        }
    }
    Core.enableLegacyInheritance(Poll);
    return Poll;
});
