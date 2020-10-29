/**
 * Object-based user list.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/User/List
 */

import * as Ajax from "../../Ajax";
import * as Core from "../../Core";
import DomUtil from "../../Dom/Util";
import UiDialog from "../Dialog";
import UiPagination from "../Pagination";
import { AjaxCallbackObject, DatabaseObjectActionResponse, RequestOptions } from "../../Ajax/Data";
import { DialogCallbackObject, DialogData, DialogSettings } from "../Dialog/Data";

/**
 * @constructor
 */
class UiUserList implements AjaxCallbackObject, DialogCallbackObject {
  private readonly cache = new Map<number, string>();
  private readonly options: AjaxRequestOptions;
  private pageCount = 0;
  private pageNo = 1;

  /**
   * Initializes the user list.
   *
   * @param  {object}  options    list of initialization options
   */
  constructor(options: AjaxRequestOptions) {
    this.options = Core.extend(
      {
        className: "",
        dialogTitle: "",
        parameters: {},
      },
      options
    ) as AjaxRequestOptions;
  }

  /**
   * Opens the user list.
   */
  open() {
    this.pageNo = 1;
    this.showPage();
  }

  /**
   * Shows the current or given page.
   */
  private showPage(pageNo?: number): void {
    if (typeof pageNo === "number") {
      this.pageNo = +pageNo;
    }

    if (this.pageCount !== 0 && (this.pageNo < 1 || this.pageNo > this.pageCount)) {
      throw new RangeError("pageNo must be between 1 and " + this.pageCount + " (" + this.pageNo + " given).");
    }

    if (this.cache.has(this.pageNo)) {
      const dialog = UiDialog.open(this, this.cache.get(this.pageNo)) as DialogData;

      if (this.pageCount > 1) {
        const element = dialog.content.querySelector(".jsPagination") as HTMLElement;
        if (element !== null) {
          new UiPagination(element, {
            activePage: this.pageNo,
            maxPage: this.pageCount,

            callbackSwitch: this.showPage.bind(this),
          });
        }

        // scroll to the list start
        const container = dialog.content.parentElement!;
        if (container.scrollTop > 0) {
          container.scrollTop = 0;
        }
      }
    } else {
      this.options.parameters.pageNo = this.pageNo;

      Ajax.api(this, {
        parameters: this.options.parameters,
      });
    }
  }

  _ajaxSuccess(data: AjaxResponse): void {
    if (data.returnValues.pageCount !== undefined) {
      this.pageCount = ~~data.returnValues.pageCount;
    }

    this.cache.set(this.pageNo, data.returnValues.template);
    this.showPage();
  }

  _ajaxSetup(): RequestOptions {
    return {
      data: {
        actionName: "getGroupedUserList",
        className: this.options.className,
        interfaceName: "wcf\\data\\IGroupedUserListAction",
      },
    };
  }

  _dialogSetup(): DialogSettings {
    return {
      id: DomUtil.getUniqueId(),
      options: {
        title: this.options.dialogTitle,
      },
      source: null,
    };
  }
}

export = UiUserList;

interface AjaxRequestOptions {
  className: string;
  dialogTitle: string;
  parameters: {
    [key: string]: any;
  };
}

interface AjaxResponse extends DatabaseObjectActionResponse {
  returnValues: {
    pageCount?: number;
    template: string;
  };
}
