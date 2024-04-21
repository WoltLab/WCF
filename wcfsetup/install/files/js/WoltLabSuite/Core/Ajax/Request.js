/**
 * Versatile AJAX request handling.
 *
 * In case you want to issue JSONP requests, please use `AjaxJsonp` instead.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  AjaxRequest (alias)
 * @module  WoltLabSuite/Core/Ajax/Request
 */
define(["require", "exports", "tslib", "./Status", "../Core", "../Dom/Change/Listener", "../Dom/Util", "../Language"], function (require, exports, tslib_1, AjaxStatus, Core, Listener_1, Util_1, Language) {
    "use strict";
    AjaxStatus = tslib_1.__importStar(AjaxStatus);
    Core = tslib_1.__importStar(Core);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    Language = tslib_1.__importStar(Language);
    let _didInit = false;
    let _ignoreAllErrors = false;
    /**
     * @constructor
     */
    class AjaxRequest {
        constructor(options) {
            this._options = Core.extend({
                data: {},
                contentType: "application/x-www-form-urlencoded; charset=UTF-8",
                responseType: "application/json",
                type: "POST",
                url: "",
                withCredentials: false,
                // behavior
                autoAbort: false,
                ignoreError: false,
                pinData: false,
                silent: false,
                includeRequestedWith: true,
                // callbacks
                failure: null,
                finalize: null,
                success: null,
                progress: null,
                uploadProgress: null,
                callbackObject: null,
            }, options);
            if (typeof options.callbackObject === "object") {
                this._options.callbackObject = options.callbackObject;
            }
            this._options.url = Core.convertLegacyUrl(this._options.url);
            if (this._options.url.indexOf("index.php") === 0) {
                this._options.url = window.WSC_API_URL + this._options.url;
            }
            if (this._options.url.indexOf(window.WSC_API_URL) === 0) {
                this._options.includeRequestedWith = true;
                // always include credentials when querying the very own server
                this._options.withCredentials = true;
            }
            if (this._options.pinData) {
                this._data = this._options.data;
            }
            if (this._options.callbackObject) {
                if (typeof this._options.callbackObject._ajaxFailure === "function") {
                    this._options.failure = this._options.callbackObject._ajaxFailure.bind(this._options.callbackObject);
                }
                if (typeof this._options.callbackObject._ajaxFinalize === "function") {
                    this._options.finalize = this._options.callbackObject._ajaxFinalize.bind(this._options.callbackObject);
                }
                if (typeof this._options.callbackObject._ajaxSuccess === "function") {
                    this._options.success = this._options.callbackObject._ajaxSuccess.bind(this._options.callbackObject);
                }
                if (typeof this._options.callbackObject._ajaxProgress === "function") {
                    this._options.progress = this._options.callbackObject._ajaxProgress.bind(this._options.callbackObject);
                }
                if (typeof this._options.callbackObject._ajaxUploadProgress === "function") {
                    this._options.uploadProgress = this._options.callbackObject._ajaxUploadProgress.bind(this._options.callbackObject);
                }
            }
            if (!_didInit) {
                _didInit = true;
                window.addEventListener("beforeunload", () => (_ignoreAllErrors = true));
            }
        }
        /**
         * Dispatches a request, optionally aborting a currently active request.
         */
        sendRequest(abortPrevious) {
            if (abortPrevious || this._options.autoAbort) {
                this.abortPrevious();
            }
            if (!this._options.silent) {
                AjaxStatus.show();
            }
            this._xhr = new XMLHttpRequest();
            this._xhr.open(this._options.type, this._options.url, true);
            if (this._options.contentType) {
                this._xhr.setRequestHeader("Content-Type", this._options.contentType);
            }
            if (this._options.withCredentials || this._options.includeRequestedWith) {
                this._xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            }
            if (this._options.withCredentials) {
                this._xhr.withCredentials = true;
            }
            const options = Core.clone(this._options);
            // Use a local variable in all callbacks, because `this._xhr` can be overwritten by
            // subsequent requests while a request is still in-flight.
            const xhr = this._xhr;
            xhr.onload = () => {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if ((xhr.status >= 200 && xhr.status < 300) || xhr.status === 304) {
                        if (xhr.status === 204) {
                            // HTTP 204 does not contain a body, the `content-type` is undefined.
                            this._success(xhr, options);
                        }
                        else {
                            if (options.responseType && this.getContentType(xhr) !== options.responseType) {
                                // request succeeded but invalid response type
                                this._failure(xhr, options);
                            }
                            else {
                                this._success(xhr, options);
                            }
                        }
                    }
                    else {
                        this._failure(xhr, options);
                    }
                }
            };
            xhr.onerror = () => {
                this._failure(xhr, options);
            };
            if (this._options.progress) {
                xhr.onprogress = this._options.progress;
            }
            if (this._options.uploadProgress) {
                xhr.upload.onprogress = this._options.uploadProgress;
            }
            if (this._options.type === "POST") {
                let data = this._options.data;
                if (typeof data === "object" && Core.getType(data) !== "FormData") {
                    data = Core.serialize(data);
                }
                xhr.send(data);
            }
            else {
                xhr.send();
            }
        }
        /**
         * Aborts a previous request.
         */
        abortPrevious() {
            if (this._xhr === undefined) {
                return;
            }
            this._xhr.abort();
            this._xhr = undefined;
            if (!this._options.silent) {
                AjaxStatus.hide();
            }
        }
        /**
         * Sets a specific option.
         */
        setOption(key, value) {
            this._options[key] = value;
        }
        /**
         * Returns an option by key or undefined.
         */
        // eslint-disable-next-line @typescript-eslint/no-redundant-type-constituents
        getOption(key) {
            if (Object.prototype.hasOwnProperty.call(this._options, key)) {
                return this._options[key];
            }
            return null;
        }
        /**
         * Sets request data while honoring pinned data from setup callback.
         */
        setData(data) {
            if (this._data !== null && Core.getType(data) !== "FormData") {
                data = Core.extend(this._data, data);
            }
            this._options.data = data;
        }
        /**
         * Handles a successful request.
         */
        _success(xhr, options) {
            if (!options.silent) {
                AjaxStatus.hide();
            }
            if (typeof options.success === "function") {
                let data = null;
                if (this.getContentType(xhr) === "application/json") {
                    try {
                        data = JSON.parse(xhr.responseText);
                    }
                    catch (e) {
                        // invalid JSON
                        this._failure(xhr, options);
                        return;
                    }
                    // trim HTML before processing, see http://jquery.com/upgrade-guide/1.9/#jquery-htmlstring-versus-jquery-selectorstring
                    if (data && data.returnValues && data.returnValues.template !== undefined) {
                        data.returnValues.template = data.returnValues.template.trim();
                    }
                    // force-invoke the background queue
                    if (data && data.forceBackgroundQueuePerform) {
                        void new Promise((resolve_1, reject_1) => { require(["../BackgroundQueue"], resolve_1, reject_1); }).then(tslib_1.__importStar).then((backgroundQueue) => backgroundQueue.invoke());
                    }
                }
                options.success(data || {}, xhr.responseText, xhr, options.data);
            }
            this._finalize(xhr, options);
        }
        /**
         * Handles failed requests, this can be both a successful request with
         * a non-success status code or an entirely failed request.
         */
        _failure(xhr, options) {
            if (_ignoreAllErrors) {
                return;
            }
            if (!options.silent) {
                AjaxStatus.hide();
            }
            let data = null;
            try {
                data = JSON.parse(xhr.responseText);
            }
            catch (e) {
                // Ignore JSON parsing failure.
            }
            let showError = true;
            if (typeof options.failure === "function") {
                // undefined might be returned by legacy callbacks and must be treated as 'true'.
                const result = options.failure(data || {}, xhr.responseText || "", xhr, options.data);
                showError = result !== false;
            }
            if (options.ignoreError !== true && showError) {
                const html = this.getErrorHtml(data, xhr);
                if (html) {
                    void new Promise((resolve_2, reject_2) => { require(["../Ui/Dialog"], resolve_2, reject_2); }).then(tslib_1.__importStar).then((UiDialog) => {
                        UiDialog.openStatic(Util_1.default.getUniqueId(), html, {
                            title: Language.get("wcf.global.error.title"),
                        });
                    });
                }
            }
            this._finalize(xhr, options);
        }
        /**
         * Returns the inner HTML for an error/exception display.
         */
        getErrorHtml(data, xhr) {
            let details = "";
            let message;
            if (data !== null && Object.keys(data).length > 0) {
                if (data.returnValues && data.returnValues.description) {
                    details += `<br><p>Description:</p><p>${data.returnValues.description}</p>`;
                }
                if (data.file && data.line) {
                    details += `<br><p>File:</p><p>${data.file} in line ${data.line}</p>`;
                }
                if (data.stacktrace) {
                    details += `<br><p>Stacktrace:</p><p>${data.stacktrace}</p>`;
                }
                else if (data.exceptionID) {
                    details += `<br><p>Exception ID: <code>${data.exceptionID}</code></p>`;
                }
                message = data.message;
                data.previous.forEach((previous) => {
                    details += `<hr><p>${previous.message}</p>`;
                    details += `<br><p>Stacktrace</p><p>${previous.stacktrace}</p>`;
                });
            }
            else {
                message = xhr.responseText;
            }
            if (!message || message === "undefined") {
                if (!window.ENABLE_DEBUG_MODE) {
                    return null;
                }
                message = "XMLHttpRequest failed without a responseText. Check your browser console.";
            }
            return `<div class="ajaxDebugMessage"><p>${message}</p>${details}</div>`;
        }
        /**
         * Finalizes a request.
         */
        _finalize(xhr, options) {
            if (typeof options.finalize === "function") {
                options.finalize(xhr);
            }
            if (this._xhr === xhr) {
                this._xhr = undefined;
            }
            Listener_1.default.trigger();
            // fix anchor tags generated through WCF::getAnchor()
            document.querySelectorAll('a[href*="#"]').forEach((link) => {
                let href = link.href;
                if (href.indexOf("AJAXProxy") !== -1 || href.indexOf("ajax-proxy") !== -1) {
                    href = href.substr(href.indexOf("#"));
                    link.href = document.location.toString().replace(/#.*/, "") + href;
                }
            });
        }
        getContentType(xhr) {
            const contentType = xhr.getResponseHeader("content-type");
            if (contentType === null) {
                return null;
            }
            return contentType.split(";", 1)[0].trim();
        }
    }
    Core.enableLegacyInheritance(AjaxRequest);
    return AjaxRequest;
});
