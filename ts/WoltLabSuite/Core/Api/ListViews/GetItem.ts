/**
 * Gets a single item for rendering in a list view.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { fromInfallibleApiRequest } from "../Result";

type Response = {
  template: string;
};

export async function getItem(
  listViewClass: string,
  objectId: string | number,
  listViewParameters?: Map<string, string>,
): Promise<Response> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/list-views/item`);
  url.searchParams.set("listView", listViewClass);
  url.searchParams.set("objectID", objectId.toString());
  if (listViewParameters) {
    listViewParameters.forEach((value, key) => {
      if (Array.isArray(value)) {
        value.forEach((innerValue, innerkey) => {
          url.searchParams.set(`listViewParameters[${key}][${innerkey}]`, innerValue);
        });
      } else {
        url.searchParams.set(`listViewParameters[${key}]`, value);
      }
    });
  }

  return fromInfallibleApiRequest(() => {
    return prepareRequest(url).get().allowCaching().disableLoadingIndicator().fetchAsJson();
  });
}
