/**
 * Dispatch requests to `wcf\\data\\DatabaseObjectAction` actions with a
 * `Promise`-based API and full IDE support.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Ajax/DboAction
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
  forceBackgroundQueuePerform?: boolean;
  objectIDs: number[];
  returnValues: unknown;
};

type RequestBody = {
  actionName: string;
  className: string;
  objectIDs?: number[];
  parameters?: Payload;
};

export class DboAction {
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

  static prepare(actionName: string, className: string): DboAction {
    return new DboAction(actionName, className);
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
    registerGlobalRejectionHandler();

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
        void import("../BackgroundQueue").then((BackgroundQueue) => BackgroundQueue.invoke());
      }

      return json.returnValues;
    } catch (error) {
      if (error instanceof ApiError) {
        throw error;
      } else {
        // Re-package the error for use in our global "unhandledrejection" handler.
        throw new ConnectionError(error);
      }
    } finally {
      if (showLoadingIndicator) {
        AjaxStatus.hide();
      }
    }
  }
}

export default DboAction;
