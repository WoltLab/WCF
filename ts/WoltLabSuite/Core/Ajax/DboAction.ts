/**
 * Dispatch requests to `wcf\\data\\DatabaseObjectAction` actions with a
 * `Promise`-based API and full IDE support.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.5
 */

import {
  ApiError,
  ConnectionError,
  ExpectedJson,
  InvalidJson,
  StatusNotOk,
  registerGlobalRejectionHandler,
} from "./Error";
import * as AjaxStatus from "./Status";
import * as Core from "../Core";

type Payload = Record<string, unknown>;
type ResponseData = {
  actionName: string;
  objectIDs: number[];
  returnValues: unknown;
};

type RequestBody = {
  actionName: string;
  className: string;
  objectIDs?: number[];
  parameters?: Payload;
};

let ignoreConnectionErrors: boolean | undefined = undefined;

export class DboAction {
  readonly #actionName: string;
  readonly #className: string;
  #objectIDs: number[] = [];
  #payload: Payload = {};
  #showLoadingIndicator = true;
  #signal?: AbortController;

  private constructor(actionName: string, className: string) {
    this.#actionName = actionName;
    this.#className = className;
  }

  static prepare(actionName: string, className: string): DboAction {
    if (ignoreConnectionErrors === undefined) {
      ignoreConnectionErrors = false;

      window.addEventListener("beforeunload", () => {
        ignoreConnectionErrors = true;
      });
    }

    return new DboAction(actionName, className);
  }

  getAbortController(): AbortController {
    if (this.#signal === undefined) {
      this.#signal = new AbortController();
    }

    return this.#signal;
  }

  objectIds(objectIds: number[]): this {
    this.#objectIDs = objectIds;

    return this;
  }

  payload(payload: Payload): this {
    this.#payload = payload;

    return this;
  }

  disableLoadingIndicator(): this {
    this.#showLoadingIndicator = false;

    return this;
  }

  async dispatch(): Promise<unknown> {
    registerGlobalRejectionHandler();

    const url = window.WSC_API_URL + "index.php?ajax-proxy/&t=" + Core.getXsrfToken();

    const body: RequestBody = {
      actionName: this.#actionName,
      className: this.#className,
    };
    if (this.#objectIDs) {
      body.objectIDs = this.#objectIDs;
    }
    if (this.#payload) {
      body.parameters = this.#payload;
    }

    const init: RequestInit = {
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
        throw new StatusNotOk(response);
      }

      const json = await tryParseAsJson(response);

      if (response.headers.get("woltlab-background-queue-check") === "yes") {
        void import("../BackgroundQueue").then((BackgroundQueue) => BackgroundQueue.invoke());
      }

      return json.returnValues;
    } catch (error) {
      if (error instanceof ApiError) {
        throw error;
      } else {
        if (error instanceof DOMException && error.name === "AbortError") {
          // `fetch()` will reject the promise with an `AbortError` when
          // the request is either explicitly (through an `AbortController`)
          // or implicitly (page navigation) aborted.
          return;
        }

        if (!ignoreConnectionErrors) {
          // Re-package the error for use in our global "unhandledrejection" handler.
          throw new ConnectionError(error);
        }
      }
    } finally {
      if (showLoadingIndicator) {
        AjaxStatus.hide();
      }
    }
  }
}

export default DboAction;

type ReturnValuesUserInputException = {
  errorMessage: string;
  errorType: string;
  fieldName: string;
  realErrorMessage: string;
};

type ResponseDataException = {
  code: number;
  returnValues: ReturnValuesUserInputException;
};

type CallbackHandleValidationErrors = (returnValues: ReturnValuesUserInputException) => boolean;

export async function handleValidationErrors(error: Error, callback: CallbackHandleValidationErrors): Promise<void> {
  if (!(error instanceof StatusNotOk)) {
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
  } catch {
    // We do not care for any errors while attempting to parse the body..
  }

  throw error;
}

function isException(json: Record<string, unknown>): json is ResponseDataException {
  return "code" in json && "returnValues" in json;
}

async function tryParseAsJson(response: Response): Promise<ResponseData> {
  const contentType = response.headers.get("content-type");
  if (!contentType || !contentType.includes("application/json")) {
    throw new ExpectedJson(response);
  }

  let json: ResponseData;
  try {
    json = await response.json();
  } catch {
    throw new InvalidJson(response);
  }

  return json;
}
