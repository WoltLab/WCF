/**
 * Data handler for a content language form builder field in an Ajax form.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Language/ContentLanguage
 * @since 5.2
 */
define(["require", "exports", "tslib", "../Value", "../../../../Language/Chooser", "../../../../Core"], function (require, exports, tslib_1, Value_1, LanguageChooser, Core) {
    "use strict";
    Value_1 = tslib_1.__importDefault(Value_1);
    LanguageChooser = tslib_1.__importStar(LanguageChooser);
    Core = tslib_1.__importStar(Core);
    class ContentLanguage extends Value_1.default {
        destroy() {
            LanguageChooser.removeChooser(this._fieldId);
        }
    }
    Core.enableLegacyInheritance(ContentLanguage);
    return ContentLanguage;
});
