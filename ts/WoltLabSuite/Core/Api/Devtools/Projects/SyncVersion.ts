/**
 * Synchronizes the version of an installed package with the version number in
 * the `package.xml` of the project.
 *
 * @author    Alexander Ebert
 * @copyright 2001-2025 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.2
 * @woltlabExcludeBundle all
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { fromInfallibleApiRequest } from "../../Result";

export async function syncVersion(projectId: number): Promise<[]> {
  return fromInfallibleApiRequest(() => {
    return prepareRequest(`${window.WSC_RPC_API_URL}core/devtools/projects/${projectId}/sync-version`).post().fetchAsJson();
  });
}
