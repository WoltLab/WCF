define(["require", "exports", "WoltLabSuite/Core/FileUtil"], function (require, exports, FileUtil_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.WoltlabCoreFileElement = exports.Thumbnail = void 0;
    class Thumbnail {
        #identifier;
        #link;
        constructor(identifier, link) {
            this.#identifier = identifier;
            this.#link = link;
        }
        get identifier() {
            return this.#identifier;
        }
        get link() {
            return this.#link;
        }
    }
    exports.Thumbnail = Thumbnail;
    class WoltlabCoreFileElement extends HTMLElement {
        #data = undefined;
        #filename = "";
        #fileId = undefined;
        #fileSize = undefined;
        #link = undefined;
        #mimeType = undefined;
        #state = 0 /* State.Initial */;
        #validationError = undefined;
        #thumbnails = [];
        #readyReject;
        #readyResolve;
        #readyPromise;
        constructor() {
            super();
            this.#readyPromise = new Promise((resolve, reject) => {
                this.#readyResolve = resolve;
                this.#readyReject = reject;
            });
        }
        connectedCallback() {
            let wasAlreadyReady = false;
            if (this.#state === 0 /* State.Initial */) {
                wasAlreadyReady = this.#initializeState();
            }
            this.#rebuildElement();
            if (wasAlreadyReady) {
                this.#readyResolve();
            }
        }
        #initializeState() {
            // Files that exist at page load have a valid file id, otherwise a new
            // file element can only be the result of an upload attempt.
            if (this.#fileId === undefined) {
                this.#filename = this.dataset.filename || "unknown.bin";
                delete this.dataset.filename;
                this.#fileSize = parseInt(this.dataset.fileSize || "0");
                delete this.dataset.fileSize;
                this.#mimeType = this.dataset.mimeType || "application/octet-stream";
                delete this.dataset.mimeType;
                const fileId = parseInt(this.getAttribute("file-id") || "0");
                if (fileId) {
                    this.#fileId = fileId;
                }
                else {
                    this.#state = 1 /* State.Uploading */;
                    return false;
                }
            }
            // Initialize the list of thumbnails from the data attribute.
            if (this.dataset.thumbnails) {
                const thumbnails = JSON.parse(this.dataset.thumbnails);
                for (const thumbnail of thumbnails) {
                    this.#thumbnails.push(new Thumbnail(thumbnail.identifier, thumbnail.link));
                }
                delete this.dataset.thumbnails;
            }
            if (this.dataset.metaData) {
                this.#data = JSON.parse(this.dataset.metaData);
                delete this.dataset.metaData;
            }
            this.#link = this.dataset.link;
            delete this.dataset.link;
            this.#state = 3 /* State.Ready */;
            return true;
        }
        #rebuildElement() {
            switch (this.#state) {
                case 1 /* State.Uploading */:
                    this.#replaceWithIcon("spinner");
                    break;
                case 2 /* State.GeneratingThumbnails */:
                    this.#replaceWithIcon("spinner");
                    break;
                case 3 /* State.Ready */:
                    if (this.previewUrl) {
                        this.#replaceWithImage(this.previewUrl);
                    }
                    else {
                        const iconName = this.iconName || "file";
                        this.#replaceWithIcon(iconName);
                    }
                    break;
                case 4 /* State.Failed */:
                    this.#replaceWithIcon("triangle-exclamation");
                    break;
                default:
                    throw new Error("Unreachable", {
                        cause: {
                            state: this.#state,
                        },
                    });
            }
        }
        #replaceWithImage(src) {
            let img = this.querySelector("img");
            if (img === null) {
                this.innerHTML = "";
                img = document.createElement("img");
                img.alt = "";
                this.append(img);
            }
            img.src = src;
            if (this.unbounded) {
                img.removeAttribute("height");
                img.removeAttribute("width");
            }
            else {
                img.height = 64;
                img.width = 64;
            }
        }
        #replaceWithIcon(iconName) {
            let icon = this.querySelector("fa-icon");
            if (icon === null) {
                this.innerHTML = "";
                icon = document.createElement("fa-icon");
                icon.size = 64;
                icon.setIcon(iconName);
                this.append(icon);
            }
            else {
                icon.setIcon(iconName);
            }
            return icon;
        }
        get fileId() {
            return this.#fileId;
        }
        get iconName() {
            if (this.mimeType === undefined) {
                return undefined;
            }
            const fileExtension = (0, FileUtil_1.getExtensionByMimeType)(this.mimeType);
            if (fileExtension === "") {
                return undefined;
            }
            const iconName = (0, FileUtil_1.getIconNameByFilename)(fileExtension);
            if (iconName === "") {
                return undefined;
            }
            return `file-${iconName}`;
        }
        get previewUrl() {
            return this.dataset.previewUrl;
        }
        get unbounded() {
            return this.getAttribute("dimensions") === "unbounded";
        }
        set unbounded(unbounded) {
            if (unbounded) {
                this.setAttribute("dimensions", "unbounded");
            }
            else {
                this.removeAttribute("dimensions");
            }
            this.#rebuildElement();
        }
        get filename() {
            return this.#filename;
        }
        get fileSize() {
            return this.#fileSize;
        }
        get mimeType() {
            return this.#mimeType;
        }
        get data() {
            return this.#data;
        }
        get link() {
            return this.#link;
        }
        isImage() {
            if (this.mimeType === undefined) {
                return false;
            }
            switch (this.mimeType) {
                case "image/gif":
                case "image/jpeg":
                case "image/png":
                case "image/webp":
                    return true;
                default:
                    return false;
            }
        }
        uploadFailed(validationError) {
            if (this.#state !== 1 /* State.Uploading */) {
                return;
            }
            this.#state = 4 /* State.Failed */;
            this.#validationError = validationError;
            this.#rebuildElement();
            this.#readyReject();
        }
        uploadCompleted(fileId, mimeType, link, data, hasThumbnails) {
            if (this.#state === 1 /* State.Uploading */) {
                this.#data = data;
                this.#fileId = fileId;
                this.#link = link;
                this.#mimeType = mimeType;
                this.setAttribute("file-id", fileId.toString());
                if (hasThumbnails) {
                    this.#state = 2 /* State.GeneratingThumbnails */;
                    this.#rebuildElement();
                }
                else {
                    this.#state = 3 /* State.Ready */;
                    this.#rebuildElement();
                    this.#readyResolve();
                }
            }
        }
        setThumbnails(thumbnails) {
            if (this.#state !== 2 /* State.GeneratingThumbnails */) {
                return;
            }
            for (const thumbnail of thumbnails) {
                this.#thumbnails.push(new Thumbnail(thumbnail.identifier, thumbnail.link));
            }
            this.#state = 3 /* State.Ready */;
            this.#rebuildElement();
            this.#readyResolve();
        }
        isFailedUpload() {
            return this.#state === 4 /* State.Failed */;
        }
        set thumbnail(thumbnail) {
            if (!this.#thumbnails.includes(thumbnail)) {
                return;
            }
            this.#replaceWithImage(thumbnail.link);
        }
        get thumbnails() {
            return [...this.#thumbnails];
        }
        get ready() {
            return this.#readyPromise;
        }
        get validationError() {
            return this.#validationError;
        }
    }
    exports.WoltlabCoreFileElement = WoltlabCoreFileElement;
    exports.default = WoltlabCoreFileElement;
    window.customElements.define("woltlab-core-file", WoltlabCoreFileElement);
});
