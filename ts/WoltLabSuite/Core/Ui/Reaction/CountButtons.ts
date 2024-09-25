/**
 * Provides interface elements to use reactions.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 * @deprecated  6.0 use <woltlab-core-reaction-summary> instead
 */

import * as Ajax from "../../Ajax";
import { AjaxCallbackSetup, ResponseData } from "../../Ajax/Data";
import * as Core from "../../Core";
import { DialogCallbackSetup } from "../Dialog/Data";
import DomChangeListener from "../../Dom/Change/Listener";
import DomUtil from "../../Dom/Util";
import * as EventHandler from "../../Event/Handler";
import { Reaction, ReactionStats } from "./Data";
import * as StringUtil from "../../StringUtil";
import UiDialog from "../Dialog";

interface CountButtonsOptions {
  // selectors
  summaryListSelector: string;
  containerSelector: string;
  isSingleItem: boolean;

  // optional parameters
  parameters: {
    data: {
      [key: string]: unknown;
    };
  };
}

interface ElementData {
  element: HTMLElement;
  objectId: number;
  reactButton: null;
  summary: null;
}

interface AjaxResponse extends ResponseData {
  returnValues: {
    template: string;
    title: string;
  };
}

const availableReactions = new Map<string, Reaction>(Object.entries(window.REACTION_TYPES));

class CountButtons {
  protected readonly _containers = new Map<string, ElementData>();
  protected _currentObjectId = 0;
  protected readonly _objects = new Map<number, ElementData[]>();
  protected readonly _objectType: string;
  protected readonly _options: CountButtonsOptions;

  /**
   * Initializes the like handler.
   */
  constructor(objectType: string, opts: Partial<CountButtonsOptions>) {
    if (!opts.containerSelector) {
      throw new Error(
        "[WoltLabSuite/Core/Ui/Reaction/CountButtons] Expected a non-empty string for option 'containerSelector'.",
      );
    }

    this._objectType = objectType;

    this._options = Core.extend(
      {
        // selectors
        summaryListSelector: ".reactionSummaryList",
        containerSelector: "",
        isSingleItem: false,

        // optional parameters
        parameters: {
          data: {},
        },
      },
      opts,
    ) as CountButtonsOptions;

    this.initContainers();

    DomChangeListener.add(`WoltLabSuite/Core/Ui/Reaction/CountButtons-${objectType}`, () => this.initContainers());
  }

  /**
   * Initialises the containers.
   */
  initContainers(): void {
    let triggerChange = false;
    document.querySelectorAll(this._options.containerSelector).forEach((element: HTMLElement) => {
      const elementId = DomUtil.identify(element);
      if (this._containers.has(elementId)) {
        return;
      }

      const objectId = ~~element.dataset.objectId!;
      const elementData: ElementData = {
        reactButton: null,
        summary: null,

        objectId: objectId,
        element: element,
      };

      this._containers.set(elementId, elementData);
      this._initReactionCountButtons(element, elementData);

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
   * Update the count buttons with the given data.
   */
  updateCountButtons(objectId: number, data: ReactionStats): void {
    let triggerChange = false;
    this._objects.get(objectId)!.forEach((elementData) => {
      let summaryList: HTMLElement | null;
      if (this._options.isSingleItem) {
        summaryList = document.querySelector(this._options.summaryListSelector);
      } else {
        summaryList = elementData.element.querySelector(this._options.summaryListSelector);
      }

      // summary list for the object not found; abort
      if (summaryList === null) {
        return;
      }

      const existingReactions = new Map<string, number>(Object.entries(data));

      const sortedElements = new Map<string, HTMLElement>();
      summaryList.querySelectorAll(".reactCountButton").forEach((reaction: HTMLElement) => {
        const reactionTypeId = reaction.dataset.reactionTypeId!;
        if (existingReactions.has(reactionTypeId)) {
          sortedElements.set(reactionTypeId, reaction);
        } else {
          // The reaction no longer has any reactions.
          reaction.remove();
        }
      });

      existingReactions.forEach((count, reactionTypeId) => {
        if (sortedElements.has(reactionTypeId)) {
          const reaction = sortedElements.get(reactionTypeId)!;
          const reactionCount = reaction.querySelector(".reactionCount") as HTMLElement;
          reactionCount.innerHTML = StringUtil.shortUnit(count);
        } else if (availableReactions.has(reactionTypeId)) {
          const createdElement = document.createElement("span");
          createdElement.className = "reactCountButton";
          createdElement.innerHTML = availableReactions.get(reactionTypeId)!.renderedIcon;
          createdElement.dataset.reactionTypeId = reactionTypeId;

          const countSpan = document.createElement("span");
          countSpan.className = "reactionCount";
          countSpan.innerHTML = StringUtil.shortUnit(count);
          createdElement.appendChild(countSpan);

          summaryList.appendChild(createdElement);

          triggerChange = true;
        }
      });

      if (summaryList.childElementCount > 0) {
        DomUtil.show(summaryList);
      } else {
        DomUtil.hide(summaryList);
      }
    });

    if (triggerChange) {
      DomChangeListener.trigger();
    }
  }

  /**
   * Initialized the reaction count buttons.
   */
  protected _initReactionCountButtons(element: HTMLElement, elementData: ElementData): void {
    let summaryList: HTMLElement | null;
    if (this._options.isSingleItem) {
      summaryList = document.querySelector(this._options.summaryListSelector);
    } else {
      summaryList = element.querySelector(this._options.summaryListSelector);
    }

    if (summaryList !== null) {
      summaryList.addEventListener("click", (ev) => this._showReactionOverlay(elementData.objectId, ev));
    }
  }

  /**
   * Shows the reaction overly for a specific object.
   */
  protected _showReactionOverlay(objectId: number, event: MouseEvent): void {
    event.preventDefault();

    this._currentObjectId = objectId;
    this._showOverlay();
  }

  /**
   * Shows a specific page of the current opened reaction overlay.
   */
  protected _showOverlay(): void {
    this._options.parameters.data.containerID = `${this._objectType}-${this._currentObjectId}`;
    this._options.parameters.data.objectID = this._currentObjectId;
    this._options.parameters.data.objectType = this._objectType;

    Ajax.api(this, {
      parameters: this._options.parameters,
    });
  }

  _ajaxSuccess(data: AjaxResponse): void {
    EventHandler.fire("com.woltlab.wcf.ReactionCountButtons", "openDialog", data);

    UiDialog.open(this, data.returnValues.template);
    UiDialog.setTitle("userReactionOverlay-" + this._objectType, data.returnValues.title);
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        actionName: "getReactionDetails",
        className: "\\wcf\\data\\reaction\\ReactionAction",
      },
    };
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: `userReactionOverlay-${this._objectType}`,
      options: {
        title: "",
      },
      source: null,
    };
  }
}

export = CountButtons;
