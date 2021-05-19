/**
 * Converts `<woltlab-metacode>` into the bbcode representation.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Redactor/Article
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../../Core", "../Article/Search"], function (require, exports, tslib_1, Core, UiArticleSearch) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    UiArticleSearch = tslib_1.__importStar(UiArticleSearch);
    class UiRedactorArticle {
        constructor(editor, button) {
            this._editor = editor;
            button.addEventListener("click", (ev) => this._click(ev));
        }
        _click(event) {
            event.preventDefault();
            UiArticleSearch.open((articleId) => this._insert(articleId));
        }
        _insert(articleId) {
            this._editor.buffer.set();
            this._editor.insert.text(`[wsa='${articleId}'][/wsa]`);
        }
    }
    Core.enableLegacyInheritance(UiRedactorArticle);
    return UiRedactorArticle;
});
