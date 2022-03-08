/**
 * Manages the values of the hidden form inputs storing the XsrfToken.
 *
 * @author  Tim Duesterhus
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/XsrfToken
 * @since 5.5
 */

import { getXsrfToken } from "../Core";

function isInput(node: Node): node is HTMLInputElement {
  return node.nodeName === "INPUT";
}

function createObserver(): void {
  const observer = new MutationObserver((mutations) => {
    const token = getXsrfToken();

    mutations.forEach((mutation) => {
      mutation.addedNodes.forEach((node) => {
        if (!isInput(node)) {
          return;
        }
        if (!node.classList.contains("xsrfTokenInput")) {
          return;
        }

        node.value = token;
        node.classList.add("xsrfTokenInputHandled");
      });
    });
  });

  observer.observe(document, { subtree: true, childList: true });
}

export function setup(): void {
  createObserver();

  const token = getXsrfToken();
  document.querySelectorAll(".xsrfTokenInput").forEach((node) => {
    if (!isInput(node)) {
      return;
    }

    node.value = token;
    node.classList.add("xsrfTokenInputHandled");
  });
}
