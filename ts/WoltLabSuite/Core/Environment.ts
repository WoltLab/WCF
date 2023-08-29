/**
 * Provides basic details on the JavaScript environment.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

type Browser = "chrome" | "firefox" | "microsoft" | "other" | "safari";
type Platform = "android" | "desktop" | "ios" | "mobile" | "windows";

let _browser: Browser = "other";
let _platform: Platform = "desktop";
let _touch = false;

/**
 * Determines environment variables.
 */
export function setup(): void {
  if (typeof (window as any).chrome === "object") {
    // this detects Opera as well, we could check for window.opr if we need to
    _browser = "chrome";
  } else {
    const styles = window.getComputedStyle(document.documentElement);
    for (let i = 0, length = styles.length; i < length; i++) {
      const property = styles[i];

      if (property.indexOf("-ms-") === 0) {
        // it is tempting to use 'msie', but it wouldn't really represent 'Edge'
        _browser = "microsoft";
      } else if (property.indexOf("-moz-") === 0) {
        _browser = "firefox";
      } else if (_browser !== "firefox" && property.indexOf("-webkit-") === 0) {
        _browser = "safari";
      }
    }
  }

  const ua = window.navigator.userAgent.toLowerCase();
  if (ua.indexOf("crios") !== -1) {
    _browser = "chrome";
    _platform = "ios";
  } else if (/(?:iphone|ipad|ipod)/.test(ua)) {
    _browser = "safari";
    _platform = "ios";
  } else if (ua.indexOf("android") !== -1) {
    _platform = "android";
  } else if (ua.indexOf("iemobile") !== -1) {
    _browser = "microsoft";
    _platform = "windows";
  }

  if (_platform === "desktop" && (ua.indexOf("mobile") !== -1 || ua.indexOf("tablet") !== -1)) {
    _platform = "mobile";
  }

  _touch =
    "ontouchstart" in window ||
    ("msMaxTouchPoints" in window.navigator && (window.navigator as any).msMaxTouchPoints > 0) ||
    ((window as any).DocumentTouch && document instanceof (window as any).DocumentTouch);

  // The iPad Pro 12.9" masquerades as a desktop browser.
  if (window.navigator.platform === "MacIntel" && window.navigator.maxTouchPoints > 1) {
    _browser = "safari";
    _platform = "ios";
  }
}

/**
 * Returns the lower-case browser identifier.
 *
 * Possible values:
 *  - chrome: Chrome and Opera
 *  - firefox
 *  - microsoft: Internet Explorer and Microsoft Edge
 *  - safari
 */
export function browser(): Browser {
  return _browser;
}

/**
 * Returns the browser platform.
 *
 * Possible values:
 *  - desktop
 *  - android
 *  - ios: iPhone, iPad and iPod
 *  - windows: Windows on phones/tablets
 */
export function platform(): Platform {
  return _platform;
}

/**
 * Returns true if browser is potentially used with a touchscreen.
 *
 * Warning: Detecting touch is unreliable and should be avoided at all cost.
 */
export function touch(): boolean {
  return _touch;
}
