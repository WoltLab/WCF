/**
 * Cleans up the markup of legacy messages.
 *
 * Messages created in the previous editor used empty paragraphs to create empty
 * lines. In addition, Firefox kept trailing <br> in lines with content, which
 * causes issues with CKEditor.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports", "tslib", "../../Dom/Util"], function (require, exports, tslib_1, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.normalizeLegacyMessage = void 0;
    Util_1 = tslib_1.__importDefault(Util_1);
    function unwrapBr(div) {
        div.querySelectorAll("br").forEach((br) => {
            if (br.previousSibling || br.nextSibling) {
                return;
            }
            let parent = br;
            while ((parent = parent.parentElement) !== null) {
                switch (parent.tagName) {
                    case "B":
                    case "EM":
                    case "I":
                    case "STRONG":
                    case "SUB":
                    case "SUP":
                    case "SPAN":
                    case "U":
                        if (br.previousSibling || br.nextSibling) {
                            return;
                        }
                        parent.insertAdjacentElement("afterend", br);
                        parent.remove();
                        parent = br;
                        break;
                    default:
                        return;
                }
            }
        });
    }
    function removeTrailingBr(div) {
        div.querySelectorAll("br").forEach((br) => {
            if (br.dataset.ckeFiller === "true") {
                return;
            }
            const paragraph = br.closest("p");
            if (paragraph === null) {
                return;
            }
            if (!Util_1.default.isAtNodeEnd(br, paragraph)) {
                return;
            }
            if (paragraph.innerHTML === "<br>") {
                paragraph.remove();
            }
            else {
                br.remove();
            }
        });
    }
    function stripLegacySpacerParagraphs(div) {
        div.querySelectorAll("p").forEach((paragraph) => {
            if (paragraph.childElementCount === 1) {
                const child = paragraph.children[0];
                if (child.tagName === "BR" && child.dataset.ckeFiller !== "true") {
                    if (paragraph.textContent.trim() === "") {
                        paragraph.remove();
                    }
                }
            }
        });
    }
    function normalizeLegacyMessage(element) {
        if (!(element instanceof HTMLTextAreaElement)) {
            throw new TypeError("Expected the element to be a <textarea>.");
        }
        const div = document.createElement("div");
        div.innerHTML = element.value;
        unwrapBr(div);
        removeTrailingBr(div);
        stripLegacySpacerParagraphs(div);
        element.value = div.innerHTML;
    }
    exports.normalizeLegacyMessage = normalizeLegacyMessage;
});
