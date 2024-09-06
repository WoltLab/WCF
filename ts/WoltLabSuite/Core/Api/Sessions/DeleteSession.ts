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
import { ApiResult, apiResultFromError, apiResultFromValue } from "../Result";

export async function deleteSession(sessionId: string): Promise<ApiResult<[]>> {
  try {
    await prepareRequest(`${window.WSC_RPC_API_URL}core/sessions/${sessionId}`).delete().fetchAsJson();
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue([]);
}
