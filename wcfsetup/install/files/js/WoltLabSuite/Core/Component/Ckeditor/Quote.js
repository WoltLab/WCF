define(["require", "exports", "../../StringUtil"], function (require, exports, StringUtil_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    function insertQuote(editor, content, contentIsText, author, link) {
        if (contentIsText) {
            content = (0, StringUtil_1.escapeHTML)(content);
        }
        author = (0, StringUtil_1.escapeHTML)(author);
        link = (0, StringUtil_1.escapeHTML)(link);
        editor.insertHtml(`<woltlab-ckeditor-blockquote author="${author}" link="${link}">${content}</woltlab-ckeditor-blockquote>`);
    }
    function setup(editor) {
        editor.sourceElement.addEventListener("ckeditor5:insert-quote", (event) => {
            const { author, content, isText, link } = event.detail;
            insertQuote(editor, content, isText, author, link);
        });
    }
    exports.setup = setup;
});
