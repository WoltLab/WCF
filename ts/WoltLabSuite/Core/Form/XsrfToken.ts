/**
 * Manages the values of the hidden form inputs storing the XsrfToken.
 *
 * @author  Tim Duesterhus
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.5
 */

import { getXsrfToken } from "../Core";
import { wheneverFirstSeen } from "../Helper/Selector";

function isInput(node: Node): node is HTMLInputElement {
  return node.nodeName === "INPUT";
}

export function setup(): void {
  wheneverFirstSeen(".xsrfTokenInput", (node) => {
    if (!isInput(node)) {
      return;
    }

    node.value = getXsrfToken();
    node.classList.add("xsrfTokenInputHandled");
  });

  // A guest's session, and with it the token, can be started by a request made
  // after the page was loaded, leaving the inputs with an outdated value.
  document.addEventListener(
    "submit",
    (event) => {
      const form = event.target as HTMLFormElement;
      const token = getXsrfToken();
      form.querySelectorAll<HTMLInputElement>("input.xsrfTokenInput").forEach((input) => {
        input.value = token;
      });
    },
    { capture: true },
  );
}
