/**
 * Provides interface elements to display and review likes.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Like/Handler
 * @deprecated  5.2 use ReactionHandler instead
 */

import * as Core from "../../Core";
import DomChangeListener from "../../Dom/Change/Listener";
import * as Language from "../../Language";
import * as StringUtil from "../../StringUtil";
import UiReactionHandler from "../Reaction/Handler";
import User from "../../User";

interface LikeHandlerOptions {
  // settings
  badgeClassNames: string;
  isSingleItem: boolean;
  markListItemAsActive: boolean;
  renderAsButton: boolean;
  summaryPrepend: boolean;
  summaryUseIcon: boolean;

  // permissions
  canDislike: boolean;
  canLike: boolean;
  canLikeOwnContent: boolean;
  canViewSummary: boolean;

  // selectors
  badgeContainerSelector: string;
  buttonAppendToSelector: string;
  buttonBeforeSelector: string;
  containerSelector: string;
  summarySelector: string;
}

interface LikeUsers {
  [key: string]: number;
}

interface ElementData {
  badge: HTMLUListElement | null;
  dislikeButton: null;
  likeButton: HTMLAnchorElement | null;
  summary: null;

  dislikes: number;
  liked: number;
  likes: number;
  objectId: number;
  users: LikeUsers;
}

class UiLikeHandler {
  protected readonly _containers = new Map<HTMLElement, ElementData>();
  protected readonly _objectType: string;
  protected readonly _options: LikeHandlerOptions;

  /**
   * Initializes the like handler.
   */
  constructor(objectType: string, opts: Partial<LikeHandlerOptions>) {
    if (!opts.containerSelector) {
      throw new Error(
        "[WoltLabSuite/Core/Ui/Like/Handler] Expected a non-empty string for option 'containerSelector'.",
      );
    }

    this._objectType = objectType;
    this._options = Core.extend(
      {
        // settings
        badgeClassNames: "",
        isSingleItem: false,
        markListItemAsActive: false,
        renderAsButton: true,
        summaryPrepend: true,
        summaryUseIcon: true,

        // permissions
        canDislike: false,
        canLike: false,
        canLikeOwnContent: false,
        canViewSummary: false,

        // selectors
        badgeContainerSelector: ".messageHeader .messageStatus",
        buttonAppendToSelector: ".messageFooter .messageFooterButtons",
        buttonBeforeSelector: "",
        containerSelector: "",
        summarySelector: ".messageFooterGroup",
      },
      opts,
    ) as LikeHandlerOptions;

    this.initContainers();

    DomChangeListener.add(`WoltLabSuite/Core/Ui/Like/Handler-${objectType}`, () => this.initContainers());

    new UiReactionHandler(this._objectType, {
      containerSelector: this._options.containerSelector,
    });
  }

  /**
   * Initializes all applicable containers.
   */
  initContainers(): void {
    let triggerChange = false;

    document.querySelectorAll(this._options.containerSelector).forEach((element: HTMLElement) => {
      if (this._containers.has(element)) {
        return;
      }

      const elementData = {
        badge: null,
        dislikeButton: null,
        likeButton: null,
        summary: null,

        dislikes: ~~element.dataset.likeDislikes!,
        liked: ~~element.dataset.likeLiked!,
        likes: ~~element.dataset.likeLikes!,
        objectId: ~~element.dataset.objectId!,
        users: JSON.parse(element.dataset.likeUsers!),
      };

      this._containers.set(element, elementData);
      this._buildWidget(element, elementData);

      triggerChange = true;
    });

    if (triggerChange) {
      DomChangeListener.trigger();
    }
  }

