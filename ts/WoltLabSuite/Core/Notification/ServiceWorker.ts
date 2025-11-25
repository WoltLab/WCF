/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 * @woltlabExcludeBundle tiny
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { updateLastRequestTimestamp } from "WoltLabSuite/Core/Notification/Handler";

let _serviceWorker: ServiceWorker | null = null;

class ServiceWorker {
  readonly #publicKey: string;
  readonly #serviceWorkerJsUrl: string;
  readonly #registerUrl: string;
  readonly #serviceWorkerRegistration: Promise<ServiceWorkerRegistration>;

  constructor(publicKey: string, serviceWorkerJsUrl: string, registerUrl: string) {
    this.#publicKey = publicKey;
    this.#serviceWorkerJsUrl = serviceWorkerJsUrl;
    this.#registerUrl = registerUrl;
    void window.navigator.serviceWorker.register(this.#serviceWorkerJsUrl, {
      scope: "/",
    });
    this.#serviceWorkerRegistration = window.navigator.serviceWorker.ready;

    window.navigator.serviceWorker.addEventListener("message", (event) => {
      // Validate that this is a message from our service worker
      if (!(event.source instanceof window.ServiceWorker) || event.source.scriptURL !== this.#serviceWorkerJsUrl) {
        return;
      }

      updateLastRequestTimestamp(event.data.time);
    });
  }

  async register(): Promise<void> {
    const currentSubscription = await (await this.#serviceWorkerRegistration).pushManager.getSubscription();
    if (currentSubscription && this.#compareApplicationServerKey(currentSubscription)) {
      return;
    }
    await this.unsubscribe(currentSubscription);
    const subscription = await (
      await this.#serviceWorkerRegistration
    ).pushManager.subscribe({
      userVisibleOnly: true,
      // The typings for buffers conflict with an implicit dependency on node.
      applicationServerKey: this.#urlBase64ToUint8Array(this.#publicKey) as BufferSource,
    });
    if (!subscription) {
      // subscription failed
      return;
    }
    await this.#sendRequest(subscription);
  }

  async unsubscribe(subscription: PushSubscription | null): Promise<void> {
    if (subscription) {
      await this.#sendRequest(subscription, true);
      await subscription.unsubscribe();
    }
  }

  #compareApplicationServerKey(subscription: PushSubscription): boolean {
    let base64 = window.btoa(String.fromCharCode(...new Uint8Array(subscription.options.applicationServerKey!)));
    base64 = base64.replace(/\+/g, "-").replace(/\//g, "_");
    base64 = base64.replace(/=+$/, "");

    return base64 === this.#publicKey;
  }

  async #sendRequest(subscription: PushSubscription, remove: boolean = false): Promise<void> {
    const key = subscription.getKey("p256dh");
    const token = subscription.getKey("auth");
    // aes128gcm must be supported from browser
    // @see https://w3c.github.io/push-api/#dom-pushmanager-supportedcontentencodings
    const contentEncoding = (PushManager.supportedContentEncodings || ["aes128gcm"])[0];
    try {
      await prepareRequest(this.#registerUrl)
        .post({
          remove: remove,
          endpoint: subscription.endpoint,
          publicKey: key ? window.btoa(String.fromCharCode(...new Uint8Array(key))) : null,
          authToken: token ? window.btoa(String.fromCharCode(...new Uint8Array(token))) : null,
          contentEncoding: contentEncoding,
        })
        .disableLoadingIndicator()
        .fetchAsResponse();
    } catch {
      // ignore registration errors
    }
  }

  //@see https://github.com/mdn/serviceworker-cookbook/blob/master/tools.js
  #urlBase64ToUint8Array(base64String: string): Uint8Array {
    const padding = "=".repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, "+").replace(/_/g, "/");

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  }

  public updateNotificationLastReadTime(timestamp: number): void {
    window.navigator.serviceWorker.controller?.postMessage({
      type: "UPDATE_NOTIFICATION_LAST_READ_TIME",
      timestamp,
    });
  }
}

export function serviceWorkerSupported(): boolean {
  if (location.protocol !== "https:") {
    // Service workers are only available on https
    return false;
  }

  if (!("serviceWorker" in window.navigator)) {
    return false;
  }

  if (!("PushManager" in window)) {
    return false;
  }

  if (!("showNotification" in ServiceWorkerRegistration.prototype)) {
    return false;
  }
  if ("Notification" in window && Notification.permission === "denied") {
    return false;
  }
  return true;
}

export function setup(
  publicKey: string,
  serviceWorkerJsUrl: string,
  registerUrl: string,
  notificationLastReadTime: number,
): void {
  if (!serviceWorkerSupported()) {
    return;
  }
  _serviceWorker = new ServiceWorker(publicKey, serviceWorkerJsUrl, registerUrl);
  if (Notification.permission === "granted") {
    registerServiceWorker();
    _serviceWorker.updateNotificationLastReadTime(notificationLastReadTime);
  }
}

export function registerServiceWorker(): void {
  void _serviceWorker?.register();
}

export function updateNotificationLastReadTime(timestamp?: number): void {
  _serviceWorker?.updateNotificationLastReadTime(timestamp ?? Math.round(Date.now() / 1000));
}
