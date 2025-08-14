/**
 * Reverts a version tracker object to a previous version.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 * @woltlabExcludeBundle tiny
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { fromInfallibleApiRequest } from "../Result";

export async function revertVersion(objectType: string, objectId: number, versionId: number): Promise<[]> {
  return fromInfallibleApiRequest(() => {
    return prepareRequest(`${window.WSC_RPC_API_URL}core/version-trackers/revert`)
      .post({
        objectType,
        objectId,
        versionId,
      })
      .fetchAsJson();
  });
}
