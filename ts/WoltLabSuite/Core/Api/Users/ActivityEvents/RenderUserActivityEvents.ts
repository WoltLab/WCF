/**
 * Loads a paginated list of recent user activity events.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "../../Result";

type Response = {
  lastEventID: number;
  lastEventTime: number;
  template: string;
};

export async function renderUserActivityEvents(
  lastEventTime: number,
  lastEventID: number = 0,
  userID: number = 0,
  boxID: number = 0,
  filteredByFollowedUsers: boolean = false,
): Promise<ApiResult<Response | Record<string, never>>> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/users/activity-events/render`);
  url.searchParams.set("lastEventTime", lastEventTime.toString());
  url.searchParams.set("lastEventID", lastEventID.toString());
  url.searchParams.set("userID", userID.toString());
  url.searchParams.set("boxID", boxID.toString());
  url.searchParams.set("filteredByFollowedUsers", filteredByFollowedUsers ? "1" : "0");

  let response: Response | Record<string, never>;
  try {
    response = (await prepareRequest(url).get().fetchAsJson()) as Response | Record<string, never>;
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue(response);
}
