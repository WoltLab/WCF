/**
 * Handles AJAX requests.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Ajax (alias)
 * @module  WoltLabSuite/Core/Ajax
 */

import AjaxRequest from "./Ajax/Request";
import { AjaxCallbackObject, CallbackSuccess, CallbackFailure, RequestData, RequestOptions } from "./Ajax/Data";

const _cache = new WeakMap();

/**
 * Shorthand function to perform a request against the WCF-API with overrides
 * for success and failure callbacks.
 */
export function api(
  callbackObject: AjaxCallbackObject,
  data?: RequestData,
  success?: CallbackSuccess,
  failure?: CallbackFailure,
): AjaxRequest {
  if (typeof data !== "object") data = {};

  let request = _cache.get(callbackObject);
  if (request === undefined) {
    if (typeof callbackObject._ajaxSetup !== "function") {
      throw new TypeError("Callback object must implement at least _ajaxSetup().");
    }

    const options = callbackObject._ajaxSetup();

    options.pinData = true;
    options.callbackObject = callbackObject;

    if (!options.url) {
      options.url = "index.php?ajax-proxy/&t=" + window.SECURITY_TOKEN;
      options.withCredentials = true;
    }

    request = new AjaxRequest(options);

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
  if (oldSuccess !== null) request.setOption("success", oldSuccess);
  if (oldFailure !== null) request.setOption("failure", oldFailure);

  return request;
}

/**
 * Shorthand function to perform a single request against the WCF-API.
 *
 * Please use `Ajax.api` if you're about to repeatedly send requests because this
 * method will spawn an new and rather expensive `AjaxRequest` with each call.
 */
export function apiOnce(options: RequestOptions): void {
  options.pinData = false;
  options.callbackObject = null;
  if (!options.url) {
    options.url = "index.php?ajax-proxy/&t=" + window.SECURITY_TOKEN;
    options.withCredentials = true;
  }

  const request = new AjaxRequest(options);
  request.sendRequest(false);
}

/**
 * Returns the request object used for an earlier call to `api()`.
 */
export function getRequestObject(callbackObject: AjaxCallbackObject): AjaxRequest {
  if (!_cache.has(callbackObject)) {
    throw new Error("Expected a previously used callback object, provided object is unknown.");
  }

  return _cache.get(callbackObject);
}
