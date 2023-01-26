define(["require", "exports", "tslib", "../../Event/Handler"], function (require, exports, tslib_1, EventHandler) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.uploadMedia = void 0;
    EventHandler = tslib_1.__importStar(EventHandler);
    function uploadMedia(elementId, file, abortController) {
        const data = { abortController, file };
        EventHandler.fire("com.woltlab.wcf.ckeditor5", `dragAndDrop_${elementId}`, data);
        // The media system works differently compared to the
        // attachments, because uploading a file will offer
        // the user to insert the content in different formats.
        //
        // Rejecting the upload promise will cause CKEditor to
        // stop caring about the file so that we regain control.
        return Promise.reject();
    }
    exports.uploadMedia = uploadMedia;
});
