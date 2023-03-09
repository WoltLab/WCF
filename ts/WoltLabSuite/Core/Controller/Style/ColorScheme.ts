/**
 * Offer users the ability to enforce a specific color scheme.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import { getPhrase } from "WoltLabSuite/Core/Language";
import { attach, create } from "../../Ui/Dropdown/Builder";
import { registerCallback } from "../../Ui/Dropdown/Simple";

export type ColorScheme = "dark" | "light" | "system";

let currentScheme: ColorScheme = "system";
let mediaQuery: MediaQueryList;

function setScheme(scheme: ColorScheme): void {
  currentScheme = scheme;

  if (currentScheme === "light" || currentScheme === "dark") {
    document.documentElement.dataset.colorScheme = currentScheme;
  } else {
    applySystemScheme();
  }

  try {
    localStorage.setItem("wsc_colorScheme", currentScheme);
  } catch {
    /* Ignore any errors when accessing the `localStorage`. */
  }
}

function applySystemScheme(): void {
  if (currentScheme === "system") {
    document.documentElement.dataset.colorScheme = mediaQuery.matches ? "dark" : "light";
  }
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

export function setup(): void {
  const button = document.querySelector<HTMLElement>(".jsButtonStyleColorScheme");
  if (button) {
    initializeButton(button);
  }

  try {
    const value = localStorage.getItem("wsc_colorScheme");
    if (value === "light" || value === "dark") {
      currentScheme = value;
    }
  } catch {
    /* Ignore any errors when accessing the `localStorage`. */
  }

  mediaQuery = matchMedia("(prefers-color-scheme: dark)");
  mediaQuery.addEventListener("change", () => {
    applySystemScheme();
  });
}
