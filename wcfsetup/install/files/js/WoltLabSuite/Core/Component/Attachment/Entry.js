define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.AttachmentEntry = void 0;
    class AttachmentEntry {
        #attachmentId;
        #name;
        constructor(attachmentId, name) {
            this.#attachmentId = attachmentId;
            this.#name = name;
        }
    }
    exports.AttachmentEntry = AttachmentEntry;
    exports.default = AttachmentEntry;
});
