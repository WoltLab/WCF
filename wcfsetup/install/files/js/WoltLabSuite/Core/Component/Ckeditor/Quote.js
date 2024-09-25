/**
 * Inserts quotes into the editor.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "../../StringUtil", "./Event"], function (require, exports, StringUtil_1, Event_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    function insertQuote(editor, payload) {
        let { author, content, link } = payload;
        if (payload.isText) {
            content = approximateHtmlRepresentation(content);
        }
        author = (0, StringUtil_1.escapeHTML)(author);
        link = (0, StringUtil_1.escapeHTML)(link);
        editor.insertHtml(`<woltlab-quote data-author="${author}" data-link="${link}">${content}</woltlab-quote>
    <p><br data-cke-filler="true"></p>`);
    }
    function approximateHtmlRepresentation(text) {
        text = (0, StringUtil_1.escapeHTML)(text);
        // An empty paragraph is marked by 5 consecutive new lines.
        text = text.replaceAll("\n\n\n\n\n", '</p><p><br data-cke-filler="true"></p><p>');
        return text
            .split("\n\n")
            .map((value) => {
            value = value.replaceAll("\n", "<br>");
            return `<p>${value}</p>`;
        })
            .join("");
    }
    function setup(element) {
        (0, Event_1.listenToCkeditor)(element).ready(({ ckeditor }) => {
            (0, Event_1.listenToCkeditor)(element).insertQuote((payload) => {
                insertQuote(ckeditor, payload);
            });
        });
    }
});
