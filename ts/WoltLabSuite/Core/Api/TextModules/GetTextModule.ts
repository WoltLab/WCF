/**
 * Gets a text module including the HTML that is inserted into the editor.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { fromInfallibleApiRequest } from "WoltLabSuite/Core/Api/Result";

export type TextModuleData = {
  textModuleID: number;
  title: string;
  message: string;
};

export function getTextModule(textModuleId: number): Promise<TextModuleData> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/text-modules/${textModuleId}`);

  return fromInfallibleApiRequest(() => {
    return prepareRequest(url).get().fetchAsJson();
  });
}
