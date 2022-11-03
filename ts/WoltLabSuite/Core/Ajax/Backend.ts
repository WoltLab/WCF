import * as LoadingIndicator from "./Status";
import {
  ApiError,
  ConnectionError,
  ExpectedJson,
  InvalidJson,
  registerGlobalRejectionHandler,
  StatusNotOk,
} from "./Error";
import { getXsrfToken } from "../Core";
import { ResponseData } from "./Data";

const enum RequestType {
  GET,
  POST,
}

type Payload = Record<string, unknown>;

class SetupRequest {
  private readonly url: string;

  constructor(url: string) {
    this.url = url;
  }

  get(): BackendRequest {
    return new BackendRequest(this.url, RequestType.GET);
  }

  post(payload?: Payload): BackendRequest {
    return new BackendRequest(this.url, RequestType.POST, payload);
  }
}

let ignoreConnectionErrors = false;
window.addEventListener("unload", () => (ignoreConnectionErrors = true));

class BackendRequest {
  readonly #url: string;
  readonly #type: RequestType;
  readonly #payload?: Payload;
  #abortController?: AbortController;
  #showLoadingIndicator = true;

  constructor(url: string, type: RequestType, payload?: Payload) {
    this.#url = url;
    this.#type = type;
    this.#payload = payload;
  }

  getAbortController(): AbortController {
    if (this.#abortController === undefined) {
      this.#abortController = new AbortController();
    }

    return this.#abortController;
  }

  disableLoadingIndicator(): this {
    this.#showLoadingIndicator = false;

    return this;
  }

  async dispatch(): Promise<unknown> {
    registerGlobalRejectionHandler();

    const init: RequestInit = {
      headers: {
        "X-Requested-With": "XMLHttpRequest",
        "X-XSRF-TOKEN": getXsrfToken(),
      },
      mode: "same-origin",
      credentials: "same-origin",
      cache: "no-store",
      redirect: "error",
    };

    if (this.#type === RequestType.POST) {
      init.method = "POST";
      init.headers!["Content-Type"] = "application/x-www-form-urlencoded; charset=UTF-8";

      if (this.#payload) {
        init.body = JSON.stringify(this.#payload);
      }
    } else {
      init.method = "GET";
    }

    if (this.#abortController) {
      init.signal = this.#abortController.signal;
    }

    // Use a local copy to isolate the behavior in case of changes before
    // the request handling has completed.
    const showLoadingIndicator = this.#showLoadingIndicator;
    if (showLoadingIndicator) {
      LoadingIndicator.show();
    }

    try {
      const response = await fetch(this.#url, init);

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
        LoadingIndicator.hide();
      }
    }
  }
}

export function prepareRequest(url: string): SetupRequest {
  return new SetupRequest(url);
}
