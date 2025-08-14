/**
 * Gets the html code for the rendering of a exception log entry.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { fromInfallibleApiRequest } from "../Result";

type Response = {
  template: string;
};

export async function renderException(exceptionId: string): Promise<Response> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/exceptions/${exceptionId}/render`);

  return fromInfallibleApiRequest(() => {
    return prepareRequest(url).get().fetchAsJson();
  });
}
