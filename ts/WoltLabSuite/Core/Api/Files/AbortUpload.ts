/**
 * Discards a pending upload that has been started through `upload()`.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "../Result";

/**
 * Deletes the temporary upload and the chunks received so far. Fails with a
 * validation error for unknown identifiers, which includes uploads that have
 * already been completed.
 */
export async function abortUpload(identifier: string): Promise<ApiResult<[]>> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/files/upload/${identifier}`);

  try {
    await prepareRequest(url).delete().disableLoadingIndicator().fetchAsJson();
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue([]);
}
