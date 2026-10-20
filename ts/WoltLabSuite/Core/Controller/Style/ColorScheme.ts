/**
 * Applies the preferred color scheme and keeps `system` in sync with the device.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

export type ColorSchemePreference = "dark" | "light" | "system";

// Must match the inline script in `headIncludeJavaScript.tpl`.
const STORAGE_KEY = "wsc_colorScheme";

const mediaQuery = window.matchMedia("(prefers-color-scheme: dark)");

function applyColorScheme(colorScheme: "dark" | "light"): void {
  document.documentElement.dataset.colorScheme = colorScheme;

  const themeColor = document.querySelector<HTMLMetaElement>('meta[name="theme-color"]')!;
  themeColor.content = window.getComputedStyle(document.body).getPropertyValue("--wcfPageThemeColor");
}

function followDevice(event: MediaQueryListEvent): void {
  applyColorScheme(event.matches ? "dark" : "light");
}

/**
 * Returns the color scheme a guest has picked, members store it in their account instead.
 *
 * @since 6.3
 */
export function getStoredColorScheme(): ColorSchemePreference | undefined {
  try {
    const value = window.localStorage.getItem(STORAGE_KEY);
    if (value === "dark" || value === "light") {
      return value;
    }
  } catch {
    // The storage is unavailable, e.g. in private mode.
  }

  return undefined;
}

/**
 * @since 6.3
 */
export function storeColorScheme(colorScheme: ColorSchemePreference): void {
  try {
    if (colorScheme === "system") {
      window.localStorage.removeItem(STORAGE_KEY);
    } else {
      window.localStorage.setItem(STORAGE_KEY, colorScheme);
    }
  } catch {
    // The storage is unavailable, e.g. in private mode.
  }
}

/**
 * @since 6.3
 */
export function setColorScheme(colorScheme: ColorSchemePreference): void {
  mediaQuery.removeEventListener("change", followDevice);

  if (colorScheme === "system") {
    mediaQuery.addEventListener("change", followDevice);
    applyColorScheme(mediaQuery.matches ? "dark" : "light");
  } else {
    applyColorScheme(colorScheme);
  }
}

export function setup(): void {
  setColorScheme("system");
}
