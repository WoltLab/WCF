/**
 * Removes the subscription of the active user to a watchable object.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { fromInfallibleApiRequest } from "../../Result";

export async function unsubscribe(objectType: string, objectID: number): Promise<void> {
  await fromInfallibleApiRequest(() => {
    return prepareRequest(`${window.WSC_RPC_API_URL}core/users/object-watches/unsubscribe`)
      .post({
        objectType,
        objectID,
      })
      .fetchAsJson();
  });
}
