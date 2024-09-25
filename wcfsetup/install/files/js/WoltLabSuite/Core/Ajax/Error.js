/**
 * Error types and a global error handler for the `Promise`-based `DboAction` class.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.5
 */
define(["require", "exports", "tslib", "../Component/Dialog", "../Core", "../Language", "../StringUtil"], function (require, exports, tslib_1, Dialog_1, Core, Language, StringUtil_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.InvalidJson = exports.ExpectedJson = exports.StatusNotOk = exports.ConnectionError = exports.ApiError = void 0;
    exports.registerGlobalRejectionHandler = registerGlobalRejectionHandler;
    Core = tslib_1.__importStar(Core);
    Language = tslib_1.__importStar(Language);
    async function genericError(error) {
        const html = await getErrorHtml(error);
        if (html instanceof HTMLIFrameElement) {
            const dialog = (0, Dialog_1.dialogFactory)().fromHtml(`<div class="dialog__iframeContainer">${html.outerHTML}</div>`).asAlert();
            dialog.show(Language.get("wcf.global.error.title"));
            dialog.querySelector("dialog").classList.add("dialog--iframe");
        }
        else if (html !== "") {
            const dialog = (0, Dialog_1.dialogFactory)().fromHtml(html).asAlert();
            dialog.show(Language.get("wcf.global.error.title"));
        }
    }
    async function getErrorHtml(error) {
        let details = "";
        let message = "";
        if (error instanceof ConnectionError) {
            // `fetch()` will yield a `TypeError` for network errors and CORS violations.
            if (error.originalError instanceof TypeError) {
                message = Language.get("wcf.global.error.ajax.network", { message: error.message });
            }
            else {
                message = error.message;
            }
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
                    if (json.exception) {
                        details += `<br>Exception: <div style="white-space: pre;">${(0, StringUtil_1.escapeHTML)(json.exception)}</div>`;
                    }
                    else if (json.stacktrace) {
                        details += `<br><p>Stacktrace:</p><p>${json.stacktrace}</p>`;
                    }
                    else if (json.exceptionID) {
                        details += `<br><p>Exception ID: <code>${json.exceptionID}</code></p>`;
                    }
                    message = json.message;
                    if (json.previous) {
                        json.previous.forEach((previous) => {
                            details += `<hr><p>${previous.message}</p>`;
                            details += `<br><p>Stacktrace</p><p>${previous.stacktrace}</p>`;
                        });
                    }
                }
                else if (json === undefined) {
                    // The content is possibly HTML, use an iframe for rendering.
                    const iframe = document.createElement("iframe");
                    iframe.classList.add("dialog__iframe");
                    iframe.srcdoc = message;
                    return iframe;
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
        name = "ApiError";
    }
    exports.ApiError = ApiError;
    class ConnectionError extends ApiError {
        originalError;
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
        response;
        constructor(response) {
            super("The API request returned a status code outside of the 200-299 range.");
            this.name = "StatusNotOk";
            this.response = response;
        }
    }
    exports.StatusNotOk = StatusNotOk;
    class ExpectedJson extends ApiError {
        response;
        constructor(response) {
            super("The API did not return a JSON response.");
            this.name = "ExpectedJson";
            this.response = response;
        }
    }
    exports.ExpectedJson = ExpectedJson;
    class InvalidJson extends ApiError {
        response;
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
});
