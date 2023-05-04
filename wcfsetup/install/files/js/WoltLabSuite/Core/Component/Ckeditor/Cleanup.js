define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.normalizeLegacyMessage = void 0;
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
        stripLegacySpacerParagraphs(div);
        element.value = div.innerHTML;
    }
    exports.normalizeLegacyMessage = normalizeLegacyMessage;
});
