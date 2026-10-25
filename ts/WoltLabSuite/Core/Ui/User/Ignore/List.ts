/**
 * Shows the ignore dialogs when editing users on the page listing ignored users.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright  2001-2026 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */

import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import { showDefaultSuccessSnackbar } from "WoltLabSuite/Core/Component/Snackbar";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";

type Response = {
  type: number;
};

export class UiUserIgnoreList {
  constructor() {
    document.querySelectorAll<HTMLElement>(".jsEditIgnoreButton").forEach((button) => {
      button.addEventListener(
        "click",
        promiseMutex(() => this.openDialog(button)),
      );
    });
  }

  protected async openDialog(button: HTMLElement): Promise<void> {
    const { ok, result } = await dialogFactory().usingFormBuilder().fromEndpoint<Response>(button.dataset.endpoint!);
    if (!ok) {
      return;
    }

    showDefaultSuccessSnackbar();

    if (!result.type) {
      button.closest(".jsIgnoredUser")!.remove();
    }
  }
}

export default UiUserIgnoreList;
