/**
 * Provides a strongly typed event interface for CKEditor on top of native DOM
 * events to prevent memory leaks.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.listenToCkeditor = exports.dispatchToCkeditor = void 0;
    class EventDispatcher {
        #element;
        constructor(element) {
            this.#element = element;
        }
        collectMetaData(payload) {
            this.#element.dispatchEvent(new CustomEvent("ckeditor5:collect-meta-data" /* EventNames.CollectMetaData */, {
                detail: payload,
            }));
        }
        changeData() {
            this.#element.dispatchEvent(new CustomEvent("ckeditor5:change-data" /* EventNames.ChangeData */));
        }
        destroy() {
            this.#element.dispatchEvent(new CustomEvent("ckeditor5:destroy" /* EventNames.Destroy */));
        }
        discardRecoveredData() {
            this.#element.dispatchEvent(new CustomEvent("ckeditor5:discard-recovered-data" /* EventNames.DiscardRecoveredData */));
        }
        autosave(payload) {
            this.#element.dispatchEvent(new CustomEvent("ckeditor5:autosave" /* EventNames.Autosave */, {
                detail: payload,
            }));
        }
        insertAttachment(payload) {
            this.#element.dispatchEvent(new CustomEvent("ckeditor5:insert-attachment" /* EventNames.InsertAttachment */, {
                detail: payload,
            }));
        }
        insertQuote(payload) {
            this.#element.dispatchEvent(new CustomEvent("ckeditor5:insert-quote" /* EventNames.InsertQuote */, {
                detail: payload,
            }));
        }
        ready(payload) {
            this.#element.dispatchEvent(new CustomEvent("ckeditor5:ready" /* EventNames.Ready */, {
                detail: payload,
            }));
        }
        removeAttachment(payload) {
            this.#element.dispatchEvent(new CustomEvent("ckeditor5:remove-attachment" /* EventNames.RemoveAttachment */, {
                detail: payload,
            }));
        }
        reset(payload) {
            this.#element.dispatchEvent(new CustomEvent("ckeditor5:reset" /* EventNames.Reset */, {
                detail: payload,
            }));
        }
        setupConfiguration(payload) {
            this.#element.dispatchEvent(new CustomEvent("ckeditor5:setup-configuration" /* EventNames.SetupConfiguration */, {
                detail: payload,
            }));
        }
        setupFeatures(payload) {
            this.#element.dispatchEvent(new CustomEvent("ckeditor5:setup-features" /* EventNames.SetupFeatures */, {
                detail: payload,
            }));
        }
        submitOnEnter(payload) {
            this.#element.dispatchEvent(new CustomEvent("ckeditor5:submit-on-enter" /* EventNames.SubmitOnEnter */, {
                detail: payload,
            }));
        }
        uploadAttachment(payload) {
            this.#element.dispatchEvent(new CustomEvent("ckeditor5:upload-attachment" /* EventNames.UploadAttachment */, {
                detail: payload,
            }));
        }
        uploadMedia(payload) {
            this.#element.dispatchEvent(new CustomEvent("ckeditor5:upload-media" /* EventNames.UploadMedia */, {
                detail: payload,
            }));
        }
    }
    class EventListener {
        #element;
        constructor(element) {
            this.#element = element;
        }
        bbcode(callback) {
            this.#element.addEventListener("ckeditor5:bbcode" /* EventNames.Bbcode */, (event) => {
                const result = callback(event.detail);
                if (result === true) {
                    event.preventDefault();
                }
                else if (result !== false) {
                    throw new Error("An event listener for the bbcode event did not return a boolean to indicate if the BBCode is handled.");
                }
            });
            return this;
        }
        changeData(callback) {
            this.#element.addEventListener("ckeditor5:change-data" /* EventNames.ChangeData */, () => {
                callback();
            });
            return this;
        }
        collectMetaData(callback) {
            this.#element.addEventListener("ckeditor5:collect-meta-data" /* EventNames.CollectMetaData */, (event) => {
                callback(event.detail);
            });
            return this;
        }
        destroy(callback) {
            this.#element.addEventListener("ckeditor5:destroy" /* EventNames.Destroy */, () => {
                callback();
            });
            return this;
        }
        discardRecoveredData(callback) {
            this.#element.addEventListener("ckeditor5:discard-recovered-data" /* EventNames.DiscardRecoveredData */, () => {
                callback();
            }, { once: true });
            return this;
        }
        insertAttachment(callback) {
            this.#element.addEventListener("ckeditor5:insert-attachment" /* EventNames.InsertAttachment */, (event) => {
                callback(event.detail);
            });
            return this;
        }
        insertQuote(callback) {
            this.#element.addEventListener("ckeditor5:insert-quote" /* EventNames.InsertQuote */, (event) => {
                callback(event.detail);
            });
            return this;
        }
        ready(callback) {
            this.#element.addEventListener("ckeditor5:ready" /* EventNames.Ready */, (event) => {
                callback(event.detail);
            }, { once: true });
            return this;
        }
        removeAttachment(callback) {
            this.#element.addEventListener("ckeditor5:remove-attachment" /* EventNames.RemoveAttachment */, (event) => {
                callback(event.detail);
            });
            return this;
        }
        reset(callback) {
            this.#element.addEventListener("ckeditor5:reset" /* EventNames.Reset */, (event) => {
                callback(event.detail);
            });
            return this;
        }
        setupConfiguration(callback) {
            this.#element.addEventListener("ckeditor5:setup-configuration" /* EventNames.SetupConfiguration */, (event) => {
                callback(event.detail);
            }, { once: true });
            return this;
        }
        setupFeatures(callback) {
            this.#element.addEventListener("ckeditor5:setup-features" /* EventNames.SetupFeatures */, (event) => {
                callback(event.detail);
            }, { once: true });
            return this;
        }
        submitOnEnter(callback) {
            this.#element.addEventListener("ckeditor5:submit-on-enter" /* EventNames.SubmitOnEnter */, (event) => {
                callback(event.detail);
            });
            return this;
        }
        uploadAttachment(callback) {
            this.#element.addEventListener("ckeditor5:upload-attachment" /* EventNames.UploadAttachment */, (event) => {
                callback(event.detail);
            });
            return this;
        }
        uploadMedia(callback) {
            this.#element.addEventListener("ckeditor5:upload-media" /* EventNames.UploadMedia */, (event) => {
                callback(event.detail);
            });
            return this;
        }
        autosave(callback) {
            this.#element.addEventListener("ckeditor5:autosave" /* EventNames.Autosave */, (event) => {
                callback(event.detail);
            });
            return this;
        }
    }
    function dispatchToCkeditor(element) {
        return new EventDispatcher(element);
    }
    exports.dispatchToCkeditor = dispatchToCkeditor;
    function listenToCkeditor(element) {
        return new EventListener(element);
    }
    exports.listenToCkeditor = listenToCkeditor;
});
