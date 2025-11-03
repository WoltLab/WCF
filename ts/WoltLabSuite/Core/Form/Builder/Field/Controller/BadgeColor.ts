/**
 * @author    Alexander Ebert
 * @copyright 2001-2025 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.2
 * @woltlabExcludeBundle all
 */

function updatePreview(inputField: HTMLInputElement, fields: HTMLElement[], placeholder: string): void {
  window.requestAnimationFrame(() => {
    const value = inputField.value.trim() || placeholder;
    for (const field of fields) {
      field.textContent = value;
    }
  });
}

export function setup(containerId: string, inputFieldId: string, placeholder: string): void {
  const container = document.getElementById(containerId);
  if (container === null) {
    throw new Error(`Unknown container with id '${containerId}'.`);
  }

  const inputField = document.getElementById(inputFieldId);
  if (inputField === null) {
    throw new Error(`Unknown input field with id '${inputFieldId}'.`);
  } else if (!(inputField instanceof HTMLInputElement)) {
    throw new Error("Expected the input field to be an <input> element.", { cause: { inputField } });
  }

  const fields = Array.from(
    container.querySelectorAll<HTMLElement>(".labelSelection__span:not(.labelSelection__span--custom)"),
  );
  if (fields.length === 0) {
    throw new Error("The container does not contain any fields.", { cause: { container } });
  }

  inputField.addEventListener(
    "input",
    () => {
      updatePreview(inputField, fields, placeholder);
    },
    { passive: true },
  );
}
