/**
 * Renders the preview of the active user's signature.
 *
 * @author  Marcel Werk
 * @copyright  2001-2026 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "WoltLabSuite/Core/Api/Result";

type Response = {
  message: string;
};

export async function renderSignaturePreview(message: string): Promise<ApiResult<Response>> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/users/signature/preview`);

  let response: Response;
  try {
    response = (await prepareRequest(url).post({ message }).fetchAsJson()) as Response;
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue(response);
}
