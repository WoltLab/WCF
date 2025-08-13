/**
 * Gets the html code for the rendering of an article popover.
 *
 * @author  Marcel Werk
 * @copyright  2001-2025 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { fromInfallibleApiRequest } from "WoltLabSuite/Core/Api/Result";

type Response = {
  template: string;
};

export async function getArticlePopover(articleId: number): Promise<string> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/articles/${articleId}/popover`);

  return fromInfallibleApiRequest(() => {
    return prepareRequest(url).get().fetchAsJson();
  });
}
