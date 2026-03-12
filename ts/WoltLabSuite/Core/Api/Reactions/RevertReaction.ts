/**
 * Reverts a reaction on an object.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "../Result";

type Response = {
  reactions: Record<number, number>;
};

export async function revertReaction(objectType: string, objectID: number): Promise<ApiResult<Response>> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/reactions/revert`);

  let response: Response;
  try {
    response = (await prepareRequest(url)
      .post({
        objectType,
        objectID,
      })
      .fetchAsJson()) as Response;
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue(response);
}
