import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";

/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */
let _serviceWorker: ServiceWorker | null = null;

class ServiceWorker {
  private readonly publicKey: string;
  private readonly serviceWorkerJsUrl: string;
  private readonly registerUrl: string;

  constructor(publicKey: string, serviceWorkerJsUrl: string, registerUrl: string) {
    this.publicKey = publicKey;
    this.serviceWorkerJsUrl = serviceWorkerJsUrl;
    this.registerUrl = registerUrl;
    // TODO check if service worker is already registered
  }

  async register(): Promise<void> {
    try {
      await window.navigator.serviceWorker.register(this.serviceWorkerJsUrl, { scope: "/" });
      const serviceWorkerRegistration = await window.navigator.serviceWorker.ready;
      await serviceWorkerRegistration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: this.#urlBase64ToUint8Array(this.publicKey),
      });
      const subscription = await serviceWorkerRegistration.pushManager.getSubscription();
      if (!subscription) {
        // subscription failed
        return;
      }

      const key = subscription.getKey("p256dh");
      const token = subscription.getKey("auth");
      const contentEncoding = (PushManager.supportedContentEncodings || ["aesgcm"])[0];

      try {
        await prepareRequest(this.registerUrl)
          .post({
            endpoint: subscription.endpoint,
            publicKey: key ? window.btoa(String.fromCharCode(...new Uint8Array(key))) : null,
            authToken: token ? window.btoa(String.fromCharCode(...new Uint8Array(token))) : null,
            contentEncoding,
          })
          .disableLoadingIndicator()
          .fetchAsResponse();
      } catch (_) {
        // ignore registration errors
      }
    } catch (_) {
      // Server keys has possible changed
      await this.unregister();
    }
  }

  async unregister(): Promise<void> {
    const serviceWorkerRegistration = await window.navigator.serviceWorker.ready;
    const subscription = await serviceWorkerRegistration.pushManager.getSubscription();
    if (subscription) {
      await subscription.unsubscribe();
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
}

function serviceWorkerSupported(): boolean {
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

export function init(publicKey: string, serviceWorkerJsUrl: string, registerUrl: string): void {
  if (!serviceWorkerSupported()) {
    return;
  }
  _serviceWorker = new ServiceWorker(publicKey, serviceWorkerJsUrl, registerUrl);
  if (Notification.permission === "granted") {
    registerServiceWorker();
  }
}

export function registerServiceWorker(): void {
  void _serviceWorker?.register();
}
