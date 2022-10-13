define(["require", "exports", "tslib", "../../../Media/Manager/Editor"], function (require, exports, tslib_1, Editor_1) {
    "use strict";
    Editor_1 = tslib_1.__importDefault(Editor_1);
    class AcpUiCodeMirrorMedia {
        element;
        constructor(elementId) {
            this.element = document.getElementById(elementId);
            const button = document.getElementById(`codemirror-${elementId}-media`);
            button.classList.add(button.id);
            new Editor_1.default({
                buttonClass: button.id,
                callbackInsert: (media, insertType, thumbnailSize) => this.insert(media, insertType, thumbnailSize),
            });
        }
        insert(mediaList, insertType, thumbnailSize) {
            switch (insertType) {
                case "separate" /* MediaInsertType.Separate */: {
                    let sizeArgument = "";
                    if (thumbnailSize) {
                        sizeArgument = ` size="${thumbnailSize}"`;
                    }
                    const content = Array.from(mediaList.values())
                        .map((item) => `{{ media="${item.mediaID}"${sizeArgument} }}`)
                        .join("");
                    this.element.codemirror.replaceSelection(content);
                }
            }
        }
    }
    return AcpUiCodeMirrorMedia;
});
