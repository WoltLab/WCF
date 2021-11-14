import * as AjaxStatus from "./Ajax/Status";
import * as Core from "./Core";
import DomChangeListener from "./Dom/Change/Listener";

type Payload = Record<string, unknown>;
type ResponseData = Record<string, unknown>;

export type CallbackFailure = (result: ErrorResult) => boolean;

export enum ErrorCode {
  CONNECTION_ERROR = "connection_error",
  EXPECTED_JSON = "expected_json",
  INVALID_JSON = "invalid_json",
  STATUS_NOT_OK = "status_not_ok",
}

export type ErrorResult =
  | {
      code: ErrorCode;
      error?: never;
      response: Response;
    }
  | {
      code: ErrorCode;
      error: any;
      response?: never;
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

export class Api<TResponseData extends ResponseData> {
  private readonly actionName: string;
  private readonly className: string;
  private _failure: CallbackFailure | undefined = undefined;
  private _objectIDs: number[] = [];
  private _payload: Payload = {};
  private _showLoadingIndicator = true;
  private _signal: AbortController | undefined = undefined;

  private constructor(actionName: string, className: string) {
    this.actionName = actionName;
    this.className = className;
  }

  static prepare<TResponseData extends ResponseData>(actionName: string, className: string): Api<TResponseData> {
    return new Api<TResponseData>(actionName, className);
  }

  failure(failure: CallbackFailure): this {
    this._failure = failure;

    return this;
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

  async dispatch(): Promise<TResponseData> {
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

    // Use a local copy to isolte the behavior in case of changes before
    // the request handling has completed.
    const showLoadingIndicator = this._showLoadingIndicator;
    if (showLoadingIndicator) {
      AjaxStatus.show();
    }

    try {
      const response = await fetch(url, init);

      if (!response.ok) {
        const result = this.handleError(ErrorCode.STATUS_NOT_OK, response);

        return Promise.reject(result);
      }

      const contentType = response.headers.get("content-type");
      if (!contentType || !contentType.includes("application/json")) {
        const result = this.handleError(ErrorCode.EXPECTED_JSON, response);

        return Promise.reject(result);
      }

      let json: TResponseData;
      try {
        json = await response.json();
      } catch (e) {
        const result = this.handleError(ErrorCode.INVALID_JSON, response);

        return Promise.reject(result);
      }

      // This is an explicit wait for the event loop to execute the
      // callee function before we execute additional tasks.
      return Promise.resolve(json).then(async (result) => {
        if (json.forceBackgroundQueuePerform) {
          const backgroundQueue = await import("./BackgroundQueue");
          backgroundQueue.invoke();
        }
        return result;
      });
    } catch (error) {
      const result: ErrorResult = {
        code: ErrorCode.CONNECTION_ERROR,
        error,
      };
      let suppressError = false;
      if (typeof this._failure === "function") {
        suppressError = this._failure(result);
      }

      if (!suppressError) {
        await this.genericError(result);
      }

      return Promise.reject(result);
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

  private async handleError(code: ErrorCode, response: Response): Promise<ErrorResult> {
    const result: ErrorResult = { code, response };
    if (!this.suppressError(result)) {
      await this.genericError(result);
    }

    return result;
  }

  private suppressError(result: ErrorResult): boolean {
    if (typeof this._failure === "function") {
      return this._failure(result);
    }

    return true;
  }

  private async genericError(result: ErrorResult): Promise<void> {
    const html = await this.getErrorHtml(result);

    if (html !== "") {
      const uiDialog = await import("./Ui/Dialog");
      const domUtil = await import("./Dom/Util");
      const language = await import("./Language");

      uiDialog.openStatic(domUtil.getUniqueId(), html, {
        title: language.get("wcf.global.error.title"),
      });
    }
  }

  private async getErrorHtml(result: ErrorResult): Promise<string> {
    let details = "";
    let message = "";

    if (result.error) {
      message = result.error.toString();
    } else if (result.response) {
      if (result.code === ErrorCode.INVALID_JSON) {
        message = await result.response.text();
      } else {
        const json = (await result.response.json()) as ErrorResponse;

        if (Core.isPlainObject(json) && Object.keys(json).length > 0) {
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
}

export default Api;
