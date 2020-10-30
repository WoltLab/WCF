/**
 * Suggestions for page object ids with external response data processing.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Page/Search/Input
 */

import * as Core from "../../../Core";
import UiSearchInput from "../../Search/Input";
import { SearchInputOptions } from "../../Search/Data";
import { DatabaseObjectActionPayload, DatabaseObjectActionResponse } from "../../../Ajax/Data";

type CallbackSuccess = (data: DatabaseObjectActionResponse) => void;

interface PageSearchOptions extends SearchInputOptions {
  callbackSuccess: CallbackSuccess;
}

class UiPageSearchInput extends UiSearchInput {
  private readonly callbackSuccess: CallbackSuccess;
  private pageId: number;

  constructor(element: HTMLInputElement, options: PageSearchOptions) {
    if (typeof options.callbackSuccess !== "function") {
      throw new Error("Expected a valid callback function for 'callbackSuccess'.");
    }

    options = Core.extend(
      {
        ajax: {
          className: "wcf\\data\\page\\PageAction",
        },
      },
      options
    ) as any;

    super(element, options);

    this.callbackSuccess = options.callbackSuccess;

    this.pageId = 0;
  }

  /**
   * Sets the target page id.
   */
  setPageId(pageId: number): void {
    this.pageId = pageId;
  }

  protected getParameters(value: string): Partial<DatabaseObjectActionPayload> {
    const data = super.getParameters(value);

    data.objectIDs = [this.pageId];

    return data;
  }

  _ajaxSuccess(data: DatabaseObjectActionResponse): void {
    this.callbackSuccess(data);
  }
}

export = UiPageSearchInput;
