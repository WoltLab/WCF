/**
 * Deletes a user’s session, effectively forcing a device with that session id
 * to be logged out.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 * @woltlabExcludeBundle tiny
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { fromInfallibleApiRequest } from "../Result";

export async function deleteSession(sessionId: string): Promise<[]> {
  return fromInfallibleApiRequest(() => {
    return prepareRequest(`${window.WSC_RPC_API_URL}core/sessions/${sessionId}`).delete().fetchAsJson();
  });
}
