/**
 * Provides the dialog to report content.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import { showDefaultSuccessSnackbar } from "WoltLabSuite/Core/Component/Snackbar";
import { dialogFactory } from "../../Component/Dialog";
import { wheneverFirstSeen } from "../../Helper/Selector";

let reportEndpoint: string | undefined;

async function openReportDialog(element: HTMLElement): Promise<void> {
  if (!reportEndpoint) {
    throw new Error("Report endpoint is not set. Please call 'setup()' first.");
  }

  const objectId = parseInt(element.dataset.objectId || "");
  const objectType = element.dataset.reportContent!;

  const url = new URL(reportEndpoint);
  url.searchParams.set("objectID", objectId.toString());
  url.searchParams.set("objectType", objectType);

  const response = await dialogFactory().usingFormBuilder().fromEndpoint(url.toString());

  if (response.ok) {
    showDefaultSuccessSnackbar();
  }
}

function validateButton(element: HTMLElement): boolean {
  if (element.dataset.reportContent === "") {
    console.error("Missing the value for [data-report-content]", element);
    return false;
  }

  const objectId = parseInt(element.dataset.objectId || "");
  if (!objectId) {
    console.error("Expected a valid integer for [data-object-id]", element);
    return false;
  }

  return true;
}

function registerButton(element: HTMLElement): void {
  if (validateButton(element)) {
    element.addEventListener("click", (event) => {
      if (element.tagName === "A" || element.dataset.isLegacyButton === "true") {
        event.preventDefault();
      }

      void openReportDialog(element);
    });
  }
}

/**
 * @deprecated 6.0 Use the attribute `[data-report-content]` instead.
 */
export function registerLegacyButton(element: HTMLElement, objectType: string): void {
  element.dataset.reportContent = objectType;
  element.dataset.isLegacyButton = "true";

  registerButton(element);
}

export function setup(reportEndpointUrl: string): void {
  reportEndpoint = reportEndpointUrl;

  wheneverFirstSeen("[data-report-content]", (element) => registerButton(element));
}
