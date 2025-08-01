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
  authorID: number;
  author: string;
  time: string;
  title: string;
  link: string;
  avatar: string;
};

export async function getMessageAuthor(className: string, objectID: number): Promise<ApiResult<Response>> {
  const url = new URL(window.WSC_RPC_API_URL + "core/messages/message-author");
  url.searchParams.set("className", className);
  url.searchParams.set("objectID", objectID.toString());

  let response: Response;
  try {
    response = (await prepareRequest(url).get().allowCaching().fetchAsJson()) as Response;
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue(response);
}
