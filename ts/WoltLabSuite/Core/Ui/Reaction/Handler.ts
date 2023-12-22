/**
 * Provides interface elements to use reactions.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 */

import * as Ajax from "../../Ajax";
import { AjaxCallbackSetup } from "../../Ajax/Data";
import * as Core from "../../Core";
import DomChangeListener from "../../Dom/Change/Listener";
import DomUtil from "../../Dom/Util";
import * as UiAlignment from "../Alignment";
import UiCloseOverlay from "../CloseOverlay";
import * as UiScreen from "../Screen";
import { Reaction, ReactionStats } from "./Data";
import { createFocusTrap, FocusTrap } from "focus-trap";

interface ReactionHandlerOptions {
  // selectors
  buttonSelector: string;
  containerSelector: string;
  isButtonGroupNavigation: boolean;
  isSingleItem: boolean;

  // other stuff
  parameters: {
    data: {
      [key: string]: unknown;
    };
    reactionTypeID?: number;
  };
}

interface ElementData {
  reactButton: HTMLElement | null;
  objectId: number;
  element: HTMLElement;
}

interface AjaxResponse {
  returnValues: {
    objectID: number | string;
    objectType: string;
    reactions: ReactionStats;
    reactionTypeID: number;
    reputationCount: number;
  };
}

const availableReactions = Object.values(window.REACTION_TYPES);

class UiReactionHandler {
  protected activeButton?: HTMLElement | undefined = undefined;
  protected readonly _cache = new Map<string, unknown>();
  protected focusTrap?: FocusTrap = undefined;
  protected readonly _containers = new Map<string, ElementData>();
  protected readonly _options: ReactionHandlerOptions;
  protected readonly _objects = new Map<number, ElementData[]>();
  protected readonly _objectType: string;
  protected _popoverCurrentObjectId = 0;
  protected _popover: HTMLElement | null;
  protected _popoverContent: HTMLElement | null;

  /**
   * Initializes the reaction handler.
   */
  constructor(objectType: string, opts: Partial<ReactionHandlerOptions>) {
    if (!opts.containerSelector) {
      throw new Error(
        "[WoltLabSuite/Core/Ui/Reaction/Handler] Expected a non-empty string for option 'containerSelector'.",
      );
    }

    this._objectType = objectType;

    this._popover = null;
    this._popoverContent = null;

    this._options = Core.extend(
      {
        // selectors
        buttonSelector: ".reactButton",
        containerSelector: "",
        isButtonGroupNavigation: false,
        isSingleItem: false,

        // other stuff
        parameters: {
          data: {},
        },
      },
      opts,
    ) as ReactionHandlerOptions;

    this.initReactButtons();

    DomChangeListener.add(`WoltLabSuite/Core/Ui/Reaction/Handler-${objectType}`, () => this.initReactButtons());
    UiCloseOverlay.add("WoltLabSuite/Core/Ui/Reaction/Handler", () => this._closePopover());
  }

  /**
   * Initializes all applicable react buttons with the given selector.
   */
  initReactButtons(): void {
    let triggerChange = false;

    document.querySelectorAll(this._options.containerSelector).forEach((element: HTMLElement) => {
      const elementId = DomUtil.identify(element);
      if (this._containers.has(elementId)) {
        return;
      }

      const objectId = ~~element.dataset.objectId!;
      const elementData: ElementData = {
        reactButton: null,
        objectId: objectId,
        element: element,
      };

      this._containers.set(elementId, elementData);
      this._initReactButton(element, elementData);

      const objects = this._objects.get(objectId) || [];

      objects.push(elementData);

      this._objects.set(objectId, objects);

      triggerChange = true;
    });

    if (triggerChange) {
      DomChangeListener.trigger();
    }
  }

