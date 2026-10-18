/**
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import WoltlabCoreDialog from "WoltLabSuite/Core/Element/woltlab-core-dialog";
import type { PromptOptions } from "../Dialog/Controls";

interface ResponseListView {
  listView: string;
}

export class ListViewSetup {
  async fromPreset(
    title: string,
    listViewClass: string,
    listViewParameters?: Map<string, string>,
    filters?: Map<string, string>,
    sortField: string = "",
    sortOrder: string = "ASC",
    pageNo: number = 1,
    promptOptions?: Partial<PromptOptions>,
  ): Promise<WoltlabCoreDialog> {
    const url = new URL(`${window.WSC_RPC_API_URL}core/list-views/render`);
    url.searchParams.set("listView", listViewClass);
    url.searchParams.set("pageNo", pageNo.toString());
    url.searchParams.set("sortField", sortField);
    url.searchParams.set("sortOrder", sortOrder);
    if (filters) {
      filters.forEach((value, key) => {
        url.searchParams.set(`filters[${key}]`, value);
      });
    }
    if (listViewParameters) {
      listViewParameters.forEach((value, key) => {
        url.searchParams.set(`listViewParameters[${key}]`, value);
      });
    }
    const json = (await prepareRequest(url).get().fetchAsJson()) as ResponseListView;

    // Prevents a circular dependency.
    const { dialogFactory } = await import("../Dialog");

    // The dialog controls are only shown if the list view is used as a prompt.
    const controls = dialogFactory().fromHtml(json.listView);
    const dialog = promptOptions === undefined ? controls.withoutControls() : controls.asPrompt(promptOptions);
    dialog.show(title);

    return dialog;
  }
}

export default ListViewSetup;
