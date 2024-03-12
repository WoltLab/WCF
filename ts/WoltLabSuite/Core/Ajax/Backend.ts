/**
 * Promise-based API to interact with PSR-15 controllers.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import * as LoadingIndicator from "./Status";
import {
  ApiError,
  ConnectionError,
  ExpectedJson,
  InvalidJson,
  registerGlobalRejectionHandler,
  StatusNotOk,
} from "./Error";
import { extend, getXsrfToken } from "../Core";

const enum RequestType {
  DELETE,
  GET,
  POST,
}

type Payload = FormData | Record<string, unknown>;

class SetupRequest {
  private readonly url: string;

  constructor(url: string) {
    this.url = url;
  }

  delete(): BackendRequest {
    return new BackendRequest(this.url, RequestType.DELETE);
  }

  get(): GetRequest {
    return new GetRequest(this.url, RequestType.GET);
  }

  post(payload?: Payload): BackendRequest {
    return new BackendRequest(this.url, RequestType.POST, payload);
  }
}

let ignoreConnectionErrors = false;
window.addEventListener("beforeunload", () => (ignoreConnectionErrors = true));

class BackendRequest {
  readonly #url: string;
  readonly #type: RequestType;
  readonly #payload?: Payload;
  #abortController?: AbortController;
  #showLoadingIndicator = true;
  #allowCaching = false;

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

  protected allowCaching(): this {
    this.#allowCaching = true;

    return this;
  }

  async fetchAsJson(): Promise<unknown> {
    const response = await this.#fetch({
      headers: {
        accept: "application/json",
      },
    });

    if (response === undefined) {
      // Aborted requests do not have a return value.
      return undefined;
    }

    const contentType = response.headers.get("content-type");
    if (!contentType || !contentType.includes("application/json")) {
      throw new ExpectedJson(response);
    }

    let json: unknown;
    try {
      json = await response.json();
    } catch (e) {
      throw new InvalidJson(response);
    }

    return json;
  }

  async fetchAsResponse(): Promise<Response | undefined> {
    return this.#fetch();
  }

  async #fetch(requestOptions: RequestInit = {}): Promise<Response | undefined> {
    registerGlobalRejectionHandler();

    const init: RequestInit = extend(
      {
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          "X-XSRF-TOKEN": getXsrfToken(),
        },
        mode: "same-origin",
        credentials: "same-origin",
        cache: this.#allowCaching ? "default" : "no-store",
        redirect: "error",
      },
      requestOptions,
    );

    if (this.#type === RequestType.POST) {
      init.method = "POST";

      if (this.#payload) {
        if (this.#payload instanceof FormData) {
          init.headers!["Content-Type"] = "application/x-www-form-urlencoded; charset=UTF-8";
          init.body = this.#payload;
        } else {
          init.headers!["Content-Type"] = "application/json; charset=UTF-8";
          init.body = JSON.stringify(this.#payload);
        }
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

      if (response.headers.get("woltlab-background-queue-check") === "yes") {
        void import("../BackgroundQueue").then((BackgroundQueue) => BackgroundQueue.invoke());
      }

      return response;
    } catch (error) {
      if (error instanceof ApiError) {
        throw error;
      } else {
        if (error instanceof DOMException && error.name === "AbortError") {
          // `fetch()` will reject the promise with an `AbortError` when
          // the request is either explicitly (through an `AbortController`)
          // or implicitly (page navigation) aborted.
          return undefined;
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

class GetRequest extends BackendRequest {
  public allowCaching(): this {
    super.allowCaching();

    return this;
  }
}

export function prepareRequest(url: string | URL): SetupRequest {
  return new SetupRequest(url.toString());
}
