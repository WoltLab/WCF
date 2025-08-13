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
import { fromInfallibleApiRequest } from "../Result";

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

export async function mentionSuggestions(query: string): Promise<Response> {
  const url = new URL(window.WSC_RPC_API_URL + "core/messages/mention-suggestions");
  url.searchParams.set("query", query);

  return fromInfallibleApiRequest<Response>(() => {
    return prepareRequest(url).get().allowCaching().disableLoadingIndicator().fetchAsJson();
  });
}
