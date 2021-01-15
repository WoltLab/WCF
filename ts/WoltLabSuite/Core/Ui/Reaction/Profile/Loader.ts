/**
 * Handles the reaction list in the user profile.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Reaction/Profile/Loader
 * @since       5.2
 */

import * as Ajax from "../../../Ajax";
import { AjaxCallbackSetup, ResponseData } from "../../../Ajax/Data";
import * as Core from "../../../Core";
import DomUtil from "../../../Dom/Util";
import * as Language from "../../../Language";

interface AjaxParameters {
  parameters: {
    [key: string]: number | string;
  };
}

interface AjaxResponse extends ResponseData {
  returnValues: {
    template?: string;
    lastLikeTime: number;
  };
}

class UiReactionProfileLoader {
  protected readonly _container: HTMLElement;
  protected readonly _loadButton: HTMLButtonElement;
  protected readonly _noMoreEntries: HTMLElement;
  protected readonly _options: AjaxParameters;
  protected _reactionTypeID: number | null = null;
  protected _targetType = "received";
  protected readonly _userID: number;

  /**
   * Initializes a new ReactionListLoader object.
   */
  constructor(userID: number) {
    this._container = document.getElementById("likeList")!;
    this._userID = userID;
    this._options = {
      parameters: {},
    };

    if (!this._userID) {
      throw new Error("[WoltLabSuite/Core/Ui/Reaction/Profile/Loader] Invalid parameter 'userID' given.");
    }

    const loadButtonList = document.createElement("li");
    loadButtonList.className = "likeListMore showMore";
    this._noMoreEntries = document.createElement("small");
    this._noMoreEntries.innerHTML = Language.get("wcf.like.reaction.noMoreEntries");
    this._noMoreEntries.style.display = "none";
    loadButtonList.appendChild(this._noMoreEntries);

    this._loadButton = document.createElement("button");
    this._loadButton.className = "small";
    this._loadButton.innerHTML = Language.get("wcf.like.reaction.more");
    this._loadButton.addEventListener("click", () => this._loadReactions());
    this._loadButton.style.display = "none";
    loadButtonList.appendChild(this._loadButton);
    this._container.appendChild(loadButtonList);

    if (document.querySelectorAll("#likeList > li").length === 2) {
      this._noMoreEntries.style.display = "";
    } else {
      this._loadButton.style.display = "";
    }

    this._setupReactionTypeButtons();
    this._setupTargetTypeButtons();
  }

  /**
   * Set up the reaction type buttons.
   */
  protected _setupReactionTypeButtons(): void {
    document.querySelectorAll("#reactionType .button").forEach((element: HTMLElement) => {
      element.addEventListener("click", () => this._changeReactionTypeValue(~~element.dataset.reactionTypeId!));
    });
  }

  /**
   * Set up the target type buttons.
   */
  protected _setupTargetTypeButtons(): void {
    document.querySelectorAll("#likeType .button").forEach((element: HTMLElement) => {
      element.addEventListener("click", () => this._changeTargetType(element.dataset.likeType!));
    });
  }

  /**
   * Changes the reaction target type (given or received) and reload the entire element.
   */
  protected _changeTargetType(targetType: string): void {
    if (targetType !== "given" && targetType !== "received") {
      throw new Error("[WoltLabSuite/Core/Ui/Reaction/Profile/Loader] Invalid parameter 'targetType' given.");
    }

    if (targetType !== this._targetType) {
      // remove old active state
      document.querySelector("#likeType .button.active")!.classList.remove("active");

      // add active status to new button
      document.querySelector(`#likeType .button[data-like-type="${targetType}"]`)!.classList.add("active");

      this._targetType = targetType;
      this._reload();
    }
  }

  /**
   * Changes the reaction type value and reload the entire element.
   */
  protected _changeReactionTypeValue(reactionTypeID: number): void {
    // remove old active state
    const activeButton = document.querySelector("#reactionType .button.active");
    if (activeButton) {
      activeButton.classList.remove("active");
    }

    if (this._reactionTypeID !== reactionTypeID) {
      // add active status to new button
      document
        .querySelector(`#reactionType .button[data-reaction-type-id="${reactionTypeID}"]`)!
        .classList.add("active");

      this._reactionTypeID = reactionTypeID;
    } else {
      this._reactionTypeID = null;
    }

    this._reload();
  }

  /**
   * Handles reload.
   */
  protected _reload(): void {
    document.querySelectorAll("#likeList > li:not(:first-child):not(:last-child)").forEach((el) => el.remove());

    this._container.dataset.lastLikeTime = "0";

    this._loadReactions();
  }

  /**
   * Load a list of reactions.
   */
  protected _loadReactions(): void {
    this._options.parameters.userID = this._userID;
    this._options.parameters.lastLikeTime = ~~this._container.dataset.lastLikeTime!;
    this._options.parameters.targetType = this._targetType;
    this._options.parameters.reactionTypeID = ~~this._reactionTypeID!;

    Ajax.api(this, {
      parameters: this._options.parameters,
    });
  }

  _ajaxSuccess(data: AjaxResponse): void {
    if (data.returnValues.template) {
      document
        .querySelector("#likeList > li:nth-last-child(1)")!
        .insertAdjacentHTML("beforebegin", data.returnValues.template);

      this._container.dataset.lastLikeTime = data.returnValues.lastLikeTime.toString();
      DomUtil.hide(this._noMoreEntries);
      DomUtil.show(this._loadButton);
    } else {
      DomUtil.show(this._noMoreEntries);
      DomUtil.hide(this._loadButton);
    }
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        actionName: "load",
        className: "\\wcf\\data\\reaction\\ReactionAction",
      },
    };
  }
}

Core.enableLegacyInheritance(UiReactionProfileLoader);

export = UiReactionProfileLoader;
