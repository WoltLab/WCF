/**
 * Refreshes the package database and returns the list of the suggested packages
 * that can be installed.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "WoltLabSuite/Core/Api/Result";

export type Response = {
  template: string;
};

export async function resolveSuggestedPackages(
  packageIds: number[],
  suggestions: string[],
): Promise<ApiResult<Response>> {
  let response: Response;
  try {
    response = (await prepareRequest(`${window.WSC_RPC_API_URL}core/packages/suggestions/resolve`)
      .post({ packageIDs: packageIds, suggestions })
      .disableLoadingIndicator()
      .fetchAsJson()) as Response;
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue(response);
}
