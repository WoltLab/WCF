define(["require", "exports", "../../Language", "../../Ui/Article/Search", "../Ckeditor/Event"], function (require, exports, Language_1, Search_1, Event_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    function setupBbcode(ckeditor) {
        ckeditor.sourceElement.addEventListener("bbcode", (evt) => {
            const bbcode = evt.detail;
            if (bbcode === "wsa") {
                evt.preventDefault();
                (0, Search_1.open)((articleId) => {
                    ckeditor.insertText(`[wsa='${articleId}'][/wsa]`);
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
        (0, Event_1.listenToCkeditor)(element).ready((ckeditor) => {
            setupBbcode(ckeditor);
        });
    }
    exports.setup = setup;
});