  /**
   * Initializes a specific react button.
   */
  _initReactButton(element: HTMLElement, elementData: ElementData): void {
    if (this._options.isSingleItem) {
      elementData.reactButton = document.querySelector(this._options.buttonSelector) as HTMLElement;
    } else {
      elementData.reactButton = element.querySelector(this._options.buttonSelector) as HTMLElement;
    }

    if (elementData.reactButton === null) {
      // The element may have no react button.
      return;
    }

    if (availableReactions.length === 1) {
      const reaction = availableReactions[0];
      elementData.reactButton.title = reaction.title;
      const textSpan = elementData.reactButton.querySelector(".invisible")!;
      textSpan.textContent = reaction.title;
    }

    elementData.reactButton.setAttribute("role", "button");
    if (availableReactions.length > 1) {
      elementData.reactButton.setAttribute("aria-haspopup", "true");
      elementData.reactButton.setAttribute("aria-expanded", "false");
    }

    elementData.reactButton.addEventListener("click", (ev) => {
      this._toggleReactPopover(elementData.objectId, elementData.reactButton!, ev);
    });
    elementData.reactButton.addEventListener("keydown", (event) => {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();

        this._toggleReactPopover(elementData.objectId, elementData.reactButton!, null);
      }
    });
  }

  protected _updateReactButton(objectID: number, reactionTypeID: number): void {
    this._objects.get(objectID)!.forEach((elementData) => {
      if (elementData.reactButton !== null) {
        if (reactionTypeID) {
          elementData.reactButton.classList.add("active");
          elementData.reactButton.dataset.reactionTypeId = reactionTypeID.toString();
        } else {
          elementData.reactButton.dataset.reactionTypeId = "0";
          elementData.reactButton.classList.remove("active");
        }
      }
    });
  }

  protected _markReactionAsActive(): void {
    let reactionTypeID: number | null = null;
    for (const element of this._objects.get(this._popoverCurrentObjectId)!) {
      if (element.reactButton !== null) {
        reactionTypeID = ~~element.reactButton.dataset.reactionTypeId!;
      }
    }

    if (reactionTypeID === null) {
      throw new Error("Unable to find react button for current popover.");
    }

    //  Clear the old active state.
    const popover = this._getPopover();
    popover.querySelectorAll(".reactionTypeButton.active").forEach((element: HTMLElement) => {
      element.classList.remove("active");
      element.removeAttribute("aria-selected");
    });

    const scrollableContainer = popover.querySelector(".reactionPopoverContent") as HTMLElement;
    if (reactionTypeID) {
      const reactionTypeButton = popover.querySelector(
        `.reactionTypeButton[data-reaction-type-id="${reactionTypeID}"]`,
      ) as HTMLElement;
      reactionTypeButton.classList.add("active");
      reactionTypeButton.setAttribute("aria-selected", "true");

      if (~~reactionTypeButton.dataset.isAssignable! === 0) {
        DomUtil.show(reactionTypeButton);
      }

      this._scrollReactionIntoView(scrollableContainer, reactionTypeButton);
    } else {
      // The "first" reaction is positioned as close as possible to the toggle button,
      // which means that we need to scroll the list to the bottom if the popover is
      // displayed above the toggle button.
      if (UiScreen.is("screen-xs")) {
        if (popover.classList.contains("inverseOrder")) {
          scrollableContainer.scrollTop = 0;
        } else {
          scrollableContainer.scrollTop = scrollableContainer.scrollHeight - scrollableContainer.clientHeight;
        }
      }
    }
  }

  protected _scrollReactionIntoView(scrollableContainer: HTMLElement, reactionTypeButton: HTMLElement): void {
    // Do not scroll if the button is located in the upper 75%.
    if (reactionTypeButton.offsetTop < scrollableContainer.clientHeight * 0.75) {
      scrollableContainer.scrollTop = 0;
    } else {
      // `Element.scrollTop` permits arbitrary values and will always clamp them to
      // the maximum possible offset value. We can abuse this behavior by calculating
      // the values to place the selected reaction in the center of the popover,
      // regardless of the offset being out of range.
      scrollableContainer.scrollTop =
        reactionTypeButton.offsetTop + reactionTypeButton.clientHeight / 2 - scrollableContainer.clientHeight / 2;
    }
  }

  /**
   * Toggle the visibility of the react popover.
   */
  protected _toggleReactPopover(objectId: number, element: HTMLElement, event: MouseEvent | null): void {
    if (event !== null) {
      event.preventDefault();
      event.stopPropagation();
    }

    if (availableReactions.length === 1) {
      const reaction = availableReactions[0];
      this._popoverCurrentObjectId = objectId;

      this._react(reaction.reactionTypeID);
    } else {
      if (this._popoverCurrentObjectId === 0 || this._popoverCurrentObjectId !== objectId) {
        this._openReactPopover(objectId, element);
      } else {
        this._closePopover();
      }
    }
  }

  /**
   * Opens the react popover for a specific react button.
   */
  protected _openReactPopover(objectId: number, element: HTMLElement): void {
    if (this._popoverCurrentObjectId !== 0) {
      this._closePopover();
    }

    this._popoverCurrentObjectId = objectId;

    UiAlignment.set(this._getPopover(), element, {
      horizontal: this._options.isButtonGroupNavigation ? "left" : "center",
      vertical: UiScreen.is("screen-xs") ? "bottom" : "top",
    });

    if (this._options.isButtonGroupNavigation) {
      element.closest("nav")!.style.setProperty("opacity", "1", "");
    }

    const popover = this._getPopover();

    // The popover could be rendered below the input field on mobile, in which case
    // the "first" button is displayed at the bottom and thus farthest away. Reversing
    // the display order will restore the logic by placing the "first" button as close
    // to the react button as possible.
    const inverseOrder = popover.style.getPropertyValue("bottom") === "auto";
    if (inverseOrder) {
      popover.classList.add("inverseOrder");
    } else {
      popover.classList.remove("inverseOrder");
    }

    this._markReactionAsActive();

    this._rebuildOverflowIndicator();

    popover.classList.remove("forceHide");
    popover.classList.add("active");

    this.activeButton = element;
    if (availableReactions.length > 1) {
      this.activeButton.setAttribute("aria-expanded", "true");
    }

    this.getFocusTrap().activate();
  }

  /**
   * Returns the react popover element.
   */
  protected _getPopover(): HTMLElement {
    if (this._popover == null) {
      this._popover = document.createElement("div");
      this._popover.className = "reactionPopover forceHide";

      this._popoverContent = document.createElement("div");
      this._popoverContent.className = "reactionPopoverContent";

      const popoverContentHTML = document.createElement("ul");
      popoverContentHTML.className = "reactionTypeButtonList";

      this._getSortedReactionTypes().forEach((reactionType) => {
        const reactionTypeItem = document.createElement("li");
        reactionTypeItem.tabIndex = 0;
        reactionTypeItem.setAttribute("role", "button");
        reactionTypeItem.className = "reactionTypeButton jsTooltip";
        reactionTypeItem.dataset.reactionTypeId = reactionType.reactionTypeID.toString();
        reactionTypeItem.dataset.title = reactionType.title;
        reactionTypeItem.dataset.isAssignable = reactionType.isAssignable.toString();

        reactionTypeItem.title = reactionType.title;

        const reactionTypeItemSpan = document.createElement("span");
        reactionTypeItemSpan.className = "reactionTypeButtonTitle";
        reactionTypeItemSpan.innerHTML = reactionType.title;

        reactionTypeItem.innerHTML = reactionType.renderedIcon;

        reactionTypeItem.appendChild(reactionTypeItemSpan);

        reactionTypeItem.addEventListener("click", () => this._react(reactionType.reactionTypeID));
        reactionTypeItem.addEventListener("keydown", (ev) => this.keydown(ev));

        if (!reactionType.isAssignable) {
          DomUtil.hide(reactionTypeItem);
        }

        popoverContentHTML.appendChild(reactionTypeItem);
      });

      this._popoverContent.appendChild(popoverContentHTML);
      this._popoverContent.addEventListener("scroll", () => this._rebuildOverflowIndicator(), { passive: true });

      this._popover.appendChild(this._popoverContent);

      document.body.appendChild(this._popover);

      DomChangeListener.trigger();
    }

    return this._popover;
  }

  protected keydown(event: KeyboardEvent): void {
    if (event.key === "Enter" || event.key === " ") {
      event.preventDefault();

      const activeButton = this.activeButton!;
      const reactionTypeItem = event.currentTarget as HTMLElement;
      const reactionTypeId = ~~reactionTypeItem.dataset.reactionTypeId!;

      this._react(reactionTypeId);

      activeButton.focus();
    }
  }

  protected _rebuildOverflowIndicator(): void {
    const popoverContent = this._popoverContent!;
    const hasTopOverflow = popoverContent.scrollTop > 0;
    if (hasTopOverflow) {
      popoverContent.classList.add("overflowTop");
    } else {
      popoverContent.classList.remove("overflowTop");
    }

    const hasBottomOverflow = popoverContent.scrollTop + popoverContent.clientHeight < popoverContent.scrollHeight;
    if (hasBottomOverflow) {
      popoverContent.classList.add("overflowBottom");
    } else {
      popoverContent.classList.remove("overflowBottom");
    }
  }

  /**
   * Sort the reaction types by the showOrder field.
   */
  protected _getSortedReactionTypes(): Reaction[] {
    return availableReactions.sort((a, b) => a.showOrder - b.showOrder);
  }

  /**
   * Closes the react popover.
   */
  protected _closePopover(): void {
    if (this._popoverCurrentObjectId !== 0) {
      const popover = this._getPopover();
      popover.classList.remove("active");

      popover
        .querySelectorAll('.reactionTypeButton[data-is-assignable="0"]')
        .forEach((el: HTMLElement) => DomUtil.hide(el));

      if (this._options.isButtonGroupNavigation) {
        this._objects.get(this._popoverCurrentObjectId)!.forEach((elementData) => {
          elementData.reactButton!.closest("nav")!.style.cssText = "";
        });
      }

      if (availableReactions.length > 1) {
        this.activeButton!.setAttribute("aria-expanded", "false");
      }

      this.activeButton = undefined;
      this._popoverCurrentObjectId = 0;

      this.getFocusTrap().deactivate();
    }
  }

  /**
   * React with the given reactionTypeId on an object.
   */
  protected _react(reactionTypeId: number): void {
    if (~~this._popoverCurrentObjectId === 0) {
      // Double clicking the reaction will cause the first click to go through, but
      // causes the second to fail because the overlay is already closing.
      return;
    }

    this._options.parameters.reactionTypeID = reactionTypeId;
    this._options.parameters.data.objectID = this._popoverCurrentObjectId;
    this._options.parameters.data.objectType = this._objectType;

    Ajax.api(this, {
      parameters: this._options.parameters,
    });

    this._closePopover();
  }

  _ajaxSuccess(data: AjaxResponse): void {
    const objectId = ~~data.returnValues.objectID;

    const reactions = new Map<number, number>();
    Object.entries(data.returnValues.reactions).forEach(([key, value]) => {
      reactions.set(parseInt(key), value);
    });

    const component = document.querySelector(
      `woltlab-core-reaction-summary[object-type="${this._objectType}"][object-id="${objectId}"]`,
    ) as WoltlabCoreReactionSummaryElement;
    component?.setData(reactions, data.returnValues.reactionTypeID);

    this._updateReactButton(objectId, data.returnValues.reactionTypeID);
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        actionName: "react",
        className: "\\wcf\\data\\reaction\\ReactionAction",
      },
    };
  }

  private getFocusTrap(): FocusTrap {
    if (this.focusTrap === undefined) {
      this.focusTrap = createFocusTrap(this._popover!, {
        allowOutsideClick: true,
        escapeDeactivates: (): boolean => {
          this._closePopover();

          return false;
        },
      });
    }

    return this.focusTrap;
  }
}

export = UiReactionHandler;
