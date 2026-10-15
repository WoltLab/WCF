/**
 * Enables editing of the icon, the icon color and the background color of a
 * badge trophy.
 *
 * @author    Alexander Ebert, Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.3
 * @woltlabExcludeBundle all
 */

import { open as openFontAwesomePicker } from "../../../../Ui/Style/FontAwesome";
import ColorPicker from "../../../../Ui/Color/Picker";

function getInput(fieldId: string, name: string): HTMLInputElement {
  const input = document.getElementById(`${fieldId}_${name}`);
  if (!(input instanceof HTMLInputElement)) {
    throw new Error(`Unknown input field '${name}' of the field '${fieldId}'.`);
  }

  return input;
}

function getButton(container: HTMLElement, value: string): HTMLButtonElement {
  return container.querySelector(`button[data-value="${value}"]`) as HTMLButtonElement;
}

function renderNativePreview(preview: HTMLElement, icon: string, forceSolid: boolean): void {
  let previewIcon = preview.querySelector("fa-icon");
  if (!(previewIcon instanceof HTMLElement) || previewIcon.parentElement !== preview) {
    preview.replaceChildren();

    previewIcon = document.createElement("fa-icon");
    previewIcon.size = 64;
    preview.append(previewIcon);
  }

  previewIcon.setIcon(icon, forceSolid);
}

function setupChangeIcon(container: HTMLElement, preview: HTMLElement, input: HTMLInputElement): void {
  getButton(container, "icon").addEventListener("click", () => {
    openFontAwesomePicker((icon, forceSolid, value, previewHtml) => {
      input.value = value ?? `${icon};${String(forceSolid)}`;

      if (typeof previewHtml === "string") {
        preview.innerHTML = previewHtml;
      } else {
        renderNativePreview(preview, icon, forceSolid);
      }
    });
  });
}

function setupColorPicker(
  button: HTMLButtonElement,
  preview: HTMLElement,
  input: HTMLInputElement,
  property: "color" | "background-color",
): void {
  button.dataset.store = input.id;

  new ColorPicker(button, {
    callbackSubmit() {
      preview.style.setProperty(property, input.value);
    },
  });
}

export function setup(fieldId: string): void {
  const container = document.getElementById(`${fieldId}_container`);
  if (container === null) {
    throw new Error(`Unknown field with id '${fieldId}'.`);
  }

  const preview = container.querySelector(".trophyIcon") as HTMLElement;

  setupChangeIcon(container, preview, getInput(fieldId, "iconName"));
  setupColorPicker(getButton(container, "color"), preview, getInput(fieldId, "iconColor"), "color");
  setupColorPicker(
    getButton(container, "background-color"),
    preview,
    getInput(fieldId, "badgeColor"),
    "background-color",
  );
}
