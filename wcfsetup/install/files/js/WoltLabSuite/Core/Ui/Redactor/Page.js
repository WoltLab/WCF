/**
 * Converts `<woltlab-metacode>` into the bbcode representation.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Redactor/Page
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../../Core", "../Page/Search"], function (require, exports, tslib_1, Core, UiPageSearch) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    UiPageSearch = tslib_1.__importStar(UiPageSearch);
    class UiRedactorPage {
        constructor(editor, button) {
            this._editor = editor;
            button.addEventListener("click", (ev) => this._click(ev));
        }
        _click(event) {
            event.preventDefault();
            UiPageSearch.open((pageId) => this._insert(pageId));
        }
        _insert(pageId) {
            this._editor.buffer.set();
            this._editor.insert.text(`[wsp='${pageId}'][/wsp]`);
        }
    }
    Core.enableLegacyInheritance(UiRedactorPage);
    return UiRedactorPage;
});
