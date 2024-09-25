/**
 * Modifies the behavior of the 'Enter' key to submit the editor instead of
 * starting a new paragraph. 'Shift' + 'Enter' can be used to create a line
 * break.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "./Event"], function (require, exports, Event_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    function setup(editor, ckeditor) {
        editor.editing.view.document.on("enter", (evt, data) => {
            // Shift+Enter is allowed to create line breaks.
            if (data.isSoft) {
                return;
            }
            data.preventDefault();
            evt.stop();
            const html = ckeditor.getHtml();
            if (html !== "") {
                (0, Event_1.dispatchToCkeditor)(ckeditor.sourceElement).submitOnEnter({
                    ckeditor,
                    html,
                });
            }
        }, { priority: "high" });
    }
});
