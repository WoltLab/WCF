/**
 * Enables editing of the badge icon, color and
 * background-color.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Acp/Ui/Trophy/Badge
 */

import { open as openFontAwesomePicker } from "../../../Ui/Style/FontAwesome";
import ColorPicker from "../../../Ui/Color/Picker";
import DomUtil from "../../../Dom/Util";

const badgeContainer = document.getElementById("badgeContainer")!;
const previewWrapper = badgeContainer.querySelector(".trophyIcon") as HTMLElement;
const previewIcon = previewWrapper.querySelector("fa-icon")!;

function setupChangeIcon(): void {
  const button = badgeContainer.querySelector('.trophyIconEditButton[data-value="icon"]') as HTMLButtonElement;
  const input = badgeContainer.querySelector('input[name="iconName"]') as HTMLInputElement;

  button.addEventListener("click", () => {
    openFontAwesomePicker((icon, forceSolid) => {
      previewIcon.setIcon(icon, forceSolid);
      input.value = `${icon};${String(forceSolid)}`;
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
