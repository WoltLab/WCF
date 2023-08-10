/**
 * Offer users the ability to enforce a specific color scheme.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @woltlabExcludeBundle tiny
 */

import { getPhrase } from "WoltLabSuite/Core/Language";
import { attach, create } from "../../Ui/Dropdown/Builder";
import { registerCallback } from "../../Ui/Dropdown/Simple";

export type ColorScheme = "dark" | "light" | "system";

let currentScheme: ColorScheme = "system";
let mediaQuery: MediaQueryList;
let themeColor: HTMLMetaElement;

function setScheme(scheme: ColorScheme): void {
  currentScheme = scheme;

  if (currentScheme === "light" || currentScheme === "dark") {
    document.documentElement.dataset.colorScheme = currentScheme;
    updateThemeColor();
  } else {
    applySystemScheme();
  }
}

function applySystemScheme(): void {
  if (currentScheme === "system") {
    document.documentElement.dataset.colorScheme = mediaQuery.matches ? "dark" : "light";
    updateThemeColor();
  }
}

function updateThemeColor(): void {
  themeColor.content = window.getComputedStyle(document.body).getPropertyValue("--wcfPageThemeColor");
}

function initializeButton(button: HTMLElement): void {
  const dropdownMenu = create([
    {
      identifier: "light",
      label: getPhrase("wcf.style.setColorScheme.light"),
      callback() {
        setScheme("light");
      },
    },
    {
      identifier: "dark",
      label: getPhrase("wcf.style.setColorScheme.dark"),
      callback() {
        setScheme("dark");
      },
    },
    "divider",
    {
      identifier: "system",
      label: getPhrase("wcf.style.setColorScheme.system"),
      callback() {
        setScheme("system");
      },
    },
  ]);

  attach(dropdownMenu, button);

  registerCallback(button.id, (_containerId, action) => {
    if (action === "open") {
      dropdownMenu.querySelectorAll(".active").forEach((element) => element.classList.remove("active"));
      dropdownMenu.querySelector(`[data-identifier="${currentScheme}"]`)!.classList.add("active");
    }
  });
}

export function setup(colorScheme: ColorScheme): void {
  const button = document.querySelector<HTMLElement>(".jsButtonStyleColorScheme");
  if (button) {
    //initializeButton(button);
  }

  currentScheme = colorScheme;
  themeColor = document.querySelector('meta[name="theme-color"]')!;

  mediaQuery = matchMedia("(prefers-color-scheme: dark)");
  mediaQuery.addEventListener("change", () => {
    applySystemScheme();
  });
}
