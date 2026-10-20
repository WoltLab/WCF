/**
 * Switches the color scheme from the page footer. Members store the choice in
 * their account, guests in the browser.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import User from "../../User";
import {
  ColorSchemePreference,
  getStoredColorScheme,
  setColorScheme,
  storeColorScheme,
} from "../../Controller/Style/ColorScheme";
import { setColorScheme as saveColorScheme } from "../../Api/Users/SetColorScheme";

let isSaving = false;
let pendingColorScheme: ColorSchemePreference | undefined;

// Saves one request at a time and skips intermediate values, so a late response
// cannot overwrite a newer choice, e.g. while stepping through the radios.
async function save(colorScheme: ColorSchemePreference): Promise<void> {
  pendingColorScheme = colorScheme;
  if (isSaving) {
    return;
  }

  isSaving = true;
  try {
    while (pendingColorScheme !== undefined) {
      const next = pendingColorScheme;
      pendingColorScheme = undefined;
      await saveColorScheme(next);
    }
  } finally {
    isSaving = false;
  }
}

export function setup(): void {
  const fieldset = document.querySelector<HTMLFieldSetElement>(".pageFooterScheme");
  if (fieldset === null) {
    return;
  }

  // The server does not know the choice of a guest.
  if (User.userId === 0) {
    const colorScheme = getStoredColorScheme() ?? "system";
    fieldset.querySelector<HTMLInputElement>(`input[value="${colorScheme}"]`)!.checked = true;
  }

  fieldset.addEventListener("change", (event) => {
    const colorScheme = (event.target as HTMLInputElement).value as ColorSchemePreference;
    setColorScheme(colorScheme);

    if (User.userId === 0) {
      storeColorScheme(colorScheme);
    } else {
      void save(colorScheme);
    }
  });
}
