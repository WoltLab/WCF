/**
 * Converts `<woltlab-metacode>` into the bbcode representation.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../Article/Search"], function (require, exports, tslib_1, UiArticleSearch) {
    "use strict";
    UiArticleSearch = tslib_1.__importStar(UiArticleSearch);
    class UiRedactorArticle {
        _editor;
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
    return UiRedactorArticle;
});
