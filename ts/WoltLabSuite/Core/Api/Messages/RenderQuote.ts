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
import { fromInfallibleApiRequest } from "../Result";

type Response = {
  objectID: number;
  author: string;
  link: string;
  avatar: string;
  message: string | null;
  rawMessage: string | null;
};

export async function renderQuote(objectType: string, objectID: number, isFullQuote: boolean): Promise<Response> {
  const url = new URL(window.WSC_RPC_API_URL + "core/messages/render-quote");
  url.searchParams.set("objectType", objectType);
  url.searchParams.set("isFullQuote", String(isFullQuote));
  url.searchParams.set("objectID", objectID.toString());

  return fromInfallibleApiRequest(() => {
    return prepareRequest(url).get().fetchAsJson();
  });
}
