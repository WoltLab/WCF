/**
 * Fetches ACP search results for a given query.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "WoltLabSuite/Core/Api/Result";

export type AcpSearchResultItem = {
  link: string;
  title: string;
  subtitle?: string;
};

export type AcpSearchResultGroup = {
  title: string;
  items: AcpSearchResultItem[];
};

type Response = {
  results: AcpSearchResultGroup[];
};

export async function searchAcp(query: string, provider = ""): Promise<ApiResult<Response>> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/acp/search`);
  url.searchParams.set("query", query);
  if (provider) {
    url.searchParams.set("provider", provider);
  }

  let response: Response;
  try {
    response = (await prepareRequest(url.toString()).get().fetchAsJson()) as Response;
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue(response);
}
