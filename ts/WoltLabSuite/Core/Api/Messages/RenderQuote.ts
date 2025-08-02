/**
 * Requests render a full quote of a message.
 *
 * @author Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 * @woltlabExcludeBundle tiny
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "../Result";

type Response = {
  objectID: number;
  author: string;
  link: string;
  avatar: string;
  message: string | null;
  rawMessage: string | null;
};

export async function renderQuote(
  objectType: string,
  objectID: number,
): Promise<ApiResult<Response>> {
  const url = new URL(window.WSC_RPC_API_URL + "core/messages/render-quote");
  url.searchParams.set("objectType", objectType);
  url.searchParams.set("isFullQuote", "true");
  url.searchParams.set("objectID", objectID.toString());

  let response: Response;
  try {
    response = (await prepareRequest(url).get().fetchAsJson()) as Response;
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue(response);
}
