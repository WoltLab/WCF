/**
 * Asks for confirmation before deleting a TOTP device.
 *
 * @author  Marcel Werk
 * @copyright  2001-2026 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since  6.3
 * @woltlabExcludeBundle  all
 */

import { confirmationFactory } from "WoltLabSuite/Core/Component/Confirmation";

export function setup(container: HTMLElement): void {
  // The listener is registered in the capture phase of the container to run
  // before any listeners of the button itself, e.g. the `IsNotClicked` dependency.
  container.addEventListener(
    "click",
    (event) => {
      const button = (event.target as HTMLElement).closest<HTMLButtonElement>(".jsTotpDeviceDelete");
      if (button === null) {
        return;
      }

      if (button.dataset.confirmed === "1") {
        return;
      }

      event.preventDefault();
      event.stopImmediatePropagation();

      void confirmationFactory()
        .delete(button.dataset.deviceName)
        .then((result) => {
          if (!result) {
            return;
          }

          button.dataset.confirmed = "1";
          button.click();
        });
    },
    { capture: true },
  );
}
