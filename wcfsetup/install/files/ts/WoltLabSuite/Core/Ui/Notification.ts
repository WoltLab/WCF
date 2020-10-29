/**
 * Simple notification overlay.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Ui/Notification (alias)
 * @module  WoltLabSuite/Core/Ui/Notification
 */

import * as Language from "../Language";

type Callback = () => void;

let _busy = false;
let _callback: Callback | null = null;
let _didInit = false;
let _message: HTMLElement;
let _notificationElement: HTMLElement;
let _timeout: number;

function init() {
  if (_didInit) return;
  _didInit = true;

  _notificationElement = document.createElement("div");
  _notificationElement.id = "systemNotification";

  _message = document.createElement("p");
  _message.addEventListener("click", hide);
  _notificationElement.appendChild(_message);

  document.body.appendChild(_notificationElement);
}

/**
 * Hides the notification and invokes the callback if provided.
 */
function hide() {
  clearTimeout(_timeout);

  _notificationElement.classList.remove("active");

  if (_callback !== null) {
    _callback();
  }

  _busy = false;
}

/**
 * Displays a notification.
 */
export function show(message?: string, callback?: Callback, cssClassName?: string): void {
  if (_busy) {
    return;
  }
  _busy = true;

  init();

  _callback = typeof callback === "function" ? callback : null;
  _message.className = cssClassName || "success";
  _message.textContent = Language.get(message || "wcf.global.success");

  _notificationElement.classList.add("active");
  _timeout = setTimeout(hide, 2000);
}
