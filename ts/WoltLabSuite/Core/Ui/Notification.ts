/**
 * Simple notification overlay.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module Ui/Notification (alias)
 * @module WoltLabSuite/Core/Ui/Notification
 */

import * as Language from "../Language";

type Callback = () => void;

const enum NotificationType {
  Error = "error",
  Info = "info",
  Success = "success",
  Warning = "warning",
}

let _message: HTMLElement;
let _notificationElement: HTMLElement;
let _pendingNotification: Promise<void> | undefined = undefined;
let _timeout: number;

function init() {
  if (_notificationElement instanceof HTMLElement) {
    return;
  }

  _notificationElement = document.createElement("div");
  _notificationElement.id = "systemNotification";

  _message = document.createElement("p");
  _message.addEventListener("click", () => hideNotification());
  _notificationElement.appendChild(_message);

  document.body.appendChild(_notificationElement);
}

/**
 * Hides the notification and invokes the callback if provided.
 */
function hideNotification() {
  window.clearTimeout(_timeout);

  _notificationElement.classList.remove("active");
}

async function notify(type: NotificationType, message: string): Promise<void> {
  init();

  if (_notificationElement.classList.contains("active")) {
    await _pendingNotification!;
  }

  _message.className = type;
  _message.textContent = Language.get(message);

  _notificationElement.classList.add("active");

  _pendingNotification = new Promise<void>((resolve) => {
    _timeout = window.setTimeout(() => {
      resolve();
    }, 2_000);
  }).then(() => hideNotification());

  return _pendingNotification;
}

/**
 * @deprecated 5.5 Use `notifyError()`, `notifyInfo()`, `notifySuccess()` or `notifyWarning()` instead
 */
export function show(message = "", callback: Callback | null = null, cssClassName = ""): void {
  const validTypes = [
    NotificationType.Error,
    NotificationType.Info,
    NotificationType.Success,
    NotificationType.Warning,
  ];

  if (!validTypes.includes(cssClassName as NotificationType)) {
    cssClassName = NotificationType.Success;
  }

  const notification = notify(cssClassName as NotificationType, message);
  void notification.then(() => {
    if (typeof callback === "function") {
      callback();
    }
  });
}

export async function notifyError(message = ""): Promise<void> {
  return notify(NotificationType.Error, message);
}

export async function notifyInfo(message = ""): Promise<void> {
  return notify(NotificationType.Info, message);
}

export async function notifySuccess(message = ""): Promise<void> {
  return notify(NotificationType.Success, message);
}

export async function notifyWarning(message = ""): Promise<void> {
  return notify(NotificationType.Warning, message);
}
