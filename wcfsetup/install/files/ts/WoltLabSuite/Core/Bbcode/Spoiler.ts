/**
 * Generic handler for spoiler boxes.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Bbcode/Spoiler
 */

import * as Core from "../Core";
import * as Language from "../Language";
import DomUtil from "../Dom/Util";

function onClick(event: Event, content: HTMLElement, toggleButton: HTMLAnchorElement): void {
  event.preventDefault();

  toggleButton.classList.toggle("active");

  const isActive = toggleButton.classList.contains("active");
  if (isActive) {
    DomUtil.show(content);
  } else {
    DomUtil.hide(content);
  }

  toggleButton.setAttribute("aria-expanded", isActive ? "true" : "false");
  content.setAttribute("aria-hidden", isActive ? "false" : "true");

  if (!Core.stringToBool(toggleButton.dataset.hasCustomLabel || "")) {
    toggleButton.textContent = Language.get(
      toggleButton.classList.contains("active") ? "wcf.bbcode.spoiler.hide" : "wcf.bbcode.spoiler.show",
    );
  }
}

export function observe(): void {
  const className = "jsSpoilerBox";
  document.querySelectorAll(`.${className}`).forEach((container: HTMLElement) => {
    container.classList.remove(className);

    const toggleButton = container.querySelector(".jsSpoilerToggle") as HTMLAnchorElement;
    const content = container.querySelector(".spoilerBoxContent") as HTMLElement;

    toggleButton.addEventListener("click", (ev) => onClick(ev, content, toggleButton));
  });
}
