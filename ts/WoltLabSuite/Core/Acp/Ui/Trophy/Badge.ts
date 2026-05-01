/**
 * Enables editing of the badge icon, color and
 * background-color.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import { open as openFontAwesomePicker } from "../../../Ui/Style/FontAwesome";
import ColorPicker from "../../../Ui/Color/Picker";
import DomUtil from "../../../Dom/Util";

const badgeContainer = document.getElementById("badgeContainer")!;
const previewWrapper = badgeContainer.querySelector(".trophyIcon") as HTMLElement;

function renderNativePreview(icon: string, forceSolid: boolean): void {
  let previewIcon = previewWrapper.querySelector("fa-icon");
  if (!(previewIcon instanceof HTMLElement) || previewIcon.parentElement !== previewWrapper) {
    previewWrapper.replaceChildren();

    previewIcon = document.createElement("fa-icon");
    previewIcon.size = 64;
    previewWrapper.append(previewIcon);
  }

  previewIcon.setIcon(icon, forceSolid);
}

function setupChangeIcon(): void {
  const button = badgeContainer.querySelector('.trophyIconEditButton[data-value="icon"]') as HTMLButtonElement;
  const input = badgeContainer.querySelector('input[name="iconName"]') as HTMLInputElement;

  button.addEventListener("click", () => {
    openFontAwesomePicker((icon, forceSolid, value, previewHtml) => {
      input.value = value ?? `${icon};${String(forceSolid)}`;

      if (typeof previewHtml === "string") {
        previewWrapper.innerHTML = previewHtml;
      } else {
        renderNativePreview(icon, forceSolid);
      }
    });
  });
}

function setupChangeColor(): void {
  const button = badgeContainer.querySelector('.trophyIconEditButton[data-value="color"]') as HTMLButtonElement;

  const input = badgeContainer.querySelector('input[name="iconColor"]') as HTMLInputElement;
  button.dataset.store = DomUtil.identify(input);

  new ColorPicker(button, {
    callbackSubmit() {
      previewWrapper.style.setProperty("color", input.value);
    },
  });
}

function setupChangeBackgroundColor(): void {
  const button = badgeContainer.querySelector(
    '.trophyIconEditButton[data-value="background-color"]',
  ) as HTMLButtonElement;

  const input = badgeContainer.querySelector('input[name="badgeColor"]') as HTMLInputElement;
  button.dataset.store = DomUtil.identify(input);

  new ColorPicker(button, {
    callbackSubmit() {
      previewWrapper.style.setProperty("background-color", input.value);
    },
  });
}

export function setup(): void {
  setupChangeIcon();
  setupChangeColor();
  setupChangeBackgroundColor();
}
