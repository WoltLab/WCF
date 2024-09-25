/**
 * Handles AJAX requests.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "./Ajax/Request", "./Core", "./Ajax/DboAction", "./Ajax/DboAction"], function (require, exports, tslib_1, Request_1, Core, DboAction_1, DboAction_2) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.handleValidationErrors = void 0;
    exports.api = api;
    exports.apiOnce = apiOnce;
    exports.getRequestObject = getRequestObject;
    exports.dboAction = dboAction;
    Request_1 = tslib_1.__importDefault(Request_1);
    Core = tslib_1.__importStar(Core);
    DboAction_1 = tslib_1.__importDefault(DboAction_1);
    const _cache = new WeakMap();
    /**
     * Shorthand function to perform a request against the WCF-API with overrides
     * for success and failure callbacks.
     */
    function api(callbackObject, data, success, failure) {
        if (typeof data !== "object")
            data = {};
        let request = _cache.get(callbackObject);
        if (request === undefined) {
            if (typeof callbackObject._ajaxSetup !== "function") {
                throw new TypeError("Callback object must implement at least _ajaxSetup().");
            }
            const options = callbackObject._ajaxSetup();
            options.pinData = true;
            options.callbackObject = callbackObject;
            if (!options.url) {
                options.url = "index.php?ajax-proxy/&t=" + Core.getXsrfToken();
                options.withCredentials = true;
            }
            request = new Request_1.default(options);
            _cache.set(callbackObject, request);
        }
        let oldSuccess = null;
        let oldFailure = null;
        if (typeof success === "function") {
            oldSuccess = request.getOption("success");
            request.setOption("success", success);
        }
        if (typeof failure === "function") {
            oldFailure = request.getOption("failure");
            request.setOption("failure", failure);
        }
        request.setData(data);
        request.sendRequest();
        // restore callbacks
        if (oldSuccess !== null)
            request.setOption("success", oldSuccess);
        if (oldFailure !== null)
            request.setOption("failure", oldFailure);
        return request;
    }
    /**
     * Shorthand function to perform a single request against the WCF-API.
     *
     * Please use `Ajax.api` if you're about to repeatedly send requests because this
     * method will spawn an new and rather expensive `AjaxRequest` with each call.
     */
    function apiOnce(options) {
        options.pinData = false;
        options.callbackObject = null;
        if (!options.url) {
            options.url = "index.php?ajax-proxy/&t=" + Core.getXsrfToken();
            options.withCredentials = true;
        }
        const request = new Request_1.default(options);
        request.sendRequest(false);
    }
    /**
     * Returns the request object used for an earlier call to `api()`.
     */
    function getRequestObject(callbackObject) {
        if (!_cache.has(callbackObject)) {
            throw new Error("Expected a previously used callback object, provided object is unknown.");
        }
        return _cache.get(callbackObject);
    }
    /**
     * Prepares a new requests to a `wcf\\data\\DatabaseObjectAction` action.
     *
     * @since 5.5
     */
    function dboAction(actionName, className) {
        return DboAction_1.default.prepare(actionName, className);
    }
    Object.defineProperty(exports, "handleValidationErrors", { enumerable: true, get: function () { return DboAction_2.handleValidationErrors; } });
});
