/**
 * Gets the context menu options for a bulk interaction button.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { fromInfallibleApiRequest } from "../Result";

type Response = {
  template: string;
};

export async function getBulkContextMenuOptions(providerClassName: string, objectIds: number[]): Promise<Response> {
  return fromInfallibleApiRequest(() => {
    return prepareRequest(`${window.WSC_RPC_API_URL}core/interactions/bulk-context-menu-options`)
      .post({ provider: providerClassName, objectIDs: objectIds })
      .disableLoadingIndicator()
      .fetchAsJson();
  });
}
