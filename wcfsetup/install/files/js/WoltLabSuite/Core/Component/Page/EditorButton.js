define(["require", "exports", "../../Language", "../../Ui/Page/Search", "../Ckeditor/Event"], function (require, exports, Language_1, Search_1, Event_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    function setupBbcode(ckeditor) {
        ckeditor.sourceElement.addEventListener("bbcode", (evt) => {
            const bbcode = evt.detail;
            if (bbcode === "wsp") {
                evt.preventDefault();
                (0, Search_1.open)((articleId) => {
                    ckeditor.insertText(`[wsp='${articleId}'][/wsp]`);
                });
            }
        });
    }
    function setup(element) {
        (0, Event_1.listenToCkeditor)(element).configuration(({ configuration }) => {
            configuration.woltlabBbcode.push({
                icon: "file-lines;false",
                name: "wsp",
                label: (0, Language_1.getPhrase)("wcf.editor.button.page"),
            });
        });
        (0, Event_1.listenToCkeditor)(element).ready((ckeditor) => {
            setupBbcode(ckeditor);
        });
    }
    exports.setup = setup;
});
