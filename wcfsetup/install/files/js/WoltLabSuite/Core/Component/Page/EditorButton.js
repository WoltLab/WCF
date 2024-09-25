/**
 * Integrates an editor button to inserts links to CMS pages.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "../../Language", "../../Ui/Page/Search", "../Ckeditor/Event"], function (require, exports, Language_1, Search_1, Event_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    function setupBbcode(ckeditor) {
        (0, Event_1.listenToCkeditor)(ckeditor.sourceElement).bbcode(({ bbcode }) => {
            if (bbcode !== "wsp") {
                return false;
            }
            (0, Search_1.open)((articleId) => {
                ckeditor.insertText(`[wsp='${articleId}'][/wsp]`);
            });
            return true;
        });
    }
    function setup(element) {
        (0, Event_1.listenToCkeditor)(element).setupConfiguration(({ configuration }) => {
            configuration.woltlabBbcode.push({
                icon: "file-lines;false",
                name: "wsp",
                label: (0, Language_1.getPhrase)("wcf.editor.button.page"),
            });
        });
        (0, Event_1.listenToCkeditor)(element).ready(({ ckeditor }) => {
            setupBbcode(ckeditor);
        });
    }
});
