/**
 * Handles the display of an icon badge with customizable colors.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

export function setup(iconFieldId: string, colorFieldId: string, backgroundColorFieldId: string) {
  const container = document.getElementById(`${iconFieldId}_icon`)!;
  container.classList.add("iconBadge");

  if (colorFieldId !== "") {
    const colorInput = document.getElementById(colorFieldId) as HTMLInputElement;
    colorInput.addEventListener("color-picker:submit", () => {
      container.style.setProperty("color", colorInput.value);
    });

    container.style.setProperty("color", colorInput.value);
  }

  if (backgroundColorFieldId !== "") {
    const backgroundColorInput = document.getElementById(backgroundColorFieldId) as HTMLInputElement;
    backgroundColorInput.addEventListener("color-picker:submit", () => {
      container.style.setProperty("background-color", backgroundColorInput.value);
    });

    container.style.setProperty("background-color", backgroundColorInput.value);
  }
}
