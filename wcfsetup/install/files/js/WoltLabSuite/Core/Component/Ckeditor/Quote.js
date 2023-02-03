define(["require", "exports", "../../StringUtil"], function (require, exports, StringUtil_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    function insertQuote(editor, payload) {
        let { author, content, link } = payload;
        if (payload.isText) {
            content = (0, StringUtil_1.escapeHTML)(content);
        }
        author = (0, StringUtil_1.escapeHTML)(author);
        link = (0, StringUtil_1.escapeHTML)(link);
        editor.insertHtml(`<woltlab-ckeditor-blockquote author="${author}" link="${link}">${content}</woltlab-ckeditor-blockquote>`);
    }
    function setup(element) {
        element.addEventListener("ckeditor5:ready", ({ detail: editor }) => {
            element.addEventListener("ckeditor5:insert-quote", (event) => {
                insertQuote(editor, event.detail);
            });
        }, { once: true });
    }
    exports.setup = setup;
});
