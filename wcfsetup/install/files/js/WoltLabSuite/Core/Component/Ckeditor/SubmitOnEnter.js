define(["require", "exports", "./Event"], function (require, exports, Event_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    function setup(editor, ckeditor) {
        editor.editing.view.document.on("enter", (evt, data) => {
            // Shift+Enter is allowed to create line breaks.
            if (data.isSoft) {
                return;
            }
            data.preventDefault();
            evt.stop();
            const html = ckeditor.getHtml();
            if (html !== "") {
                (0, Event_1.dispatchToCkeditor)(ckeditor.sourceElement).submitOnEnter({
                    ckeditor,
                    html,
                });
            }
        }, { priority: "high" });
    }
    exports.setup = setup;
});
