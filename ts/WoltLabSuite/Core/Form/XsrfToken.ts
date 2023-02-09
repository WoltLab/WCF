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
  const token = getXsrfToken();

  wheneverFirstSeen(".xsrfTokenInput", (node) => {
    if (!isInput(node)) {
      return;
    }

    node.value = token;
    node.classList.add("xsrfTokenInputHandled");
  });
}
