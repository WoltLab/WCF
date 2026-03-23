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
import { fromInfallibleApiRequest } from "../Result";

type Response = {
  reactions: Record<number, number>;
};

export async function revertReaction(objectType: string, objectID: number): Promise<Response> {
  return fromInfallibleApiRequest(() => {
    return prepareRequest(`${window.WSC_RPC_API_URL}core/reactions/revert`)
      .post({
        objectType,
        objectID,
      })
      .fetchAsJson();
  });
}
