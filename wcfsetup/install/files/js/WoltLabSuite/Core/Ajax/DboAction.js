/**
 * Dispatch requests to `wcf\\data\\DatabaseObjectAction` actions with a
 * `Promise`-based API and full IDE support.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Ajax/DboAction
 * @since 5.5
 */
define(["require", "exports", "tslib", "./Error", "./Status", "../Core", "../Dom/Change/Listener"], function (require, exports, tslib_1, Error_1, AjaxStatus, Core, Listener_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.DboAction = void 0;
    AjaxStatus = tslib_1.__importStar(AjaxStatus);
    Core = tslib_1.__importStar(Core);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    class DboAction {
        constructor(actionName, className) {
            this._objectIDs = [];
            this._payload = {};
            this._showLoadingIndicator = true;
            this._signal = undefined;
            this.actionName = actionName;
            this.className = className;
        }
        static prepare(actionName, className) {
            return new DboAction(actionName, className);
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
            (0, Error_1.registerGlobalRejectionHandler)();
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
                    throw new Error_1.StatusNotOk(response);
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
                if (json.forceBackgroundQueuePerform) {
                    void new Promise((resolve_1, reject_1) => { require(["../BackgroundQueue"], resolve_1, reject_1); }).then(tslib_1.__importStar).then((BackgroundQueue) => BackgroundQueue.invoke());
                }
                return json.returnValues;
            }
            catch (error) {
                if (error instanceof Error_1.ApiError) {
                    throw error;
                }
                else {
                    // Re-package the error for use in our global "unhandledrejection" handler.
                    throw new Error_1.ConnectionError(error);
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
    exports.DboAction = DboAction;
    exports.default = DboAction;
});
