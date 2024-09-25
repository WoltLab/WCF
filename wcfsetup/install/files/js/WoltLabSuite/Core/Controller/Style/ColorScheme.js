/**
 * Dynamically updates the color scheme to match the system preference.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    function setup() {
        const themeColor = document.querySelector('meta[name="theme-color"]');
        themeColor.content = window.getComputedStyle(document.body).getPropertyValue("--wcfPageThemeColor");
        window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", (event) => {
            document.documentElement.dataset.colorScheme = event.matches ? "dark" : "light";
            themeColor.content = window.getComputedStyle(document.body).getPropertyValue("--wcfPageThemeColor");
        });
    }
});
