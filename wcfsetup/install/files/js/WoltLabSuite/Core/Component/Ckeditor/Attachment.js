define(["require", "exports", "tslib", "../../Event/Handler"], function (require, exports, tslib_1, EventHandler) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setupRemoveAttachment = exports.uploadAttachment = void 0;
    EventHandler = tslib_1.__importStar(EventHandler);
    function uploadAttachment(elementId, file, abortController) {
        const data = { abortController, file };
        EventHandler.fire("com.woltlab.wcf.ckeditor5", `dragAndDrop_${elementId}`, data);
        return new Promise((resolve) => {
            void data.promise.then(({ attachmentId, url }) => {
                resolve({
                    "data-attachment-id": attachmentId.toString(),
                    urls: {
                        default: url,
                    },
                });
            });
        });
    }
    exports.uploadAttachment = uploadAttachment;
    function setupRemoveAttachment(ckeditor) {
        EventHandler.add("com.woltlab.wcf.ckeditor5", `removeEmbeddedAttachment_${ckeditor.sourceElement.id}`, ({ attachmentId }) => {
            ckeditor.removeAll("imageBlock", { attachmentId });
            ckeditor.removeAll("imageInline", { attachmentId });
        });
    }
    exports.setupRemoveAttachment = setupRemoveAttachment;
});
