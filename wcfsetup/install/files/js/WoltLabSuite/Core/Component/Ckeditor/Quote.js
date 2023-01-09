define(["require", "exports", "tslib", "../../Event/Handler", "../../StringUtil"], function (require, exports, tslib_1, EventHandler, StringUtil_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    EventHandler = tslib_1.__importStar(EventHandler);
    function insertQuote(editor, content, contentIsText, author, link) {
        if (contentIsText) {
            content = (0, StringUtil_1.escapeHTML)(content);
        }
        author = (0, StringUtil_1.escapeHTML)(author);
        link = (0, StringUtil_1.escapeHTML)(link);
        editor.insertHtml(`<woltlab-ckeditor-blockquote author="${author}" link="${link}">${content}</woltlab-ckeditor-blockquote>`);
    }
    function setup(editor) {
        EventHandler.add("com.woltlab.wcf.ckeditor5", `insertQuote_${editor.sourceElement.id}`, (data) => {
            const { author, content, isText, link } = data;
            insertQuote(editor, content, isText, author, link);
        });
    }
    exports.setup = setup;
});
