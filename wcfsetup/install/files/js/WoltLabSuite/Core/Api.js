define(["require", "exports", "tslib", "./Ajax/Status", "./Core", "./Dom/Change/Listener"], function (require, exports, tslib_1, AjaxStatus, Core, Listener_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.InvalidJson = exports.ExpectedJson = exports.StatusNotOk = exports.ConnectionError = exports.ApiError = exports.Api = void 0;
    AjaxStatus = (0, tslib_1.__importStar)(AjaxStatus);
    Core = (0, tslib_1.__importStar)(Core);
    Listener_1 = (0, tslib_1.__importDefault)(Listener_1);
    class Api {
        constructor(actionName, className) {
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
            // Use a local copy to isolate the behavior in case of changes before
            // the request handling has completed.
            const showLoadingIndicator = this._showLoadingIndicator;
            if (showLoadingIndicator) {
                AjaxStatus.show();
            }
            try {
                const response = await fetch(url, init);
                if (!response.ok) {
                    throw new StatusNotOk(response);
                }
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    throw new ExpectedJson(response);
                }
                let json;
                try {
                    json = await response.json();
                }
                catch (e) {
                    throw new InvalidJson(response);
                }
                if (json.forceBackgroundQueuePerform) {
                    void new Promise((resolve_1, reject_1) => { require(["./BackgroundQueue"], resolve_1, reject_1); }).then(tslib_1.__importStar).then((BackgroundQueue) => BackgroundQueue.invoke());
                }
                return json.returnValues;
            }
            catch (error) {
                if (error instanceof ExpectedJson || error instanceof InvalidJson || error instanceof StatusNotOk) {
                    throw error;
                }
                else {
                    // Re-package the error for use in our global "unhandledrejection" handler.
                    throw new ConnectionError(error);
                }
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
    }
    exports.Api = Api;
    class ApiError extends Error {
        constructor(message) {
            super(message);
            this.name = "ApiError";
        }
    }
    exports.ApiError = ApiError;
    class ConnectionError extends ApiError {
        constructor(originalError) {
            let message = "Unknown error";
            if (originalError instanceof Error) {
                message = originalError.message;
            }
            super(message);
            this.name = "ConnectionError";
            this.originalError = originalError;
        }
    }
    exports.ConnectionError = ConnectionError;
    class StatusNotOk extends ApiError {
        constructor(response) {
            super("The API request returned a status code outside of the 200-299 range.");
            this.name = "StatusNotOk";
            this.response = response;
        }
    }
    exports.StatusNotOk = StatusNotOk;
    class ExpectedJson extends ApiError {
        constructor(response) {
            super("The API did not return a JSON response.");
            this.name = "ExpectedJson";
            this.response = response;
        }
    }
    exports.ExpectedJson = ExpectedJson;
    class InvalidJson extends ApiError {
        constructor(response) {
            super("Failed to decode the JSON response from the API.");
            this.name = "InvalidJson";
            this.response = response;
        }
    }
    exports.InvalidJson = InvalidJson;
    async function genericError(error) {
        const html = await getErrorHtml(error);
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
    async function getErrorHtml(error) {
        let details = "";
        let message = "";
        if (error instanceof ConnectionError) {
            message = error.message;
        }
        else {
            if (error instanceof InvalidJson) {
                message = await error.response.text();
            }
            else if (error instanceof ExpectedJson || error instanceof StatusNotOk) {
                let json = undefined;
                try {
                    json = await error.response.json();
                }
                catch (e) {
                    message = await error.response.text();
                }
                if (json && Core.isPlainObject(json) && Object.keys(json).length > 0) {
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
    window.addEventListener("unhandledrejection", (event) => {
        if (event.reason instanceof ApiError) {
            event.preventDefault();
            void genericError(event.reason);
        }
    });
    exports.default = Api;
});
