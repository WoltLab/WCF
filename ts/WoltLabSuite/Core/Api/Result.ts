/**
 * Represents the result of a request to an API endpoint and provides functions
 * to create the result itself. Unwrapping the result through `.unwrap()` is
 * useful in situations where there should formally never an error.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */

import { StatusNotOk } from "../Ajax/Error";
import { dialogFactory } from "../Component/Dialog";
import { isPlainObject } from "../Core";
import { getPhrase } from "../Language";
import { escapeHTML } from "../StringUtil";
import { ApiError } from "./Error";

export type ApiResult<T> =
  | {
      ok: true;
      value: T;
      unwrap(): T;
    }
  | {
      ok: false;
      error: ApiError;
      unwrap(): never;
    };

export function apiResultFromValue<T>(value: T): ApiResult<T> {
  return {
    ok: true,
    value,
    unwrap() {
      return value;
    },
  };
}

export async function apiResultFromError(error: Error): Promise<ApiResult<never>> {
  if (error instanceof StatusNotOk) {
    return apiResultFromStatusNotOk(error);
  }

  throw error;
}

export async function apiResultFromStatusNotOk(e: StatusNotOk): Promise<ApiResult<never>> {
  const { response } = e;

  if (response === undefined) {
    // Aborted requests do not have a return value.
    throw e;
  }

  const contentType = response.headers.get("content-type");
  if (!contentType || !contentType.includes("application/json")) {
    throw e;
  }

  let json: unknown;
  try {
    json = await response.clone().json();
  } catch {
    throw e;
  }

  if (
    isPlainObject(json) &&
    Object.hasOwn(json, "type") &&
    (json.type === "api_error" || json.type === "invalid_request_error") &&
    typeof json.code === "string" &&
    typeof json.message === "string" &&
    typeof json.param === "string"
  ) {
    const apiError = new ApiError(json.type, json.code, json.message, json.param, response.status);

    return {
      ok: false,
      error: apiError,
      unwrap() {
        showErrorDialog(apiError);

        throw new Error("Trying to unwrap an erroneous result.", { cause: apiError });
      },
    };
  }

  throw e;
}

/**
 * Helper method for API requests that are expected to never fail. Infallible
 * requests are those that should only fail if there is an unexpected server
 * error or if the request was the result of a bug in the client.
 */
export async function fromInfallibleApiRequest<T = unknown>(request: () => Promise<unknown>): Promise<T> {
  try {
    return (await request()) as T;
  } catch (e) {
    const error = await apiResultFromError(e);
    return error.unwrap();
  }
}

function showErrorDialog(apiError: ApiError): void {
  let html = "";
  if ((!window.ENABLE_DEBUG_MODE && apiError.code === "permission_denied") || apiError.code === "assertion_failed") {
    html = getPhrase(
      apiError.code === "permission_denied" ? "wcf.ajax.error.permissionDenied" : "wcf.ajax.error.illegalLink",
    );
  } else {
    const code = escapeHTML(apiError.code);
    const type = escapeHTML(apiError.type);
    const message = apiError.message ? escapeHTML(apiError.message) : "(not set)";
    const param = apiError.param ? "<kbd>" + escapeHTML(apiError.param) + "</kbd>" : "(not set)";

    html = `
      <dl>
        <dt>Unexpected server error</dt>
        <dd><kbd>${type}</kbd></dd>
      </dl>
      <dl>
        <dt>Error code</dt>
        <dd><kbd>${code}</kbd></dd>
      </dl>
      <dl>
        <dt>Parameter</dt>
        <dd>${param}</dd>
      </dl>
      <dl>
        <dt>Message</dt>
        <dd>${message}</dd>
      </dl>
    `;
  }

  const dialog = dialogFactory().fromHtml(html).asAlert();
  dialog.show(getPhrase("wcf.global.error.title"));
}
