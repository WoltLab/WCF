/**
 * Manages the invocation of the background queue.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/BackgroundQueue
 */

import * as Ajax from "./Ajax";
import { AjaxCallbackObject, AjaxCallbackSetup, ResponseData } from "./Ajax/Data";

class BackgroundQueue implements AjaxCallbackObject {
  private _invocations = 0;
  private _isBusy = false;
  private readonly _url: string;

  constructor(url: string) {
    this._url = url;
  }

  invoke(): void {
    if (this._isBusy) return;

    this._isBusy = true;

    Ajax.api(this);
  }

  _ajaxSuccess(data: ResponseData): void {
    this._invocations++;

    // invoke the queue up to 5 times in a row
    if (((data as unknown) as number) > 0 && this._invocations < 5) {
      window.setTimeout(() => {
        this._isBusy = false;
        this.invoke();
      }, 1000);
    } else {
      this._isBusy = false;
      this._invocations = 0;
    }
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      url: this._url,
      ignoreError: true,
      silent: true,
    };
  }
}

let queue: BackgroundQueue;

/**
 * Sets the url of the background queue perform action.
 */
export function setUrl(url: string): void {
  if (!queue) {
    queue = new BackgroundQueue(url);
  }
}

/**
 * Invokes the background queue.
 */
export function invoke(): void {
  if (!queue) {
    console.error("The background queue has not been initialized yet.");
    return;
  }

  queue.invoke();
}
