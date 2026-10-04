/**
 * Selects the content of one or more users in a dialog and removes it with
 * the `UserContentRemoveWorker`.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import AcpUiWorker from "WoltLabSuite/Core/Acp/Ui/Worker";
import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import { showDefaultSuccessSnackbar } from "WoltLabSuite/Core/Component/Snackbar";
import { getPhrase } from "WoltLabSuite/Core/Language";

type Result = {
  userIDs: number[];
  contentProviders: string[];
};

async function removeContent(endpoint: string): Promise<boolean> {
  const { ok, result } = await dialogFactory().usingFormBuilder().fromEndpoint<Result>(endpoint);
  if (!ok) {
    return false;
  }

  return new Promise((resolve) => {
    new AcpUiWorker({
      dialogId: "removeContentWorker",
      dialogTitle: getPhrase("wcf.acp.content.removeContent"),
      className: "wcf\\system\\worker\\UserContentRemoveWorker",
      parameters: {
        userIDs: result.userIDs,
        contentProvider: result.contentProviders,
      },
      callbackAbort: () => resolve(false),
      callbackSuccess: () => resolve(true),
    });
  });
}

export function setup(identifier: string, container: HTMLElement): void {
  container.addEventListener("interaction:execute", (event: CustomEvent<Record<string, string>>) => {
    if (event.detail.interaction !== identifier) {
      return;
    }

    const element = event.target as HTMLElement;
    void removeContent(event.detail.endpoint).then((ok) => {
      if (!ok) {
        return;
      }

      // The activity points and the number of likes received may have changed.
      element.dispatchEvent(
        new CustomEvent("interaction:invalidate", {
          bubbles: true,
          detail: event.detail,
        }),
      );

      showDefaultSuccessSnackbar();
    });
  });
}

export function setupBulk(identifier: string, container: HTMLElement): void {
  container.addEventListener("bulk-interaction", (event: CustomEvent<Record<string, string>>) => {
    if (event.detail.bulkInteraction !== identifier) {
      return;
    }

    const objectIds = JSON.parse(event.detail.objectIds) as number[];
    void removeContent(event.detail.endpoint).then((ok) => {
      if (!ok) {
        return;
      }

      for (const objectId of objectIds) {
        container.querySelector(`[data-object-id="${objectId}"]`)?.dispatchEvent(
          new CustomEvent("interaction:invalidate", {
            bubbles: true,
          }),
        );
      }

      showDefaultSuccessSnackbar();

      container.dispatchEvent(new CustomEvent("interaction:reset-selection"));
    });
  });
}
