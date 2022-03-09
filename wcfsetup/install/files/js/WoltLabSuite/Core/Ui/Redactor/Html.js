/**
 * Manages html code blocks.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Redactor/Html
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../../Core", "../../Event/Handler", "../../Language"], function (require, exports, tslib_1, Core, EventHandler, Language) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    EventHandler = tslib_1.__importStar(EventHandler);
    Language = tslib_1.__importStar(Language);
    class UiRedactorHtml {
        /**
         * Initializes the source code management.
         */
        constructor(editor) {
            this._pre = null;
            this._editor = editor;
            this._elementId = this._editor.$element[0].id;
            EventHandler.add("com.woltlab.wcf.redactor2", `bbcode_woltlabHtml_${this._elementId}`, (data) => this._bbcodeCode(data));
            EventHandler.add("com.woltlab.wcf.redactor2", `observe_load_${this._elementId}`, () => this._observeLoad());
            // support for active button marking
            this._editor.opts.activeButtonsStates["woltlab-html"] = "woltlabHtml";
            // bind listeners on init
            this._observeLoad();
        }
        /**
         * Intercepts the insertion of `[woltlabHtml]` tags and uses a native `<pre>` instead.
         */
        _bbcodeCode(data) {
            data.cancel = true;
            let pre = this._editor.selection.block();
            if (pre && pre.nodeName === "PRE" && !pre.classList.contains("woltlabHtml")) {
                return;
            }
            this._editor.button.toggle({}, "pre", "func", "block.format");
            pre = this._editor.selection.block();
            if (pre && pre.nodeName === "PRE") {
                pre.classList.add("woltlabHtml");
                if (pre.childElementCount === 1 && pre.children[0].nodeName === "BR") {
                    // drop superfluous linebreak
                    pre.removeChild(pre.children[0]);
                }
                this._setTitle(pre);
                // work-around for Safari
                this._editor.caret.end(pre);
            }
        }
        /**
         * Binds event listeners and sets quote title on both editor
         * initialization and when switching back from code view.
         */
        _observeLoad() {
            this._editor.$editor[0].querySelectorAll("pre.woltlabHtml").forEach((pre) => {
                this._setTitle(pre);
            });
        }
        /**
         * Sets or updates the code's header title.
         */
        _setTitle(pre) {
            ["title", "description"].forEach((title) => {
                const phrase = Language.get(`wcf.editor.html.${title}`);
                if (pre.dataset[title] !== phrase) {
                    pre.dataset[title] = phrase;
                }
            });
        }
    }
    Core.enableLegacyInheritance(UiRedactorHtml);
    return UiRedactorHtml;
});
