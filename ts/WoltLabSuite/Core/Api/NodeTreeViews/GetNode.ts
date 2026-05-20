/**
 * Gets a single node for rendering in a node tree view.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { fromInfallibleApiRequest } from "../Result";

type Response = {
  template: string;
};

export async function getNode(
  nodeTreeViewClass: string,
  objectId: string | number,
  nodeTreeViewParameters?: Map<string, string>,
): Promise<Response> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/node-tree-views/node`);
  url.searchParams.set("nodeTreeView", nodeTreeViewClass);
  url.searchParams.set("objectID", objectId.toString());
  if (nodeTreeViewParameters) {
    nodeTreeViewParameters.forEach((value, key) => {
      if (Array.isArray(value)) {
        value.forEach((innerValue, innerKey) => {
          url.searchParams.set(`nodeTreeViewParameters[${key}][${innerKey}]`, innerValue);
        });
      } else {
        url.searchParams.set(`nodeTreeViewParameters[${key}]`, value);
      }
    });
  }

  return fromInfallibleApiRequest(() => {
    return prepareRequest(url).get().allowCaching().disableLoadingIndicator().fetchAsJson();
  });
}
