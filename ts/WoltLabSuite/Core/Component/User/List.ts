/**
 * Object-based user list.
 *
 * @author Marcel Werk
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import { dboAction } from "../../Ajax";
import WoltlabCoreDialogElement from "../../Element/woltlab-core-dialog";
import UiPagination from "../../Ui/Pagination";
import { dialogFactory } from "../Dialog";
import DomUtil from "WoltLabSuite/Core/Dom/Util";

export class UserList {
  readonly #options: AjaxRequestOptions;
  readonly #dialogTitle: string;
  #pageNo = 1;
  #pageCount = 0;
  #dialog?: WoltlabCoreDialogElement;

  constructor(options: AjaxRequestOptions, dialogTitle: string) {
    this.#options = options;
    this.#dialogTitle = dialogTitle;
  }

  open(): void {
    this.#pageNo = 1;
    void this.#loadPage(this.#pageNo);
  }

  #showPage(pageNo: number, template: string): void {
    if (pageNo) {
      this.#pageNo = pageNo;
    }

    const dialog = this.#getDialog();
    DomUtil.setInnerHtml(dialog.content, template);
    dialog.show(this.#dialogTitle);

    if (this.#pageCount > 1) {
      const element = dialog.content.querySelector(".jsPagination") as HTMLElement;
      if (element !== null) {
        new UiPagination(element, {
          activePage: this.#pageNo,
          maxPage: this.#pageCount,
          callbackSwitch: (pageNo) => {
            void this.#loadPage(pageNo);
          },
        });
      }
    }
  }

  async #loadPage(pageNo: number): Promise<void> {
    if (this.#pageCount !== 0 && (pageNo < 1 || pageNo > this.#pageCount)) {
      throw new RangeError(`pageNo must be between 1 and ${this.#pageCount} (${pageNo} given).`);
    }

    this.#options.parameters.pageNo = pageNo;

    const response = (await dboAction("getGroupedUserList", this.#options.className)
      .payload(this.#options.parameters)
      .dispatch()) as ResponseGetGroupedUserList;

    if (response.pageCount) {
      this.#pageCount = response.pageCount;
    }

    this.#showPage(pageNo, response.template);
  }

  #getDialog(): WoltlabCoreDialogElement {
    if (this.#dialog === undefined) {
      this.#dialog = dialogFactory().withoutContent().withoutControls();
    }

    return this.#dialog;
  }
}

type AjaxRequestOptions = {
  className: string;
  parameters: {
    [key: string]: any;
  };
};

type ResponseGetGroupedUserList = {
  pageCount?: number;
  template: string;
};
