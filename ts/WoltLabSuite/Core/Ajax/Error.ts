/**
 * Error types and a global error handler for the `Promise`-based `DboAction` class.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Ajax/Error
 * @since 5.5
 */

import * as Core from "../Core";
import * as Language from "../Language";

type ErrorResponsePrevious = {
  message: string;
  stacktrace: string;
};

type ErrorResponse = {
  exceptionID?: string;
  file?: string;
  line?: number;
  message: string;
  previous: ErrorResponsePrevious[];
  returnValues?: {
    description?: string;
  };
  stacktrace?: string;
};

async function genericError(error: ApiError): Promise<void> {
  const html = await getErrorHtml(error);

  if (html !== "") {
    // Load these modules on runtime to avoid circular dependencies.
    const [UiDialog, DomUtil, Language] = await Promise.all([
      import("../Ui/Dialog"),
      import("../Dom/Util"),
      import("../Language"),
    ]);
    UiDialog.openStatic(DomUtil.getUniqueId(), html, {
      title: Language.get("wcf.global.error.title"),
    });
  }
}

async function getErrorHtml(error: ApiError): Promise<string> {
  let details = "";
  let message = "";

  if (error instanceof ConnectionError) {
    // `fetch()` will yield a `TypeError` for network errors and CORS violations.
    if (error.originalError instanceof TypeError) {
      message = Language.get("wcf.global.error.ajax.network", { message: error.message });
    } else {
      message = error.message;
    }
  } else {
    if (error instanceof InvalidJson) {
      message = await error.response.clone().text();
    } else if (error instanceof ExpectedJson || error instanceof StatusNotOk) {
      let json: ErrorResponse | undefined = undefined;
      try {
        json = await error.response.clone().json();
      } catch (e) {
        message = await error.response.clone().text();
      }

      if (json && Core.isPlainObject(json) && Object.keys(json).length > 0) {
        if (json.returnValues && json.returnValues.description) {
          details += `<br><p>Description:</p><p>${json.returnValues.description}</p>`;
        }

        if (json.file && json.line) {
          details += `<br><p>File:</p><p>${json.file} in line ${json.line}</p>`;
        }

        if (json.stacktrace) {
          details += `<br><p>Stacktrace:</p><p>${json.stacktrace}</p>`;
        } else if (json.exceptionID) {
          details += `<br><p>Exception ID: <code>${json.exceptionID}</code></p>`;
        }

        message = json.message;

        json.previous.forEach((previous) => {
          details += `<hr><p>${previous.message}</p>`;
          details += `<br><p>Stacktrace</p><p>${previous.stacktrace}</p>`;
        });
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

export class ApiError extends Error {
  name = "ApiError";
}

export class ConnectionError extends ApiError {
  readonly originalError: unknown;

  constructor(originalError: unknown) {
    let message = "Unknown error";
    if (originalError instanceof Error) {
      message = originalError.message;
    }

    super(message);

    this.name = "ConnectionError";
    this.originalError = originalError;
  }
}

export class StatusNotOk extends ApiError {
  readonly response: Response;

  constructor(response: Response) {
    super("The API request returned a status code outside of the 200-299 range.");

    this.name = "StatusNotOk";
    this.response = response;
  }
}

export class ExpectedJson extends ApiError {
  readonly response: Response;

  constructor(response: Response) {
    super("The API did not return a JSON response.");

    this.name = "ExpectedJson";
    this.response = response;
  }
}

export class InvalidJson extends ApiError {
  readonly response: Response;

  constructor(response: Response) {
    super("Failed to decode the JSON response from the API.");

    this.name = "InvalidJson";
    this.response = response;
  }
}

let hasGlobalRejectionHandler = false;
export function registerGlobalRejectionHandler(): void {
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
