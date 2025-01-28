/**
 * Inline editor action to trash an object.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { Action, InlineEditor } from "WoltLabSuite/Core/Component/Inline/Editor";
import { DropdownBuilderItemData } from "WoltLabSuite/Core/Ui/Dropdown/Builder";
import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { apiResultFromError, apiResultFromValue, ApiResult } from "WoltLabSuite/Core/Api/Result";
import { confirmationFactory } from "WoltLabSuite/Core/Component/Confirmation";
import { getPhrase } from "WoltLabSuite/Core/Language";

export class Trash implements Action {
  protected readonly inlineEditor: InlineEditor;
  protected readonly endpoint: string | URL;
  protected readonly title: string;

  constructor(inlineEditor: InlineEditor, title: string, endpoint: string | URL) {
    this.inlineEditor = inlineEditor;
    this.endpoint = endpoint;
    this.title = title;
  }

  get item(): DropdownBuilderItemData {
    return {
      label: getPhrase("wcf.global.button.trash"),
      callback: async () => {
        const result = await confirmationFactory().softDelete(this.title, true);
        if (!result.result) {
          return;
        }

        const response = await trash(this.endpoint, result.reason);
        if (response.ok) {
          this.inlineEditor.update({
            isDeleted: true,
            deleteNote: response.value,
          });
        }
      },
    };
  }

  isVisible(): boolean {
    return this.inlineEditor.getPermissions()["canTrash"] && !this.inlineEditor.getState("isDeleted");
  }
}

type Response = {
  deleteNote: string;
};

async function trash(endpoint: string | URL, reason: string): Promise<ApiResult<string>> {
  let response: Response;
  try {
    response = (await prepareRequest(endpoint)
      .post({
        reason,
      })
      .fetchAsJson()) as Response;
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue(response.deleteNote);
}
