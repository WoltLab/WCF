/**
 * Gets the items for the rendering of a list view.
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
  pages: number;
  totalItems: number;
  filterLabels: ArrayLike<string>;
};

export async function getItems(
  listViewClass: string,
  pageNo: number,
  sortField: string = "",
  sortOrder: string = "ASC",
  filters?: Map<string, string>,
  listViewParameters?: Map<string, string>,
  allowFiltering = true,
  allowSorting = true,
  allowInteractions = true,
  allowBulkInteractions = true,
): Promise<Response> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/list-views/items`);
  url.searchParams.set("listView", listViewClass);
  url.searchParams.set("pageNo", pageNo.toString());
  url.searchParams.set("sortField", sortField);
  url.searchParams.set("sortOrder", sortOrder);
  url.searchParams.set("allowFiltering", allowFiltering.toString());
  url.searchParams.set("allowSorting", allowSorting.toString());
  url.searchParams.set("allowInteractions", allowInteractions.toString());
  url.searchParams.set("allowBulkInteractions", allowBulkInteractions.toString());
  if (filters) {
    filters.forEach((value, key) => {
      url.searchParams.set(`filters[${key}]`, value);
    });
  }
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
