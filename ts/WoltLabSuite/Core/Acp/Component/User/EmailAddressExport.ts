/**
 * Exports the email addresses of one or more users and offers the resulting
 * file for download.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";

type Result = {
  filename: string;
  mimeType: string;
  content: string;
};

async function exportEmailAddresses(endpoint: string): Promise<boolean> {
  const { ok, result } = await dialogFactory().usingFormBuilder().fromEndpoint<Result>(endpoint);
  if (!ok) {
    return false;
  }

  const url = window.URL.createObjectURL(new Blob([result.content], { type: `${result.mimeType};charset=utf-8` }));

  const link = document.createElement("a");
  link.href = url;
  link.download = result.filename;
  link.click();

  // Revoking the URL synchronously can abort the download in some browsers.
  window.setTimeout(() => window.URL.revokeObjectURL(url), 0);

  return true;
}

export function setupBulk(identifier: string, container: HTMLElement): void {
  container.addEventListener("bulk-interaction", (event: CustomEvent<Record<string, string>>) => {
    if (event.detail.bulkInteraction !== identifier) {
      return;
    }

    void exportEmailAddresses(event.detail.endpoint).then((ok) => {
      if (!ok) {
        return;
      }

      container.dispatchEvent(new CustomEvent("interaction:reset-selection"));
    });
  });
}
