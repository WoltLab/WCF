/**
 * Error types and a global error handler for the `Promise`-based `DboAction` class.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Ajax/Error
 * @since 5.5
 */
define(["require", "exports", "tslib", "../Core"], function (require, exports, tslib_1, Core) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.registerGlobalRejectionHandler = exports.InvalidJson = exports.ExpectedJson = exports.StatusNotOk = exports.ConnectionError = exports.ApiError = void 0;
    Core = (0, tslib_1.__importStar)(Core);
    async function genericError(error) {
        const html = await getErrorHtml(error);
        if (html !== "") {
            // Load these modules on runtime to avoid circular dependencies.
            const [UiDialog, DomUtil, Language] = await Promise.all([
                new Promise((resolve_1, reject_1) => { require(["../Ui/Dialog"], resolve_1, reject_1); }).then(tslib_1.__importStar),
                new Promise((resolve_2, reject_2) => { require(["../Dom/Util"], resolve_2, reject_2); }).then(tslib_1.__importStar),
                new Promise((resolve_3, reject_3) => { require(["../Language"], resolve_3, reject_3); }).then(tslib_1.__importStar),
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
                message = await error.response.clone().text();
            }
            else if (error instanceof ExpectedJson || error instanceof StatusNotOk) {
                let json = undefined;
                try {
                    json = await error.response.clone().json();
                }
                catch (e) {
                    message = await error.response.clone().text();
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
    class ApiError extends Error {
        constructor() {
            super(...arguments);
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
    let hasGlobalRejectionHandler = false;
    function registerGlobalRejectionHandler() {
        if (hasGlobalRejectionHandler) {
            return;
        }
        window.addEventListener("unhandledrejection", (event) => {
            if (event.reason instanceof ApiError) {
                event.preventDefault();
                void genericError(event.reason);
            }
        });
        hasGlobalRejectionHandler = true;
    }
    exports.registerGlobalRejectionHandler = registerGlobalRejectionHandler;
});
