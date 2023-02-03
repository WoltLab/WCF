define(["require", "exports", "../../Language", "../../Ui/Article/Search"], function (require, exports, Language_1, Search_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    function setupBbcode(editor) {
        editor.sourceElement.addEventListener("bbcode", (evt) => {
            const bbcode = evt.detail;
            if (bbcode === "wsa") {
                evt.preventDefault();
                (0, Search_1.open)((articleId) => {
                    editor.insertText(`[wsa='${articleId}'][/wsa]`);
                });
            }
        });
    }
    function setup(element) {
        element.addEventListener("ckeditor5:configuration", (event) => {
            const { configuration } = event.detail;
            configuration.woltlabBbcode.push({
                icon: "file-word;false",
                name: "wsa",
                label: (0, Language_1.getPhrase)("wcf.editor.button.article"),
            });
        }, { once: true });
        element.addEventListener("ckeditor5:ready", (event) => {
            setupBbcode(event.detail);
        }, { once: true });
    }
    exports.setup = setup;
});
