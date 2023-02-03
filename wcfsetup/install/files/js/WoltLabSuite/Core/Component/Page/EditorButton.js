define(["require", "exports", "../../Language", "../../Ui/Page/Search"], function (require, exports, Language_1, Search_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    function setupBbcode(editor) {
        editor.sourceElement.addEventListener("bbcode", (evt) => {
            const bbcode = evt.detail;
            if (bbcode === "wsp") {
                evt.preventDefault();
                (0, Search_1.open)((articleId) => {
                    editor.insertText(`[wsp='${articleId}'][/wsp]`);
                });
            }
        });
    }
    function setup(element) {
        element.addEventListener("ckeditor5:configuration", (event) => {
            event.detail.woltlabBbcode.push({
                icon: "file-lines;false",
                name: "wsp",
                label: (0, Language_1.getPhrase)("wcf.editor.button.page"),
            });
        }, { once: true });
        element.addEventListener("ckeditor5:ready", (event) => {
            setupBbcode(event.detail);
        }, { once: true });
    }
    exports.setup = setup;
});
