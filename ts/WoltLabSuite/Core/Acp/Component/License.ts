/**
 * Offers to install packages from the list of licensed products.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import AcpUiPackagePrepareInstallation from "../Ui/Package/PrepareInstallation";

function installPackage(button: HTMLButtonElement): Promise<void> {
  const installation = new AcpUiPackagePrepareInstallation();
  return installation.start(button.dataset.package!, button.dataset.packageVersion!);
}

export function setup(): void {
  const callback = promiseMutex((button: HTMLButtonElement) => installPackage(button));
  document.querySelectorAll<HTMLButtonElement>(".jsInstallPackage").forEach((button) => {
    button.addEventListener("click", () => {
      callback(button);
    });
  });
}
