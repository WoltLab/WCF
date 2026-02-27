/**
 * Loads a paginated list of reactions for a user profile.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "../../Result";

type Response = {
  lastLikeTime: number;
  template: string;
};

export async function renderUserReactions(
  userID: number,
  targetType: string,
  lastLikeTime: number = 0,
  reactionTypeID: number = 0,
): Promise<ApiResult<Response | Record<string, never>>> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/users/${userID}/reactions/render`);
  url.searchParams.set("targetType", targetType);
  url.searchParams.set("lastLikeTime", lastLikeTime.toString());
  url.searchParams.set("reactionTypeID", reactionTypeID.toString());

  let response: Response | Record<string, never>;
  try {
    response = (await prepareRequest(url).get().fetchAsJson()) as Response | Record<string, never>;
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue(response);
}
