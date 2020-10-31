/**
 * Handles the user trophy dialog.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/User/Trophy/List
 */

import * as Ajax from "../../../Ajax";
import { AjaxCallbackObject, AjaxCallbackSetup, DatabaseObjectActionResponse } from "../../../Ajax/Data";
import { DialogCallbackObject, DialogData, DialogCallbackSetup } from "../../Dialog/Data";
import DomChangeListener from "../../../Dom/Change/Listener";
import UiDialog from "../../Dialog";
import UiPagination from "../../Pagination";

class CacheData {
  private readonly cache = new Map<number, string>();

  constructor(readonly pageCount: number, readonly title: string) {}

  has(pageNo: number): boolean {
    return this.cache.has(pageNo);
  }

  get(pageNo: number): string | undefined {
    return this.cache.get(pageNo);
  }

  set(pageNo: number, template: string): void {
    this.cache.set(pageNo, template);
  }
}

class UiUserTrophyList implements AjaxCallbackObject, DialogCallbackObject {
  private readonly cache = new Map<number, CacheData>();
  private currentPageNo = 0;
  private currentUser = 0;
  private readonly knownElements = new WeakSet<HTMLElement>();

  /**
   * Initializes the user trophy list.
   */
  constructor() {
    DomChangeListener.add("WoltLabSuite/Core/Ui/User/Trophy/List", () => this.rebuild());

    this.rebuild();
  }

  /**
   * Adds event userTrophyOverlayList elements.
   */
  private rebuild(): void {
    document.querySelectorAll(".userTrophyOverlayList").forEach((element: HTMLElement) => {
      if (!this.knownElements.has(element)) {
        element.addEventListener("click", (ev) => this.open(element, ev));

        this.knownElements.add(element);
      }
    });
  }

  /**
   * Opens the user trophy list for a specific user.
   */
  private open(element: HTMLElement, event: MouseEvent): void {
    event.preventDefault();

    this.currentPageNo = 1;
    this.currentUser = +element.dataset.userId!;
    this.showPage();
  }

  /**
   * Shows the current or given page.
   */
  private showPage(pageNo?: number): void {
    if (pageNo !== undefined) {
      this.currentPageNo = pageNo;
    }

    const data = this.cache.get(this.currentUser);
    if (data) {
      // validate pageNo
      if (data.pageCount !== 0 && (this.currentPageNo < 1 || this.currentPageNo > data.pageCount)) {
        throw new RangeError(`pageNo must be between 1 and ${data.pageCount} (${this.currentPageNo} given).`);
      }
    }

    if (data && data.has(this.currentPageNo)) {
      const dialog = UiDialog.open(this, data.get(this.currentPageNo)) as DialogData;
      UiDialog.setTitle("userTrophyListOverlay", data.title);

      if (data.pageCount > 1) {
        const element = dialog.content.querySelector(".jsPagination") as HTMLElement;
        if (element !== null) {
          new UiPagination(element, {
            activePage: this.currentPageNo,
            maxPage: data.pageCount,
            callbackSwitch: this.showPage.bind(this),
          });
        }
      }
    } else {
      Ajax.api(this, {
        parameters: {
          pageNo: this.currentPageNo,
          userID: this.currentUser,
        },
      });
    }
  }

  _ajaxSuccess(data: AjaxResponse): void {
    let cache: CacheData;
    if (data.returnValues.pageCount !== undefined) {
      cache = new CacheData(+data.returnValues.pageCount, data.returnValues.title!);
      this.cache.set(this.currentUser, cache);
    } else {
      cache = this.cache.get(this.currentUser)!;
    }

    cache.set(this.currentPageNo, data.returnValues.template);
    this.showPage();
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        actionName: "getGroupedUserTrophyList",
        className: "wcf\\data\\user\\trophy\\UserTrophyAction",
      },
    };
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: "userTrophyListOverlay",
      options: {
        title: "",
      },
      source: null,
    };
  }
}

export = UiUserTrophyList;

interface AjaxResponse extends DatabaseObjectActionResponse {
  returnValues: {
    pageCount?: number;
    template: string;
    title?: string;
  };
}
