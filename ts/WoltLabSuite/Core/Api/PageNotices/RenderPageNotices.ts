/**
 * Renders the notices of a page and object that are visible to the active user.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { fromInfallibleApiRequest } from "WoltLabSuite/Core/Api/Result";

type Response = {
  template: string;
};

export function renderPageNotices(pageId: number, pageObjectId: number | undefined): Promise<Response> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/page-notices/render`);
  url.searchParams.set("pageID", pageId.toString());
  if (pageObjectId !== undefined) {
    url.searchParams.set("pageObjectID", pageObjectId.toString());
  }

  return fromInfallibleApiRequest(() => {
    return prepareRequest(url).get().fetchAsJson();
  });
}
