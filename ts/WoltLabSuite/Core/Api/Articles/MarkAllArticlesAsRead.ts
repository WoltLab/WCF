/**
 * Mark all articles as read
 *
 * @author Olaf Braun
 * @copyright  2001-2025 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "WoltLabSuite/Core/Api/Result";

export async function markAllArticlesAsRead(): Promise<ApiResult<[]>> {
  try {
    await prepareRequest(new URL(`${window.WSC_RPC_API_URL}core/articles/mark-all-as-read`))
      .post()
      .fetchAsJson();
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue([]);
}
