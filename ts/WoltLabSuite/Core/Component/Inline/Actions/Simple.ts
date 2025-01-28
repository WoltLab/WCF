/**
 * Inline editor action to restore an object.
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
import { getPhrase } from "WoltLabSuite/Core/Language";

export abstract class Simple implements Action {
  protected readonly inlineEditor: InlineEditor;
  protected readonly endpoint: string | URL;
  protected readonly label: string;

  protected constructor(inlineEditor: InlineEditor, label: string, endpoint: string | URL) {
    this.inlineEditor = inlineEditor;
    this.endpoint = endpoint;
    this.label = label;
  }

  get item(): DropdownBuilderItemData {
    return {
      label: getPhrase(this.label),
      callback: async () => {
        const response = await request(this.endpoint);
        if (response.ok) {
          this.responseOk();
        }
      },
    };
  }

  abstract responseOk(): void;
}

async function request(endpoint: string | URL): Promise<ApiResult<[]>> {
  try {
    await prepareRequest(endpoint).post().fetchAsJson();
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue([]);
}
