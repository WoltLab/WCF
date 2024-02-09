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
    //check if service worker is already registered
    if (navigator.serviceWorker.controller) {
      //TODO
      //return;
    }
  }

  async register(): Promise<void> {
    await navigator.serviceWorker.register(this.serviceWorkerJsUrl, { scope: "/" });
    const serviceWorkerRegistration = await navigator.serviceWorker.ready;
    await serviceWorkerRegistration.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: this.#base64ToUint8Array(this.publicKey),
    });
    const subscription = await serviceWorkerRegistration.pushManager.getSubscription();
    console.log(subscription);
  }

  #base64ToUint8Array(base64String: string): Uint8Array {
    const padding = "=".repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, "+").replace(/_/g, "/");
    return Uint8Array.from(atob(base64), (c) => c.charCodeAt(0));
  }
}

function serviceWorkerSupported(): boolean {
  if (!("serviceWorker" in navigator)) {
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
  if (_serviceWorker === null) {
    return;
  }
  void _serviceWorker.register();
}
