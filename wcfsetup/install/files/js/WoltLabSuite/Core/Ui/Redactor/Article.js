/**
 * Converts `<woltlab-metacode>` into the bbcode representation.
 *
 * @author      Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Redactor/Article
 */
define(['WoltLabSuite/Core/Ui/Article/Search'], function (UiArticleSearch) {
    "use strict";
    if (!COMPILER_TARGET_DEFAULT) {
        var Fake = function () { };
        Fake.prototype = {
            init: function () { },
            _click: function () { },
            _insert: function () { }
        };
        return Fake;
    }
    function UiRedactorArticle(editor, button) { this.init(editor, button); }
    UiRedactorArticle.prototype = {
        init: function (editor, button) {
            this._editor = editor;
            button.addEventListener(WCF_CLICK_EVENT, this._click.bind(this));
        },
        _click: function (event) {
            event.preventDefault();
            UiArticleSearch.open(this._insert.bind(this));
        },
        _insert: function (articleId) {
            this._editor.buffer.set();
            this._editor.insert.text("[wsa='" + articleId + "'][/wsa]");
        }
    };
    return UiRedactorArticle;
});
