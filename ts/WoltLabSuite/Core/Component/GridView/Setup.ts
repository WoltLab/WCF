/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import WoltlabCoreDialog from "WoltLabSuite/Core/Element/woltlab-core-dialog";

interface ResponseGridView {
  gridView: string;
}

export class GridViewSetup {
  async fromPreset(
    title: string,
    gridViewClass: string,
    pageNo: number = 1,
    sortField: string = "",
    sortOrder: string = "ASC",
    filters?: Map<string, string>,
    gridViewParameters?: Map<string, string | string[]>,
  ): Promise<WoltlabCoreDialog> {
    const url = new URL(`${window.WSC_RPC_API_URL}core/grid-views/render`);
    url.searchParams.set("gridView", gridViewClass);
    url.searchParams.set("pageNo", pageNo.toString());
    url.searchParams.set("sortField", sortField);
    url.searchParams.set("sortOrder", sortOrder);
    if (filters) {
      filters.forEach((value, key) => {
        url.searchParams.set(`filters[${key}]`, value);
      });
    }
    if (gridViewParameters) {
      gridViewParameters.forEach((value, key) => {
        if (Array.isArray(value)) {
          value.forEach((innerValue, innerKey) => {
            url.searchParams.set(`gridViewParameters[${key}][${innerKey}]`, innerValue);
          });
        } else {
          url.searchParams.set(`gridViewParameters[${key}]`, value);
        }
      });
    }
    const json = (await prepareRequest(url).get().fetchAsJson()) as ResponseGridView;

    // Prevents a circular dependency.
    const { dialogFactory } = await import("../Dialog");

    const dialog = dialogFactory().fromHtml(json.gridView).withoutControls();
    dialog.show(title);

    return dialog;
  }
}

export default GridViewSetup;
