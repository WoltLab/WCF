/**
 * Inline editor action to disable an object.
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

export class Disable implements Action {
  protected readonly inlineEditor: InlineEditor;
  protected readonly endpoint: string | URL;

  constructor(inlineEditor: InlineEditor, endpoint: string | URL) {
    this.inlineEditor = inlineEditor;
    this.endpoint = endpoint;
  }

  get item(): DropdownBuilderItemData {
    return {
      label: "wcf.global.button.disable",
      callback: async () => {
        const response = await disable(this.endpoint);

        if (response.ok) {
          this.inlineEditor.update({
            isDisabled: true,
          });
        }
      },
    };
  }

  isVisible(): boolean {
    return (
      this.inlineEditor.getPermissions()["canEnable"] &&
      !this.inlineEditor.getState("isDeleted") &&
      !this.inlineEditor.getState("isDisabled")
    );
  }
}

async function disable(endpoint: string | URL): Promise<ApiResult<[]>> {
  try {
    await prepareRequest(endpoint).post().fetchAsJson();
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue([]);
}
