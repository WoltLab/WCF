/**
 * Positions the popover of `<woltlab-core-label-picker>` relative to its
 * toggle button while it is open.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { wheneverFirstSeen } from "../Helper/Selector";
import * as UiAlignment from "../Ui/Alignment";

function setupPicker(picker: WoltlabCoreLabelPickerElement): void {
  let popover: HTMLElement | undefined = undefined;

  const updatePosition = (event?: Event) => {
    if (popover === undefined) {
      return;
    }

    // Removing the picker from the DOM hides the popover without any events.
    if (!popover.matches(":popover-open")) {
      stopTracking();
      return;
    }

    // Scrolling the list of labels does not affect the position.
    if (event?.type === "scroll" && popover.contains(event.target as Node)) {
      return;
    }

    const button = picker.querySelector<HTMLElement>(":scope > .labelPicker__toggle")!;

    // The popover is at least as wide as the toggle button.
    popover.style.setProperty("min-width", `${button.offsetWidth}px`);

    UiAlignment.set(popover, button, {
      verticalOffset: 4,
    });
  };

  const stopTracking = () => {
    popover = undefined;

    window.removeEventListener("resize", updatePosition);
    window.removeEventListener("scroll", updatePosition, { capture: true });
  };

  // The popover is created by the web component, which might not have been
  // upgraded yet. `beforetoggle` does not bubble, but can be captured.
  picker.addEventListener(
    "beforetoggle",
    (event) => {
      const target = event.target as HTMLElement;
      if (!target.classList.contains("labelPicker__popover")) {
        return;
      }

      if (event.newState === "open") {
        popover = target;

        // The popover is shown after this event, position it before the next paint.
        window.requestAnimationFrame(() => updatePosition());

        window.addEventListener("resize", updatePosition, { passive: true });
        window.addEventListener("scroll", updatePosition, { capture: true, passive: true });
      } else {
        stopTracking();
      }
    },
    { capture: true },
  );

  // The size of the toggle button depends on the selected labels.
  picker.addEventListener("change", () => updatePosition());
}

export function setup(): void {
  wheneverFirstSeen("woltlab-core-label-picker", (picker: WoltlabCoreLabelPickerElement) => {
    setupPicker(picker);
  });
}
