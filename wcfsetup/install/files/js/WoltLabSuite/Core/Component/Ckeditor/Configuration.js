define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.createConfiguration = void 0;
    function createConfiguration(features) {
        const toolbar = [
            "heading",
            "|",
            "bold",
            "italic",
            {
                label: "woltlabToolbarGroup_format",
                items: ["underline", "strikethrough", "subscript", "superscript", "code"],
            },
            "|",
            {
                label: "woltlabToolbarGroup_list",
                items: ["bulletedList", "numberedList"],
            },
            "alignment",
        ];
        if (features.url) {
            toolbar.push("link");
        }
        if (features.image) {
            ("insertImage");
        }
        const blocks = ["insertTable", "blockQuote", "codeBlock"];
        if (features.spoiler) {
            blocks.push("spoiler");
        }
        if (features.html) {
            blocks.push("htmlEmbed");
        }
        if (features.media) {
            blocks.push("woltlabBbcode_media");
        }
        toolbar.push({
            label: "TODO: Insert block",
            icon: "plus",
            items: blocks,
        });
        const config = {
            // The typings for the toolbar are outdated.
            toolbar: toolbar,
        };
        return config;
    }
    exports.createConfiguration = createConfiguration;
});
