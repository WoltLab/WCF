/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { renderGridView } from "WoltLabSuite/Core/Api/GridViews/RenderGridView";
import WoltlabCoreDialog from "WoltLabSuite/Core/Element/woltlab-core-dialog";

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
    const json = await renderGridView(gridViewClass, pageNo, sortField, sortOrder, filters, gridViewParameters);

    // Prevents a circular dependency.
    const { dialogFactory } = await import("../Dialog");

    const dialog = dialogFactory().fromHtml(json.gridView).withoutControls();
    dialog.show(title);

    return dialog;
  }
}

export default GridViewSetup;
