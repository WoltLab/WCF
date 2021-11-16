import * as AjaxStatus from "./Ajax/Status";
import * as Core from "./Core";
import DomChangeListener from "./Dom/Change/Listener";

type Payload = Record<string, unknown>;
type ResponseData = {
  actionName: string;
  forceBackgroundQueuePerform?: boolean;
  objectIDs: number[];
  returnValues: unknown;
};

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

type RequestBody = {
  actionName: string;
  className: string;
  objectIDs?: number[];
  parameters?: Payload;
};

export class Api {
  private readonly actionName: string;
  private readonly className: string;
  private _objectIDs: number[] = [];
  private _payload: Payload = {};
  private _showLoadingIndicator = true;
  private _signal: AbortController | undefined = undefined;

  private constructor(actionName: string, className: string) {
    this.actionName = actionName;
    this.className = className;
  }

  static dboAction(actionName: string, className: string): Api {
    return new Api(actionName, className);
  }

  getAbortController(): AbortController {
    if (this._signal === undefined) {
      this._signal = new AbortController();
    }

    return this._signal;
  }

  objectIds(objectIds: number[]): this {
    this._objectIDs = objectIds;

    return this;
  }

  payload(payload: Payload): this {
    this._payload = payload;

    return this;
  }

  disableLoadingIndicator(): this {
    this._showLoadingIndicator = false;

    return this;
  }

  async dispatch(): Promise<unknown> {
    const url = window.WSC_API_URL + "index.php?ajax-proxy/&t=" + Core.getXsrfToken();

    const body: RequestBody = {
      actionName: this.actionName,
      className: this.className,
    };
    if (this._objectIDs) {
      body.objectIDs = this._objectIDs;
    }
    if (this._payload) {
      body.parameters = this._payload;
    }

    const init: RequestInit = {
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
        throw new StatusNotOk(response);
      }

      const contentType = response.headers.get("content-type");
      if (!contentType || !contentType.includes("application/json")) {
        throw new ExpectedJson(response);
      }

      let json: ResponseData;
      try {
        json = await response.json();
      } catch (e) {
        throw new InvalidJson(response);
      }

      if (json.forceBackgroundQueuePerform) {
        void import("./BackgroundQueue").then((BackgroundQueue) => BackgroundQueue.invoke());
      }

      return json.returnValues;
    } catch (error) {
      if (error instanceof ExpectedJson || error instanceof InvalidJson || error instanceof StatusNotOk) {
        throw error;
      } else {
        // Re-package the error for use in our global "unhandledrejection" handler.
        throw new ConnectionError(error);
      }
    } finally {
      if (showLoadingIndicator) {
        AjaxStatus.hide();
      }

      DomChangeListener.trigger();

      // fix anchor tags generated through WCF::getAnchor()
      document.querySelectorAll('a[href*="#"]').forEach((link: HTMLAnchorElement) => {
        let href = link.href;
        if (href.indexOf("AJAXProxy") !== -1 || href.indexOf("ajax-proxy") !== -1) {
          href = href.substr(href.indexOf("#"));
          link.href = document.location.toString().replace(/#.*/, "") + href;
        }
      });
    }
  }
}

export class ApiError extends Error {
  constructor(message: string) {
    super(message);

    this.name = "ApiError";
  }
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

async function genericError(error: ApiError): Promise<void> {
  const html = await getErrorHtml(error);

  if (html !== "") {
    // Load these modules on runtime to avoid circular dependencies.
    const [UiDialog, DomUtil, Language] = await Promise.all([
      import("./Ui/Dialog"),
      import("./Dom/Util"),
      import("./Language"),
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
    message = error.message;
  } else {
    if (error instanceof InvalidJson) {
      message = await error.response.text();
    } else if (error instanceof ExpectedJson || error instanceof StatusNotOk) {
      let json: ErrorResponse | undefined = undefined;
      try {
        json = await error.response.json();
      } catch (e) {
        message = await error.response.text();
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

window.addEventListener("unhandledrejection", (event) => {
  if (event.reason instanceof ApiError) {
    event.preventDefault();

    void genericError(event.reason);
  }
});

export default Api;
