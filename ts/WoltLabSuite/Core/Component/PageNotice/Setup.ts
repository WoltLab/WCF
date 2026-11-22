/**
 * Handles the creation of page notices and refreshes the list of notices
 * after a notice has been added, edited or deleted.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */

import { renderPageNotices } from "WoltLabSuite/Core/Api/PageNotices/RenderPageNotices";
import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import { showDefaultSuccessSnackbar } from "WoltLabSuite/Core/Component/Snackbar";
import DomUtil from "WoltLabSuite/Core/Dom/Util";

async function refresh(container: HTMLElement): Promise<void> {
  const pageId = parseInt(container.dataset.pageId!, 10);
  const pageObjectId = container.dataset.pageObjectId ? parseInt(container.dataset.pageObjectId, 10) : undefined;

  const { template } = await renderPageNotices(pageId, pageObjectId);

  DomUtil.setInnerHtml(container, template);
}

async function addNotice(container: HTMLElement, endpoint: string): Promise<void> {
  const { ok } = await dialogFactory().usingFormBuilder().fromEndpoint(endpoint);
  if (!ok) {
    return;
  }

  await refresh(container);

  showDefaultSuccessSnackbar();
}

export function setup(container: HTMLElement): void {
  document.querySelectorAll<HTMLAnchorElement>(".jsPageNoticeAdd").forEach((link) => {
    link.addEventListener("click", (event) => {
      event.preventDefault();

      void addNotice(container, link.dataset.endpoint!);
    });
  });

  // The standalone interaction buttons would otherwise reload the page or
  // redirect, the listeners use the capture phase to intercept the events
  // before they reach the buttons.
  for (const eventName of ["interaction:invalidate", "interaction:remove"]) {
    container.addEventListener(
      eventName,
      (event) => {
        event.stopPropagation();

        void refresh(container);
      },
      { capture: true },
    );
  }
}
