/**
 * Handles the display of an icon badge with customizable colors.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

export class IconBadge {
  #iconContainer: HTMLElement;
  #colorInput?: HTMLInputElement;
  #backgroundColorInput?: HTMLInputElement;

  constructor(iconFieldId: string, colorFieldId?: string, backgroundColorFieldId?: string) {
    this.#iconContainer = document.getElementById(`${iconFieldId}_icon`)!;
    this.#iconContainer.classList.add("iconBadge");

    const observer = new MutationObserver((mutationsList) => {
      for (const mutation of mutationsList) {
        if (mutation.type === "attributes" && mutation.attributeName === "value") {
          this.#updateIcon();
        }
      }
    });

    if (colorFieldId) {
      this.#colorInput = document.getElementById(colorFieldId) as HTMLInputElement;

      observer.observe(this.#colorInput, {
        attributes: true,
        attributeFilter: ["value"],
      });
    }

    if (backgroundColorFieldId) {
      this.#backgroundColorInput = document.getElementById(backgroundColorFieldId) as HTMLInputElement;

      observer.observe(this.#backgroundColorInput, {
        attributes: true,
        attributeFilter: ["value"],
      });
    }

    this.#updateIcon();
  }

  #updateIcon(): void {
    this.#iconContainer.style.color = this.#colorInput?.value || "";
    this.#iconContainer.style.backgroundColor = this.#backgroundColorInput?.value || "";
  }
}
