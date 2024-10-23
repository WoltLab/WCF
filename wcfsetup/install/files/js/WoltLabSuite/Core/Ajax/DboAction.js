/**
 * Dispatch requests to `wcf\\data\\DatabaseObjectAction` actions with a
 * `Promise`-based API and full IDE support.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.5
 */
define(["require", "exports", "tslib", "./Error", "./Status", "../Core"], function (require, exports, tslib_1, Error_1, AjaxStatus, Core) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.DboAction = void 0;
    exports.handleValidationErrors = handleValidationErrors;
    AjaxStatus = tslib_1.__importStar(AjaxStatus);
    Core = tslib_1.__importStar(Core);
    let ignoreConnectionErrors = undefined;
    class DboAction {
        #actionName;
        #className;
        #objectIDs = [];
        #payload = {};
        #showLoadingIndicator = true;
        #signal;
        constructor(actionName, className) {
            this.#actionName = actionName;
            this.#className = className;
        }
        static prepare(actionName, className) {
            if (ignoreConnectionErrors === undefined) {
                ignoreConnectionErrors = false;
                window.addEventListener("beforeunload", () => {
                    ignoreConnectionErrors = true;
                });
            }
            return new DboAction(actionName, className);
        }
        getAbortController() {
            if (this.#signal === undefined) {
                this.#signal = new AbortController();
            }
            return this.#signal;
        }
        objectIds(objectIds) {
            this.#objectIDs = objectIds;
            return this;
        }
        payload(payload) {
            this.#payload = payload;
            return this;
        }
        disableLoadingIndicator() {
            this.#showLoadingIndicator = false;
            return this;
        }
        async dispatch() {
            (0, Error_1.registerGlobalRejectionHandler)();
            const url = window.WSC_API_URL + "index.php?ajax-proxy/&t=" + Core.getXsrfToken();
            const body = {
                actionName: this.#actionName,
                className: this.#className,
            };
            if (this.#objectIDs) {
                body.objectIDs = this.#objectIDs;
            }
            if (this.#payload) {
                body.parameters = this.#payload;
            }
            const init = {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-XSRF-TOKEN": Core.getXsrfToken(),
                    Accept: "application/json",
                },
                body: Core.serialize(body),
                mode: "same-origin",
                credentials: "same-origin",
                cache: "no-store",
                redirect: "error",
            };
            if (this.#signal) {
                init.signal = this.#signal.signal;
            }
            // Use a local copy to isolate the behavior in case of changes before
            // the request handling has completed.
            const showLoadingIndicator = this.#showLoadingIndicator;
            if (showLoadingIndicator) {
                AjaxStatus.show();
            }
            try {
                const response = await fetch(url, init);
                if (!response.ok) {
                    throw new Error_1.StatusNotOk(response);
                }
                const json = await tryParseAsJson(response);
                if (response.headers.get("woltlab-background-queue-check") === "yes") {
                    void new Promise((resolve_1, reject_1) => { require(["../BackgroundQueue"], resolve_1, reject_1); }).then(tslib_1.__importStar).then((BackgroundQueue) => BackgroundQueue.invoke());
                }
                return json.returnValues;
            }
            catch (error) {
                if (error instanceof Error_1.ApiError) {
                    throw error;
                }
                else {
                    if (error instanceof DOMException && error.name === "AbortError") {
                        // `fetch()` will reject the promise with an `AbortError` when
                        // the request is either explicitly (through an `AbortController`)
                        // or implicitly (page navigation) aborted.
                        return;
                    }
                    if (!ignoreConnectionErrors) {
                        // Re-package the error for use in our global "unhandledrejection" handler.
                        throw new Error_1.ConnectionError(error);
                    }
                }
            }
            finally {
                if (showLoadingIndicator) {
                    AjaxStatus.hide();
                }
            }
        }
    }
    exports.DboAction = DboAction;
    exports.default = DboAction;
    async function handleValidationErrors(error, callback) {
        if (!(error instanceof Error_1.StatusNotOk)) {
            throw error;
        }
        const response = error.response.clone();
        try {
            const json = await tryParseAsJson(response);
            if (isException(json) && json.code === 412) {
                const suppressError = callback(json.returnValues);
                if (suppressError === true) {
                    return;
                }
            }
        }
        catch {
            // We do not care for any errors while attempting to parse the body..
        }
        throw error;
    }
    function isException(json) {
        return "code" in json && "returnValues" in json;
    }
    async function tryParseAsJson(response) {
        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            throw new Error_1.ExpectedJson(response);
        }
        let json;
        try {
            json = await response.json();
        }
        catch {
            throw new Error_1.InvalidJson(response);
        }
        return json;
    }
});
