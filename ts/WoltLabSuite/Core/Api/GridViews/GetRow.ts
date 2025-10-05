/**
 * Gets a single row for rendering in a grid view.
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

export async function getRow(
  gridViewClass: string,
  objectId: string | number,
  gridViewParameters?: Map<string, string>,
): Promise<Response> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/grid-views/row`);
  url.searchParams.set("gridView", gridViewClass);
  url.searchParams.set("objectID", objectId.toString());
  if (gridViewParameters) {
    gridViewParameters.forEach((value, key) => {
      if (Array.isArray(value)) {
        value.forEach((innerValue, innerkey) => {
          url.searchParams.set(`gridViewParameters[${key}][${innerkey}]`, innerValue);
        });
      } else {
        url.searchParams.set(`gridViewParameters[${key}]`, value);
      }
    });
  }

  return fromInfallibleApiRequest(() => {
    return prepareRequest(url).get().allowCaching().disableLoadingIndicator().fetchAsJson();
  });
}
