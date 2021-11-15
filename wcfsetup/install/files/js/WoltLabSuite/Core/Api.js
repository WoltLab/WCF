define(["require", "exports", "tslib", "./Ajax/Status", "./Core", "./Dom/Change/Listener"], function (require, exports, tslib_1, AjaxStatus, Core, Listener_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Api = exports.ErrorCode = void 0;
    AjaxStatus = (0, tslib_1.__importStar)(AjaxStatus);
    Core = (0, tslib_1.__importStar)(Core);
    Listener_1 = (0, tslib_1.__importDefault)(Listener_1);
    var ErrorCode;
    (function (ErrorCode) {
        ErrorCode["CONNECTION_ERROR"] = "connection_error";
        ErrorCode["EXPECTED_JSON"] = "expected_json";
        ErrorCode["INVALID_JSON"] = "invalid_json";
        ErrorCode["STATUS_NOT_OK"] = "status_not_ok";
    })(ErrorCode = exports.ErrorCode || (exports.ErrorCode = {}));
    class Api {
        constructor(actionName, className) {
            this._failure = undefined;
            this._objectIDs = [];
            this._payload = {};
            this._showLoadingIndicator = true;
            this._signal = undefined;
            this.actionName = actionName;
            this.className = className;
        }
        static dboAction(actionName, className) {
            return new Api(actionName, className);
        }
        failure(failure) {
            this._failure = failure;
            return this;
        }
        getAbortController() {
            if (this._signal === undefined) {
                this._signal = new AbortController();
            }
            return this._signal;
        }
        objectIds(objectIds) {
            this._objectIDs = objectIds;
            return this;
        }
        payload(payload) {
            this._payload = payload;
            return this;
        }
        disableLoadingIndicator() {
            this._showLoadingIndicator = false;
            return this;
        }
        async dispatch() {
            const url = window.WSC_API_URL + "index.php?ajax-proxy/&t=" + Core.getXsrfToken();
            const body = {
                actionName: this.actionName,
                className: this.className,
            };
            if (this._objectIDs) {
                body.objectIDs = this._objectIDs;
            }
            if (this._payload) {
                body.parameters = this._payload;
            }
            const init = {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-XSRF-TOKEN": Core.getXsrfToken(),
                },
                body: Core.serialize(body),
                mode: "same-origin",
                credentials: "same-origin",
                cache: "no-store",
                redirect: "error",
            };
            if (this._signal) {
                init.signal = this._signal.signal;
            }
            // Use a local copy to isolte the behavior in case of changes before
            // the request handling has completed.
            const showLoadingIndicator = this._showLoadingIndicator;
            if (showLoadingIndicator) {
                AjaxStatus.show();
            }
            try {
                const response = await fetch(url, init);
                if (!response.ok) {
                    const result = this.handleError(ErrorCode.STATUS_NOT_OK, response);
                    return Promise.reject(result);
                }
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    const result = this.handleError(ErrorCode.EXPECTED_JSON, response);
                    return Promise.reject(result);
                }
                let json;
                try {
                    json = await response.json();
                }
                catch (e) {
                    const result = this.handleError(ErrorCode.INVALID_JSON, response);
                    return Promise.reject(result);
                }
                return Promise.resolve(json.returnValues).then((result) => {
                    if (json.forceBackgroundQueuePerform) {
                        void new Promise((resolve_1, reject_1) => { require(["./BackgroundQueue"], resolve_1, reject_1); }).then(tslib_1.__importStar).then((BackgroundQueue) => BackgroundQueue.invoke());
                    }
                    return result;
                });
            }
            catch (error) {
                const result = {
                    code: ErrorCode.CONNECTION_ERROR,
                    error,
                };
                let suppressError = false;
                if (typeof this._failure === "function") {
                    suppressError = this._failure(result);
                }
                if (!suppressError) {
                    await this.genericError(result);
                }
                return Promise.reject(result);
            }
            finally {
                if (showLoadingIndicator) {
                    AjaxStatus.hide();
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
        }
        async handleError(code, response) {
            const result = { code, response };
            if (!this.suppressError(result)) {
                await this.genericError(result);
            }
            return result;
        }
        suppressError(result) {
            if (typeof this._failure === "function") {
                return this._failure(result);
            }
            return true;
        }
        async genericError(result) {
            const html = await this.getErrorHtml(result);
            if (html !== "") {
                // Load these modules on runtime to avoid circular dependencies.
                const [UiDialog, DomUtil, Language] = await Promise.all([
                    new Promise((resolve_2, reject_2) => { require(["./Ui/Dialog"], resolve_2, reject_2); }).then(tslib_1.__importStar),
                    new Promise((resolve_3, reject_3) => { require(["./Dom/Util"], resolve_3, reject_3); }).then(tslib_1.__importStar),
                    new Promise((resolve_4, reject_4) => { require(["./Language"], resolve_4, reject_4); }).then(tslib_1.__importStar),
                ]);
                UiDialog.openStatic(DomUtil.getUniqueId(), html, {
                    title: Language.get("wcf.global.error.title"),
                });
            }
        }
        async getErrorHtml(result) {
            let details = "";
            let message = "";
            if (result.error) {
                message = result.error.toString();
            }
            else if (result.response) {
                if (result.code === ErrorCode.INVALID_JSON) {
                    message = await result.response.text();
                }
                else {
                    const json = (await result.response.json());
                    if (Core.isPlainObject(json) && Object.keys(json).length > 0) {
                        if (json.returnValues && json.returnValues.description) {
                            details += `<br><p>Description:</p><p>${json.returnValues.description}</p>`;
                        }
                        if (json.file && json.line) {
                            details += `<br><p>File:</p><p>${json.file} in line ${json.line}</p>`;
                        }
                        if (json.stacktrace) {
                            details += `<br><p>Stacktrace:</p><p>${json.stacktrace}</p>`;
                        }
                        else if (json.exceptionID) {
                            details += `<br><p>Exception ID: <code>${json.exceptionID}</code></p>`;
                        }
                        message = json.message;
                        json.previous.forEach((previous) => {
                            details += `<hr><p>${previous.message}</p>`;
                            details += `<br><p>Stacktrace</p><p>${previous.stacktrace}</p>`;
                        });
                    }
                }
            }
            if (!message || message === "undefined") {
                if (!window.ENABLE_DEBUG_MODE) {
                    return "";
                }
                message = "fetch() failed without a response body. Check your browser console.";
            }
            return `<div class="ajaxDebugMessage"><p>${message}</p>${details}</div>`;
        }
    }
    exports.Api = Api;
    exports.default = Api;
});
