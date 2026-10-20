/**
 * Applies the preferred color scheme and keeps `system` in sync with the device.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getStoredColorScheme = getStoredColorScheme;
    exports.storeColorScheme = storeColorScheme;
    exports.setColorScheme = setColorScheme;
    exports.setup = setup;
    // Must match the inline script in `headIncludeJavaScript.tpl`.
    const STORAGE_KEY = "wsc_colorScheme";
    const mediaQuery = window.matchMedia("(prefers-color-scheme: dark)");
    function applyColorScheme(colorScheme) {
        document.documentElement.dataset.colorScheme = colorScheme;
        const themeColor = document.querySelector('meta[name="theme-color"]');
        themeColor.content = window.getComputedStyle(document.body).getPropertyValue("--wcfPageThemeColor");
    }
    function followDevice(event) {
        applyColorScheme(event.matches ? "dark" : "light");
    }
    /**
     * Returns the color scheme a guest has picked, members store it in their account instead.
     *
     * @since 6.3
     */
    function getStoredColorScheme() {
        try {
            const value = window.localStorage.getItem(STORAGE_KEY);
            if (value === "dark" || value === "light") {
                return value;
            }
        }
        catch {
            // The storage is unavailable, e.g. in private mode.
        }
        return undefined;
    }
    /**
     * @since 6.3
     */
    function storeColorScheme(colorScheme) {
        try {
            if (colorScheme === "system") {
                window.localStorage.removeItem(STORAGE_KEY);
            }
            else {
                window.localStorage.setItem(STORAGE_KEY, colorScheme);
            }
        }
        catch {
            // The storage is unavailable, e.g. in private mode.
        }
    }
    /**
     * @since 6.3
     */
    function setColorScheme(colorScheme) {
        mediaQuery.removeEventListener("change", followDevice);
        if (colorScheme === "system") {
            mediaQuery.addEventListener("change", followDevice);
            applyColorScheme(mediaQuery.matches ? "dark" : "light");
        }
        else {
            applyColorScheme(colorScheme);
        }
    }
    function setup() {
        setColorScheme("system");
    }
});
