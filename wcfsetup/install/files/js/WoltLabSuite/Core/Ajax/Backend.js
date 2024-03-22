/**
 * Promise-based API to interact with PSR-15 controllers.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports", "tslib", "./Status", "./Error", "../Core"], function (require, exports, tslib_1, LoadingIndicator, Error_1, Core_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.prepareRequest = void 0;
    LoadingIndicator = tslib_1.__importStar(LoadingIndicator);
    class SetupRequest {
        url;
        constructor(url) {
            this.url = url;
        }
        delete() {
            return new BackendRequest(this.url, 0 /* RequestType.DELETE */);
        }
        get() {
            return new GetRequest(this.url, 1 /* RequestType.GET */);
        }
        post(payload) {
            return new BackendRequest(this.url, 2 /* RequestType.POST */, payload);
        }
    }
    let ignoreConnectionErrors = false;
    window.addEventListener("beforeunload", () => (ignoreConnectionErrors = true));
    class BackendRequest {
        #headers = new Map();
        #url;
        #type;
        #payload;
        #abortController;
        #showLoadingIndicator = true;
        #allowCaching = false;
        constructor(url, type, payload) {
            this.#url = url;
            this.#type = type;
            this.#payload = payload;
        }
        getAbortController() {
            if (this.#abortController === undefined) {
                this.#abortController = new AbortController();
            }
            return this.#abortController;
        }
        disableLoadingIndicator() {
            this.#showLoadingIndicator = false;
            return this;
        }
        withHeader(key, value) {
            this.#headers.set(key, value);
            return this;
        }
        allowCaching() {
            this.#allowCaching = true;
            return this;
        }
        async fetchAsJson() {
            const response = await this.#fetch({
                headers: {
                    accept: "application/json",
                },
            });
            if (response === undefined) {
                // Aborted requests do not have a return value.
                return undefined;
            }
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                throw new Error_1.ExpectedJson(response);
            }
            let json;
            try {
                json = await response.json();
            }
            catch (e) {
                throw new Error_1.InvalidJson(response);
            }
            return json;
        }
        async fetchAsResponse() {
            return this.#fetch();
        }
        async #fetch(requestOptions = {}) {
            (0, Error_1.registerGlobalRejectionHandler)();
            this.#headers.set("X-Requested-With", "XMLHttpRequest");
            this.#headers.set("X-XSRF-TOKEN", (0, Core_1.getXsrfToken)());
            const headers = Object.fromEntries(this.#headers);
            const init = (0, Core_1.extend)({
                headers,
                mode: "same-origin",
                credentials: "same-origin",
                cache: this.#allowCaching ? "default" : "no-store",
                redirect: "error",
            }, requestOptions);
            if (this.#type === 2 /* RequestType.POST */) {
                init.method = "POST";
                if (this.#payload) {
                    if (this.#payload instanceof Blob) {
                        init.headers["Content-Type"] = "application/octet-stream";
                        init.body = this.#payload;
                    }
                    else if (this.#payload instanceof FormData) {
                        init.headers["Content-Type"] = "application/x-www-form-urlencoded; charset=UTF-8";
                        init.body = this.#payload;
                    }
                    else {
                        init.headers["Content-Type"] = "application/json; charset=UTF-8";
                        init.body = JSON.stringify(this.#payload);
                    }
                }
            }
            else {
                init.method = "GET";
            }
            if (this.#abortController) {
                init.signal = this.#abortController.signal;
            }
            // Use a local copy to isolate the behavior in case of changes before
            // the request handling has completed.
            const showLoadingIndicator = this.#showLoadingIndicator;
            if (showLoadingIndicator) {
                LoadingIndicator.show();
            }
            try {
                const response = await fetch(this.#url, init);
                if (!response.ok) {
                    throw new Error_1.StatusNotOk(response);
                }
                if (response.headers.get("woltlab-background-queue-check") === "yes") {
                    void new Promise((resolve_1, reject_1) => { require(["../BackgroundQueue"], resolve_1, reject_1); }).then(tslib_1.__importStar).then((BackgroundQueue) => BackgroundQueue.invoke());
                }
                return response;
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
                        return undefined;
                    }
                    if (!ignoreConnectionErrors) {
                        // Re-package the error for use in our global "unhandledrejection" handler.
                        throw new Error_1.ConnectionError(error);
                    }
                }
            }
            finally {
                if (showLoadingIndicator) {
                    LoadingIndicator.hide();
                }
            }
        }
    }
    class GetRequest extends BackendRequest {
        allowCaching() {
            super.allowCaching();
            return this;
        }
    }
    function prepareRequest(url) {
        return new SetupRequest(url.toString());
    }
    exports.prepareRequest = prepareRequest;
});
