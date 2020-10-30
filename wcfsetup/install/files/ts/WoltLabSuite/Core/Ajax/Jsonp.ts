/**
 * Provides a utility class to issue JSONP requests.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  AjaxJsonp (alias)
 * @module  WoltLabSuite/Core/Ajax/Jsonp
 */

import * as Core from "../Core";

/**
 * Dispatch a JSONP request, the `url` must not contain a callback parameter.
 */
export function send(
  url: string,
  success: (...args: unknown[]) => void,
  failure: () => void,
  options?: JsonpOptions
): void {
  url = typeof (url as any) === "string" ? url.trim() : "";
  if (url.length === 0) {
    throw new Error("Expected a non-empty string for parameter 'url'.");
  }

  if (typeof success !== "function") {
    throw new TypeError("Expected a valid callback function for parameter 'success'.");
  }

  options = Core.extend(
    {
      parameterName: "callback",
      timeout: 10,
    },
    options || {}
  ) as JsonpOptions;

  const callbackName = "wcf_jsonp_" + Core.getUuid().replace(/-/g, "").substr(0, 8);
  const script = document.createElement("script");

  const timeout = window.setTimeout(() => {
    if (typeof failure === "function") {
      failure();
    }

    window[callbackName] = undefined;
    script.remove();
  }, (~~options.timeout || 10) * 1_000);

  window[callbackName] = (...args: any[]) => {
    window.clearTimeout(timeout);

    success(...args);

    window[callbackName] = undefined;
    script.remove();
  };

  url += url.indexOf("?") === -1 ? "?" : "&";
  url += options.parameterName + "=" + callbackName;

  script.async = true;
  script.src = url;

  document.head.appendChild(script);
}

interface JsonpOptions {
  parameterName: string;
  timeout: number;
}
