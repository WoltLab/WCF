/**
 * Edits the profile of a user in a dialog and reloads the "About" tab afterwards.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import { showDefaultSuccessSnackbar } from "WoltLabSuite/Core/Component/Snackbar";
import { reloadTabMenuContent } from "WoltLabSuite/Core/Controller/User/Profile";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { getTabMenu } from "WoltLabSuite/Core/Ui/TabMenu";

async function editProfile(userId: number, endpoint: string): Promise<void> {
  const { ok } = await dialogFactory().usingFormBuilder().fromEndpoint(endpoint);
  if (!ok) {
    return;
  }

  // Reload the tab before selecting it, otherwise the select event would load
  // the content of a tab that has not been loaded yet a second time.
  await reloadTabMenuContent(userId, "about");
  getTabMenu("profileContent")?.select("about");

  showDefaultSuccessSnackbar();
}

export function setup(button: HTMLElement, userId: number, editOnInit: boolean): void {
  const endpoint = button.dataset.endpoint!;

  const openDialog = promiseMutex(() => editProfile(userId, endpoint));
  button.addEventListener("click", () => openDialog());

  if (editOnInit) {
    openDialog();
  }
}
