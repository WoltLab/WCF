/**
 * Sends a post request to the given endpoint.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 * @woltlabExcludeBundle tiny
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "./Result";

type Payload = Blob | FormData | Record<string, unknown>;

export async function postObject<T = []>(endpoint: string, payload?: Payload): Promise<ApiResult<T>> {
  let response: T;

  try {
    response = (await prepareRequest(endpoint).post(payload).fetchAsJson()) as T;
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue(response);
}
