/**
 * Handles the button to search for package updates.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import {
  Response as ResponseSearchForUpdates,
  searchForUpdates as apiSearchForUpdates,
} from "WoltLabSuite/Core/Api/Packages/Updates/SearchForUpdates";
import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import type WoltlabCoreDialogElement from "WoltLabSuite/Core/Element/woltlab-core-dialog";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { getPhrase } from "WoltLabSuite/Core/Language";

declare global {
  interface Window {
    // Used internally for automation purposes.
    _trackSearchForUpdates?: (data: { returnValues: ResponseSearchForUpdates }) => void;
  }
}

let noResultsDialog: WoltlabCoreDialogElement | undefined = undefined;

async function searchForUpdates(): Promise<void> {
  if (noResultsDialog !== undefined) {
    noResultsDialog.show(getPhrase("wcf.acp.package.searchForUpdates"));
    return;
  }

  const result = await apiSearchForUpdates();
  if (!result.ok && result.error.param === "benchmark") {
    const message = document.createElement("p");
    message.textContent = result.error.code;
    dialogFactory().fromElement(message).asAlert().show(getPhrase("wcf.global.error.title"));
    return;
  }

  const response = result.unwrap();

  if (typeof window._trackSearchForUpdates === "function") {
    window._trackSearchForUpdates({ returnValues: response });
    return;
  }

  if (response.url !== "") {
    window.location.href = response.url;
    return;
  }

  const content = document.createElement("p");
  content.textContent = getPhrase("wcf.acp.package.searchForUpdates.noResults");
  noResultsDialog = dialogFactory().fromElement(content).asAlert();
  noResultsDialog.show(getPhrase("wcf.acp.package.searchForUpdates"));
}

export function setup(button: HTMLButtonElement): void {
  const search = promiseMutex(() => {
    button.disabled = true;

    return searchForUpdates().finally(() => {
      button.disabled = false;
    });
  });
  button.addEventListener("click", () => {
    search();
  });

  if (new URL(window.location.href).searchParams.has("searchForUpdates")) {
    search();
  }
}
