/**
 * Gets the rows for the rendering of a grid view.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "../Result";

type Response = {
  template: string;
  pages: number;
  totalRows: number;
  filterLabels: ArrayLike<string>;
};

export async function getRows(
  gridViewClass: string,
  pageNo: number,
  sortField: string = "",
  sortOrder: string = "ASC",
  filters?: Map<string, string>,
  gridViewParameters?: Map<string, string>,
): Promise<ApiResult<Response>> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/grid-views/rows`);
  url.searchParams.set("gridView", gridViewClass);
  url.searchParams.set("pageNo", pageNo.toString());
  url.searchParams.set("sortField", sortField);
  url.searchParams.set("sortOrder", sortOrder);
  if (filters) {
    filters.forEach((value, key) => {
      url.searchParams.set(`filters[${key}]`, value);
    });
  }
  if (gridViewParameters) {
    gridViewParameters.forEach((value, key) => {
      url.searchParams.set(`gridViewParameters[${key}]`, value);
    });
  }

  let response: Response;
  try {
    response = (await prepareRequest(url).get().allowCaching().disableLoadingIndicator().fetchAsJson()) as Response;
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue(response);
}
