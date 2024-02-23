/**
 * Provides desktop notifications via periodic polling with an
 * increasing request delay on inactivity.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */

import * as Ajax from "../Ajax";
import { AjaxCallbackSetup } from "../Ajax/Data";
import * as Core from "../Core";
import * as EventHandler from "../Event/Handler";
import { serviceWorkerSupported } from "./ServiceWorker";

interface NotificationHandlerOptions {
  icon: string;
}

interface PollingResult {
  notification: {
    link: string;
    message?: string;
    title: string;
  };
}

interface AjaxResponse {
  returnValues: {
    keepAliveData: unknown;
    lastRequestTimestamp: number;
    pollData: PollingResult;
  };
}

class NotificationHandler {
  private allowNotification: boolean;
  private readonly icon: string;
  private inactiveSince = 0;
  private lastRequestTimestamp = window.TIME_NOW;
  private requestTimer?: number = undefined;

  /**
   * Initializes the desktop notification system.
   */
  constructor(options: NotificationHandlerOptions) {
    options = Core.extend(
      {
        icon: "",
      },
      options,
    ) as NotificationHandlerOptions;

    this.icon = options.icon;

    this.prepareNextRequest();

    document.addEventListener("visibilitychange", (ev) => this.onVisibilityChange(ev));
    window.addEventListener("storage", () => this.onStorage());

    this.onVisibilityChange();

    if ("Notification" in window && Notification.permission === "granted") {
      this.allowNotification = true;
    }
    if (serviceWorkerSupported()) {
      window.navigator.serviceWorker.addEventListener("message", (event) => {
        const payload = event.data;
        if (payload.time > this.lastRequestTimestamp) {
          this.lastRequestTimestamp = payload.time;
        }
      });
    }
  }

  enableNotifications(): void {
    this.allowNotification = true;
  }

  /**
   * Detects when this window is hidden or restored.
   */
  private onVisibilityChange(event?: Event) {
    // document was hidden before
    if (event && !document.hidden) {
      const difference = (Date.now() - this.inactiveSince) / 60_000;
      if (difference > 4) {
        this.resetTimer();
        this.dispatchRequest();
      }
    }

    this.inactiveSince = document.hidden ? Date.now() : 0;
  }

  /**
   * Returns the delay in minutes before the next request should be dispatched.
   */
  private getNextDelay(): number {
    if (this.inactiveSince === 0) {
      return 5;
    }

    // milliseconds -> minutes
    const inactiveMinutes = ~~((Date.now() - this.inactiveSince) / 60_000);
    if (inactiveMinutes < 15) {
      return 5;
    } else if (inactiveMinutes < 30) {
      return 10;
    }

    return 15;
  }

  /**
   * Resets the request delay timer.
   */
  private resetTimer(): void {
    if (this.requestTimer) {
      window.clearTimeout(this.requestTimer);
      this.requestTimer = undefined;
    }
  }

  /**
   * Schedules the next request using a calculated delay.
   */
  private prepareNextRequest(): void {
    this.resetTimer();

    this.requestTimer = window.setTimeout(() => this.dispatchRequest(), this.getNextDelay() * 60_000);
  }

  /**
   * Requests new data from the server.
   */
  dispatchRequest(): void {
    const parameters: ArbitraryObject = {};

    EventHandler.fire("com.woltlab.wcf.notification", "beforePoll", parameters);

    // this timestamp is used to determine new notifications and to avoid
    // notifications being displayed multiple times due to different origins
    // (=subdomains) used, because we cannot synchronize them in the client
    parameters.lastRequestTimestamp = this.lastRequestTimestamp;

    Ajax.api(this, {
      parameters: parameters,
    });
  }

  /**
   * Notifies subscribers for updated data received by another tab.
   */
  private onStorage(): void {
    // abort and re-schedule periodic request
    this.prepareNextRequest();

    let pollData: unknown;
    let keepAliveData: unknown;
    let abort = false;
    try {
      pollData = window.localStorage.getItem(Core.getStoragePrefix() + "notification");
      keepAliveData = window.localStorage.getItem(Core.getStoragePrefix() + "keepAliveData");

      pollData = JSON.parse(pollData as string);
      keepAliveData = JSON.parse(keepAliveData as string);
    } catch (e) {
      abort = true;
    }

    if (!abort) {
      EventHandler.fire("com.woltlab.wcf.notification", "onStorage", {
        pollData,
        keepAliveData,
      });
    }
  }

  _ajaxSuccess(data: AjaxResponse): void {
    const keepAliveData = data.returnValues.keepAliveData;
    const pollData = data.returnValues.pollData;

    // forward keep alive data
    window.WCF.System.PushNotification.executeCallbacks({ returnValues: keepAliveData });

    // store response data in local storage
    let abort = false;
    try {
      window.localStorage.setItem(Core.getStoragePrefix() + "notification", JSON.stringify(pollData));
      window.localStorage.setItem(Core.getStoragePrefix() + "keepAliveData", JSON.stringify(keepAliveData));
    } catch (e) {
      // storage is unavailable, e.g. in private mode, log error and disable polling
      abort = true;

      window.console.log(e);
    }

    if (!abort) {
      this.prepareNextRequest();
    }

    this.lastRequestTimestamp = data.returnValues.lastRequestTimestamp;

    EventHandler.fire("com.woltlab.wcf.notification", "afterPoll", pollData);

    this.showNotification(pollData);
  }

  /**
   * Displays a desktop notification.
   */
  private showNotification(pollData: PollingResult): void {
    if (!this.allowNotification) {
      return;
    }

    if (typeof pollData.notification === "object" && typeof pollData.notification.message === "string") {
      let notification: Notification;

      const div = document.createElement("div");
      div.innerHTML = pollData.notification.message;
      div.querySelectorAll("img").forEach((img) => {
        img.replaceWith(document.createTextNode(img.alt));
      });

      try {
        notification = new window.Notification(pollData.notification.title, {
          body: div.textContent!,
          icon: this.icon,
        });
      } catch (e) {
        // The `Notification` constructor is not available on Android.
        // See https://bugs.chromium.org/p/chromium/issues/detail?id=481856
        if (e instanceof Error) {
          if (e.name === "TypeError") {
            return;
          }
        }

        throw e;
      }

      notification.onclick = () => {
        window.focus();
        notification.close();

        window.location.href = pollData.notification.link;
      };
    }
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        actionName: "poll",
        className: "wcf\\data\\session\\SessionAction",
      },
      ignoreError: !window.ENABLE_DEBUG_MODE,
      silent: !window.ENABLE_DEBUG_MODE,
    };
  }
}

let notificationHandler: NotificationHandler;

/**
 * Initializes the desktop notification system.
 */
export function setup(options: NotificationHandlerOptions): void {
  if (!notificationHandler) {
    notificationHandler = new NotificationHandler(options);
  }
}

export function enableNotifications(): void {
  notificationHandler!.enableNotifications();
}

export function poll(): void {
  notificationHandler?.dispatchRequest();
}
