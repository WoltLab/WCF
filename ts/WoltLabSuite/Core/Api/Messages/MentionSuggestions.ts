/**
 * Requests the list of users and groups that match the provided query string.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 * @woltlabExcludeBundle tiny
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "../Result";

type Item =
  | {
      avatarTag: string;
      username: string;
      userID: number;
      type: "user";
    }
  | {
      name: string;
      groupID: string;
      type: "group";
    };
type Response = Item[];

export async function mentionSuggestions(query: string): Promise<ApiResult<Response>> {
  const url = new URL(window.WSC_API_URL + "core/messages/mentionsuggestions");
  url.searchParams.set("query", query);

  let response: Response;
  try {
    response = (await prepareRequest(url).get().allowCaching().disableLoadingIndicator().fetchAsJson()) as Response;
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue(response);
}
