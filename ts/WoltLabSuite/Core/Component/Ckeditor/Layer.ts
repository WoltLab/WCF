/**
 * Moves CKEditor’s overlay elements into the page overlay container. This
 * preserves their functionality when the editor appears within a native
 * `<dialog>` element.
 * 
 * See https://github.com/ckeditor/ckeditor5/issues/14747
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @woltlabExcludeBundle tiny
 */

import { getPageOverlayContainer } from "../../Helper/PageOverlay";

const targetClassNames = ["ck-body-wrapper", "ck-inspector-wrapper", "ck-inspector-portal"];

function setupWatcher(): void {
  const observer = new MutationObserver((mutations) => {
    for (const mutation of mutations) {
      for (const node of mutation.addedNodes) {
        if (!(node instanceof HTMLElement)) {
          continue;
        }

        for (const className of targetClassNames) {
          if (node.classList.contains(className)) {
            getPageOverlayContainer().append(node);

            continue;
          }
        }
      }
    }
  });
  observer.observe(document.body, {
    childList: true,
  });
}

let hasWatcher = false;
export function setup(): void {
  if (hasWatcher) {
    return;
  }

  hasWatcher = true;
  setupWatcher();
}
