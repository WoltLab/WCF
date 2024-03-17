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
import { isPlainObject } from "../Core";
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

export function apiResultFromError(error: ApiError): ApiResult<never> {
  return {
    ok: false,
    error,
    unwrap() {
      throw error;
    },
  };
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
    json = await response.json();
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
    return apiResultFromError(new ApiError(json.type, json.code, json.message, json.param, response.status));
  }

  throw e;
}
