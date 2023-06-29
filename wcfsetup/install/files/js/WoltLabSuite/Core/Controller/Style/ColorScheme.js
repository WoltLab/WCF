/**
 * Offer users the ability to enforce a specific color scheme.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports", "WoltLabSuite/Core/Language", "../../Ui/Dropdown/Builder", "../../Ui/Dropdown/Simple"], function (require, exports, Language_1, Builder_1, Simple_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    let currentScheme = "system";
    let mediaQuery;
    let themeColor;
    function setScheme(scheme) {
        currentScheme = scheme;
        if (currentScheme === "light" || currentScheme === "dark") {
            document.documentElement.dataset.colorScheme = currentScheme;
            updateThemeColor();
        }
        else {
            applySystemScheme();
        }
        try {
            localStorage.setItem("wsc_colorScheme", currentScheme);
        }
        catch {
            /* Ignore any errors when accessing the `localStorage`. */
        }
    }
    function applySystemScheme() {
        if (currentScheme === "system") {
            document.documentElement.dataset.colorScheme = mediaQuery.matches ? "dark" : "light";
            updateThemeColor();
        }
    }
    function updateThemeColor() {
        themeColor.content = window.getComputedStyle(document.body).getPropertyValue("--wcfPageThemeColor");
    }
    function initializeButton(button) {
        const dropdownMenu = (0, Builder_1.create)([
            {
                identifier: "light",
                label: (0, Language_1.getPhrase)("wcf.style.setColorScheme.light"),
                callback() {
                    setScheme("light");
                },
            },
            {
                identifier: "dark",
                label: (0, Language_1.getPhrase)("wcf.style.setColorScheme.dark"),
                callback() {
                    setScheme("dark");
                },
            },
            "divider",
            {
                identifier: "system",
                label: (0, Language_1.getPhrase)("wcf.style.setColorScheme.system"),
                callback() {
                    setScheme("system");
                },
            },
        ]);
        (0, Builder_1.attach)(dropdownMenu, button);
        (0, Simple_1.registerCallback)(button.id, (_containerId, action) => {
            if (action === "open") {
                dropdownMenu.querySelectorAll(".active").forEach((element) => element.classList.remove("active"));
                dropdownMenu.querySelector(`[data-identifier="${currentScheme}"]`).classList.add("active");
            }
        });
    }
    function setup() {
        const button = document.querySelector(".jsButtonStyleColorScheme");
        if (button) {
            initializeButton(button);
        }
        try {
            const value = localStorage.getItem("wsc_colorScheme");
            if (value === "light" || value === "dark") {
                currentScheme = value;
            }
        }
        catch {
            /* Ignore any errors when accessing the `localStorage`. */
        }
        themeColor = document.querySelector('meta[name="theme-color"]');
        mediaQuery = matchMedia("(prefers-color-scheme: dark)");
        mediaQuery.addEventListener("change", () => {
            applySystemScheme();
        });
    }
    exports.setup = setup;
});