  /**
   * Creates the interface elements.
   */
  protected _buildWidget(element: HTMLElement, elementData: ElementData): void {
    let badgeContainer: HTMLElement | null;
    let isSummaryPosition = true;

    if (this._options.isSingleItem) {
      badgeContainer = document.querySelector(this._options.summarySelector);
    } else {
      badgeContainer = element.querySelector(this._options.summarySelector);
    }

    if (badgeContainer === null) {
      if (this._options.isSingleItem) {
        badgeContainer = document.querySelector(this._options.badgeContainerSelector);
      } else {
        badgeContainer = element.querySelector(this._options.badgeContainerSelector);
      }

      isSummaryPosition = false;
    }

    if (badgeContainer !== null) {
      const summaryList = document.createElement("ul");
      summaryList.classList.add("reactionSummaryList");
      if (isSummaryPosition) {
        summaryList.classList.add("likesSummary");
      } else {
        summaryList.classList.add("reactionSummaryListTiny");
      }

      const availableReactions = new Map(Object.entries(window.REACTION_TYPES));
      Object.entries(elementData.users).forEach(([reactionTypeId, count]) => {
        const reaction = availableReactions.get(reactionTypeId);
        if (reactionTypeId === "reactionTypeID" || !reaction) {
          return;
        }

        // create element
        const createdElement = document.createElement("li");
        createdElement.className = "reactCountButton";
        createdElement.setAttribute("reaction-type-id", reactionTypeId);

        const countSpan = document.createElement("span");
        countSpan.className = "reactionCount";
        countSpan.innerHTML = StringUtil.shortUnit(~~count);
        createdElement.appendChild(countSpan);

        createdElement.innerHTML = reaction.renderedIcon + createdElement.innerHTML;

        summaryList.appendChild(createdElement);
      });

      if (isSummaryPosition) {
        if (this._options.summaryPrepend) {
          badgeContainer.insertAdjacentElement("afterbegin", summaryList);
        } else {
          badgeContainer.insertAdjacentElement("beforeend", summaryList);
        }
      } else {
        if (badgeContainer.nodeName === "OL" || badgeContainer.nodeName === "UL") {
          const listItem = document.createElement("li");
          listItem.appendChild(summaryList);
          badgeContainer.appendChild(listItem);
        } else {
          badgeContainer.appendChild(summaryList);
        }
      }

      elementData.badge = summaryList;
    }

    // build reaction button
    if (this._options.canLike && (User.userId != ~~element.dataset.userId! || this._options.canLikeOwnContent)) {
      let appendTo: HTMLElement | null = null;
      if (this._options.buttonAppendToSelector) {
        if (this._options.isSingleItem) {
          appendTo = document.querySelector(this._options.buttonAppendToSelector);
        } else {
          appendTo = element.querySelector(this._options.buttonAppendToSelector);
        }
      }

      let insertPosition: HTMLElement | null = null;
      if (this._options.buttonBeforeSelector) {
        if (this._options.isSingleItem) {
          insertPosition = document.querySelector(this._options.buttonBeforeSelector);
        } else {
          insertPosition = element.querySelector(this._options.buttonBeforeSelector);
        }
      }

      if (insertPosition === null && appendTo === null) {
        throw new Error("Unable to find insert location for like/dislike buttons.");
      } else {
        elementData.likeButton = this._createButton(
          element,
          elementData.users.reactionTypeID,
          insertPosition,
          appendTo,
        );
      }
    }
  }

  /**
   * Creates a reaction button.
   */
  protected _createButton(
    element: HTMLElement,
    reactionTypeID: number,
    insertBefore: HTMLElement | null,
    appendTo: HTMLElement | null,
  ): HTMLAnchorElement {
    const title = Language.get("wcf.reactions.react");

    const listItem = document.createElement("li");
    listItem.className = "wcfReactButton";

    const button = document.createElement("a");
    button.className = "jsTooltip reactButton";
    if (this._options.renderAsButton) {
      button.classList.add("button");
    }

    button.href = "#";
    button.title = title;

    const icon = document.createElement("span");
    icon.className = "icon icon16 fa-smile-o";

    if (reactionTypeID === undefined || reactionTypeID == 0) {
      icon.dataset.reactionTypeId = "0";
    } else {
      button.dataset.reactionTypeId = reactionTypeID.toString();
      button.classList.add("active");
    }

    button.appendChild(icon);

    const invisibleText = document.createElement("span");
    invisibleText.className = "invisible";
    invisibleText.innerHTML = title;

    button.appendChild(document.createTextNode(" "));
    button.appendChild(invisibleText);

    listItem.appendChild(button);

    if (insertBefore) {
      insertBefore.insertAdjacentElement("beforebegin", listItem);
    } else {
      appendTo!.insertAdjacentElement("beforeend", listItem);
    }

    return button;
  }
}

Core.enableLegacyInheritance(UiLikeHandler);

export = UiLikeHandler;
