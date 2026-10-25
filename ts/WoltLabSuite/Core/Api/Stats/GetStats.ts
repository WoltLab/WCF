/**
 * Fetches the daily statistics of the requested types, grouped by the requested time unit.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle all
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "WoltLabSuite/Core/Api/Result";

export type DateGrouping = "daily" | "weekly" | "monthly" | "yearly";

export type StatValue = "counter" | "total";

export type StatSeries = {
  objectTypeID: number;
  label: string;
  data: Array<{
    date: string;
    value: number;
  }>;
};

export async function getStats(
  startDate: string,
  endDate: string,
  objectTypeIDs: number[],
  dateGrouping: DateGrouping,
  value: StatValue,
): Promise<ApiResult<StatSeries[]>> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/stats`);
  url.searchParams.set("startDate", startDate);
  url.searchParams.set("endDate", endDate);
  for (const objectTypeID of objectTypeIDs) {
    url.searchParams.append("objectTypeIDs[]", objectTypeID.toString());
  }
  url.searchParams.set("dateGrouping", dateGrouping);
  url.searchParams.set("value", value);

  let response: StatSeries[];
  try {
    response = (await prepareRequest(url).get().fetchAsJson()) as StatSeries[];
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue(response);
}
