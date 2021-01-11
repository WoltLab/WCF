/**
 * Provides the media search for the media manager.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Media/Manager/Search
 */

import MediaManager from "./Base";
import { AjaxCallbackObject, AjaxCallbackSetup } from "../../Ajax/Data";
import { Media } from "../Data";
import * as DomTraverse from "../../Dom/Traverse";
import * as Language from "../../Language";
import * as Ajax from "../../Ajax";
import * as Core from "../../Core";
import DomUtil from "../../Dom/Util";

interface AjaxResponseData {
  returnValues: {
    media?: Media;
    pageCount?: number;
    pageNo?: number;
    template?: string;
  };
}

class MediaManagerSearch implements AjaxCallbackObject {
  protected readonly _cancelButton: HTMLSpanElement;
  protected readonly _input: HTMLInputElement;
  protected readonly _mediaManager: MediaManager;
  protected readonly _searchContainer: HTMLDivElement;
  protected _searchMode = false;

  constructor(mediaManager: MediaManager) {
    this._mediaManager = mediaManager;

    const dialog = mediaManager.getDialog();

    this._searchContainer = dialog.querySelector(".mediaManagerSearch") as HTMLDivElement;
    this._input = dialog.querySelector(".mediaManagerSearchField") as HTMLInputElement;
    this._input.addEventListener("keypress", (ev) => this._keyPress(ev));

    this._cancelButton = dialog.querySelector(".mediaManagerSearchCancelButton") as HTMLSpanElement;
    this._cancelButton.addEventListener("click", () => this._cancelSearch());
  }

  public _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        actionName: "getSearchResultList",
        className: "wcf\\data\\media\\MediaAction",
        interfaceName: "wcf\\data\\ISearchAction",
      },
    };
  }

  public _ajaxSuccess(data: AjaxResponseData): void {
    this._mediaManager.setMedia(data.returnValues.media || ({} as Media), data.returnValues.template || "", {
      pageCount: data.returnValues.pageCount || 0,
      pageNo: data.returnValues.pageNo || 0,
    });

    this._mediaManager.getDialog().querySelector(".dialogContent")!.scrollTop = 0;
  }

  /**
   * Cancels the search after clicking on the cancel search button.
   */
  protected _cancelSearch(): void {
    if (this._searchMode) {
      this._searchMode = false;

      this.resetSearch();
      this._mediaManager.resetMedia();
    }
  }

  /**
   * Hides the search string threshold error.
   */
  protected _hideStringThresholdError(): void {
    const innerInfo = DomTraverse.childByClass(
      this._input.parentNode!.parentNode as HTMLElement,
      "innerInfo",
    ) as HTMLElement;
    if (innerInfo) {
      DomUtil.hide(innerInfo);
    }
  }

  /**
   * Handles the `[ENTER]` key to submit the form.
   */
  protected _keyPress(event: KeyboardEvent): void {
    if (event.key === "Enter") {
      event.preventDefault();

      if (this._input.value.length >= this._mediaManager.getOption("minSearchLength")) {
        this._hideStringThresholdError();

        this.search();
      } else {
        this._showStringThresholdError();
      }
    }
  }

  /**
   * Shows the search string threshold error.
   */
  protected _showStringThresholdError(): void {
    let innerInfo = DomTraverse.childByClass(
      this._input.parentNode!.parentNode as HTMLElement,
      "innerInfo",
    ) as HTMLParagraphElement;
    if (innerInfo) {
      DomUtil.show(innerInfo);
    } else {
      innerInfo = document.createElement("p");
      innerInfo.className = "innerInfo";
      innerInfo.textContent = Language.get("wcf.media.search.info.searchStringThreshold", {
        minSearchLength: this._mediaManager.getOption("minSearchLength"),
      });

      (this._input.parentNode! as HTMLElement).insertAdjacentElement("afterend", innerInfo);
    }
  }

  /**
   * Hides the media search.
   */
  public hideSearch(): void {
    DomUtil.hide(this._searchContainer);
  }

  /**
   * Resets the media search.
   */
  public resetSearch(): void {
    this._input.value = "";
  }

  /**
   * Shows the media search.
   */
  public showSearch(): void {
    DomUtil.show(this._searchContainer);
  }

  /**
   * Sends an AJAX request to fetch search results.
   */
  public search(pageNo?: number): void {
    if (typeof pageNo !== "number") {
      pageNo = 1;
    }

    let searchString = this._input.value;
    if (searchString && this._input.value.length < this._mediaManager.getOption("minSearchLength")) {
      this._showStringThresholdError();

      searchString = "";
    } else {
      this._hideStringThresholdError();
    }

    this._searchMode = true;

    Ajax.api(this, {
      parameters: {
        categoryID: this._mediaManager.getCategoryId(),
        imagesOnly: this._mediaManager.getOption("imagesOnly"),
        mode: this._mediaManager.getMode(),
        pageNo: pageNo,
        searchString: searchString,
      },
    });
  }
}

Core.enableLegacyInheritance(MediaManagerSearch);

export = MediaManagerSearch;
