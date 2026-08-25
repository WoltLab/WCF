/**
 * Subscribes the active user to a watchable object.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { fromInfallibleApiRequest } from "../../Result";

export async function subscribe(objectType: string, objectID: number, enableNotification: boolean): Promise<void> {
  return fromInfallibleApiRequest(() => {
    return prepareRequest(`${window.WSC_RPC_API_URL}core/users/object-watches/subscribe`)
      .post({
        objectType,
        objectID,
        enableNotification,
      })
      .fetchAsJson();
  });
}
