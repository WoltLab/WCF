define(["require", "exports"], function (require, exports) {
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
        #filename = "";
        #fileId = undefined;
        #state = 0 /* State.Initial */;
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
            if (this.#state === 0 /* State.Initial */) {
                this.#initializeState();
            }
            this.#rebuildElement();
        }
        #initializeState() {
            // Files that exist at page load have a valid file id, otherwise a new
            // file element can only be the result of an upload attempt.
            if (this.#fileId === undefined) {
                this.#filename = this.dataset.filename || "";
                delete this.dataset.filename;
                const fileId = parseInt(this.getAttribute("file-id") || "0");
                if (fileId) {
                    this.#fileId = fileId;
                }
                else {
                    this.#state = 1 /* State.Uploading */;
                    return;
                }
            }
            // Initialize the list of thumbnails from the data attribute.
            if (this.dataset.thumbnails) {
                const thumbnails = JSON.parse(this.dataset.thumbnails);
                for (const thumbnail of thumbnails) {
                    this.#thumbnails.push(new Thumbnail(thumbnail.identifier, thumbnail.link));
                }
            }
            this.#state = 3 /* State.Ready */;
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
                    this.#replaceWithIcon("times");
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
            return this.dataset.iconName;
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
        uploadFailed() {
            if (this.#state !== 1 /* State.Uploading */) {
                return;
            }
            this.#state = 4 /* State.Failed */;
            this.#rebuildElement();
            this.#readyReject();
        }
        uploadCompleted(fileId, hasThumbnails) {
            if (this.#state === 1 /* State.Uploading */) {
                this.#fileId = fileId;
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
        get thumbnails() {
            return [...this.#thumbnails];
        }
        get ready() {
            return this.#readyPromise;
        }
    }
    exports.WoltlabCoreFileElement = WoltlabCoreFileElement;
    exports.default = WoltlabCoreFileElement;
    window.customElements.define("woltlab-core-file", WoltlabCoreFileElement);
});
