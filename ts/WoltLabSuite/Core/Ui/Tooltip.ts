/**
 * Provides enhanced tooltips.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import * as Environment from "../Environment";
import { getPageOverlayContainer } from "../Helper/PageOverlay";
import { wheneverSeen } from "../Helper/Selector";
import * as UiAlignment from "./Alignment";

let _text: HTMLElement;
let _tooltip: HTMLElement;

/**
 * Displays the tooltip on mouse enter.
 */
function mouseEnter(event: MouseEvent): void {
  const element = event.currentTarget as HTMLElement;

  let title = element.title.trim();
  if (title !== "") {
    element.dataset.tooltip = title;
    element.setAttribute("aria-label", title);
    element.removeAttribute("title");
  }

  title = element.dataset.tooltip || "";

  // reset tooltip position
  _tooltip.style.removeProperty("top");
  _tooltip.style.removeProperty("left");

  // ignore empty tooltip
  if (!title.length) {
    _tooltip.classList.remove("active");
    return;
  } else {
    _tooltip.classList.add("active");
  }

  _text.textContent = title;
  UiAlignment.set(_tooltip, element, {
    horizontal: "center",
    verticalOffset: 4,
    vertical: "top",
  });
}

/**
 * Hides the tooltip once the mouse leaves the element.
 */
function mouseLeave(): void {
  _tooltip.classList.remove("active");
}

/**
 * Initializes the tooltip element and binds event listener.
 */
export function setup(): void {
  if (Environment.platform() !== "desktop") {
    return;
  }

  _tooltip = document.createElement("div");
  _tooltip.id = "balloonTooltip";
  _tooltip.classList.add("balloonTooltip");
  _tooltip.addEventListener("transitionend", () => {
    if (!_tooltip.classList.contains("active")) {
      // reset back to the upper left corner, prevent it from staying outside
      // the viewport if the body overflow was previously hidden
      ["bottom", "left", "right", "top"].forEach((property) => {
        _tooltip.style.removeProperty(property);
      });
    }
  });

  _text = document.createElement("span");
  _text.id = "balloonTooltipText";
  _tooltip.appendChild(_text);

  getPageOverlayContainer().append(_tooltip);

  wheneverSeen(".jsTooltip", (element) => {
    element.classList.remove("jsTooltip");

    const title = element.title.trim();
    if (title.length) {
      element.dataset.tooltip = title;
      element.removeAttribute("title");
      element.setAttribute("aria-label", title);

      element.addEventListener("mouseenter", mouseEnter);
      element.addEventListener("mouseleave", mouseLeave);
      element.addEventListener("click", mouseLeave);
    }
  });

  window.addEventListener("scroll", mouseLeave);
}
