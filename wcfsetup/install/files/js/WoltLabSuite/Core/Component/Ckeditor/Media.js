define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.uploadMedia = void 0;
    function uploadMedia(element, file, abortController) {
        const data = { abortController, file };
        element.dispatchEvent(new CustomEvent("ckeditor5:drop", {
            detail: data,
        }));
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
