/**
 * Converts `<woltlab-metacode>` into the bbcode representation.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Redactor/Metacode
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../../Event/Handler", "../../Dom/Util", "../../StringUtil"], function (require, exports, tslib_1, EventHandler, Util_1, StringUtil) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.convertFromHtml = void 0;
    EventHandler = tslib_1.__importStar(EventHandler);
    Util_1 = tslib_1.__importDefault(Util_1);
    StringUtil = tslib_1.__importStar(StringUtil);
    /**
     * Returns a text node representing the opening bbcode tag.
     */
    function getOpeningTag(name, attributes) {
        let buffer = "[" + name;
        if (attributes.length) {
            buffer += "=";
            buffer += attributes
                .map((attribute) => StringUtil.unescapeHTML(attribute))
                .map((attribute) => `'${attribute}'`)
                .join(",");
        }
        return document.createTextNode(buffer + "]");
    }
    /**
     * Returns a text node representing the closing bbcode tag.
     */
    function getClosingTag(name) {
        return document.createTextNode(`[/${name}]`);
    }
    /**
     * Returns the first paragraph of provided element. If there are no children or
     * the first child is not a paragraph, a new paragraph is created and inserted
     * as first child.
     */
    function getFirstParagraph(element) {
        let paragraph;
        if (element.childElementCount === 0) {
            paragraph = document.createElement("p");
            element.appendChild(paragraph);
        }
        else {
            const firstChild = element.children[0];
            if (firstChild.nodeName === "P") {
                paragraph = firstChild;
            }
            else {
                paragraph = document.createElement("p");
                element.insertBefore(paragraph, firstChild);
            }
        }
        return paragraph;
    }
    /**
     * Returns the last paragraph of provided element. If there are no children or
     * the last child is not a paragraph, a new paragraph is created and inserted
     * as last child.
     */
    function getLastParagraph(element) {
        const count = element.childElementCount;
        let paragraph;
        if (count === 0) {
            paragraph = document.createElement("p");
            element.appendChild(paragraph);
        }
        else {
            const lastChild = element.children[count - 1];
            if (lastChild.nodeName === "P") {
                paragraph = lastChild;
            }
            else {
                paragraph = document.createElement("p");
                element.appendChild(paragraph);
            }
        }
        return paragraph;
    }
    /**
     * Parses the attributes string.
     */
    function parseAttributes(attributes) {
        try {
            attributes = JSON.parse(atob(attributes));
        }
        catch (e) {
            /* invalid base64 data or invalid json */
        }
        if (!Array.isArray(attributes)) {
            return [];
        }
        return attributes.map((attribute) => {
            return attribute.toString().replace(/^'(.*)'$/, "$1");
        });
    }
    function convertFromHtml(editorId, html) {
        const div = document.createElement("div");
        div.innerHTML = html;
        div.querySelectorAll("woltlab-metacode").forEach((metacode) => {
            const name = metacode.dataset.name;
            const attributes = parseAttributes(metacode.dataset.attributes || "");
            const data = {
                attributes: attributes,
                cancel: false,
                metacode: metacode,
            };
            EventHandler.fire("com.woltlab.wcf.redactor2", `metacode_${name}_${editorId}`, data);
            if (data.cancel) {
                return;
            }
            const tagOpen = getOpeningTag(name, attributes);
            const tagClose = getClosingTag(name);
            if (metacode.parentElement === div) {
                const paragraph = getFirstParagraph(metacode);
                paragraph.insertBefore(tagOpen, paragraph.firstChild);
                getLastParagraph(metacode).appendChild(tagClose);
            }
            else {
                metacode.insertBefore(tagOpen, metacode.firstChild);
                metacode.appendChild(tagClose);
            }
            Util_1.default.unwrapChildNodes(metacode);
        });
        // convert `<kbd>…</kbd>` to `[tt]…[/tt]`
        div.querySelectorAll("kbd").forEach((inlineCode) => {
            inlineCode.insertBefore(document.createTextNode("[tt]"), inlineCode.firstChild);
            inlineCode.appendChild(document.createTextNode("[/tt]"));
            Util_1.default.unwrapChildNodes(inlineCode);
        });
        return div.innerHTML;
    }
    exports.convertFromHtml = convertFromHtml;
});
