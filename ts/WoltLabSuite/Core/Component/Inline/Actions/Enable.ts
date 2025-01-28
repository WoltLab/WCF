/**
 * Inline editor action to enable an object.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { Action, InlineEditor } from "WoltLabSuite/Core/Component/Inline/Editor";
import { DropdownBuilderItemData } from "WoltLabSuite/Core/Ui/Dropdown/Builder";
import { ApiResult, apiResultFromError, apiResultFromValue } from "WoltLabSuite/Core/Api/Result";
import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import { getPhrase } from "WoltLabSuite/Core/Language";

export class Enable implements Action {
  protected readonly inlineEditor: InlineEditor;
  protected readonly endpoint: string | URL;
  protected readonly useFormBuilder: boolean;

  constructor(inlineEditor: InlineEditor, endpoint: string | URL, useFormBuilder: boolean = false) {
    this.inlineEditor = inlineEditor;
    this.endpoint = endpoint;
    this.useFormBuilder = useFormBuilder;
  }

  get item(): DropdownBuilderItemData {
    return {
      label: getPhrase("wcf.global.button.enable"),
      callback: async () => {
        if (this.useFormBuilder) {
          const response = await dialogFactory()
            .usingFormBuilder()
            .fromEndpoint<{ isDisabled: boolean }>(this.endpoint.toString());

          if (response.ok) {
            this.inlineEditor.update({
              isDisabled: response.result.isDisabled,
            });
          }
        } else {
          if ((await enable(this.endpoint)).ok) {
            this.inlineEditor.update({
              isDisabled: false,
            });
          }
        }
      },
    };
  }

  isVisible(): boolean {
    return (
      this.inlineEditor.getPermissions()["canEnable"] &&
      !this.inlineEditor.getState("isDeleted") &&
      this.inlineEditor.getState("isDisabled")
    );
  }
}

async function enable(endpoint: string | URL): Promise<ApiResult<[]>> {
  try {
    await prepareRequest(endpoint).post().fetchAsJson();
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue([]);
}
