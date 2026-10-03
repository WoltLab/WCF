/**
 * Edits the profile of a user in a dialog.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import { showDefaultSuccessSnackbar } from "WoltLabSuite/Core/Component/Snackbar";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";

async function editProfile(endpoint: string, onSuccess: () => Promise<void>): Promise<void> {
  const { ok } = await dialogFactory().usingFormBuilder().fromEndpoint(endpoint);
  if (!ok) {
    return;
  }

  await onSuccess();

  showDefaultSuccessSnackbar();
}

export function setup(button: HTMLElement, editOnInit: boolean, onSuccess: () => Promise<void>): void {
  const endpoint = button.dataset.endpoint!;

  const openDialog = promiseMutex(() => editProfile(endpoint, onSuccess));
  button.addEventListener("click", () => openDialog());

  if (editOnInit) {
    openDialog();
  }
}
