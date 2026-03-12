/**
 * Handles the reactions buttons.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */

import { revertReaction } from "WoltLabSuite/Core/Api/Reactions/RevertReaction";
import { setReaction } from "WoltLabSuite/Core/Api/Reactions/SetReaction";
import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";
import { Reaction } from "WoltLabSuite/Core/Ui/Reaction/Data";
import DomChangeListener from "WoltLabSuite/Core/Dom/Change/Listener";
import { createFocusTrap, FocusTrap } from "focus-trap";
import * as UiAlignment from "WoltLabSuite/Core/Ui/Alignment";
import * as UiScreen from "WoltLabSuite/Core/Ui/Screen";
import UiCloseOverlay from "WoltLabSuite/Core/Ui/CloseOverlay";

type Result =
  | {
      ok: true;
      reactionTypeId: number;
    }
  | {
      ok: false;
    };

const availableReactions = Object.values(window.REACTION_TYPES);

class ReactionPopover {
  #resolve?: (value: Result) => void;
  #popover?: HTMLElement;
  #focusTrap?: FocusTrap;

  open(button: HTMLButtonElement): Promise<Result> {
    if (this.#resolve !== undefined) {
      this.#resolve({ ok: false });
    }

    this.#showPopover(button);

    return new Promise<Result>((resolve) => {
      this.#resolve = resolve;
    });
  }

  cancel(): void {
    if (this.#resolve !== undefined) {
      this.#resolve({ ok: false });
    }
    this.#resolve = undefined;
    this.#hidePopover();
  }

  #click(reactionTypeId: number): void {
    if (this.#resolve !== undefined) {
      this.#resolve({ ok: true, reactionTypeId });
    }
    this.#resolve = undefined;
    this.#hidePopover();
  }

  #createPopover(): void {
    if (this.#popover !== undefined) {
      return;
    }

    this.#popover = document.createElement("div");
    this.#popover.className = "reactionPopover forceHide";

    const popoverContent = document.createElement("div");
    popoverContent.className = "reactionPopoverContent";

    this.#getSortedReactionTypes().forEach((reactionType) => {
      const reactionTypeButton = document.createElement("button");
      reactionTypeButton.type = "button";
      reactionTypeButton.tabIndex = 0;
      reactionTypeButton.className = "reactionTypeButton jsTooltip";
      reactionTypeButton.title = reactionType.title;
      reactionTypeButton.dataset.reactionTypeId = reactionType.reactionTypeID.toString();
      reactionTypeButton.dataset.isAssignable = reactionType.isAssignable.toString();
      reactionTypeButton.innerHTML = reactionType.renderedIcon;
      reactionTypeButton.addEventListener("click", () => this.#click(reactionType.reactionTypeID));

      if (!reactionType.isAssignable) {
        reactionTypeButton.hidden = true;
      }

      popoverContent.appendChild(reactionTypeButton);
    });

    this.#popover.appendChild(popoverContent);

    document.body.appendChild(this.#popover);

    UiCloseOverlay.add("WoltLabSuite/Core/Component/Reaction/Button", () => this.cancel());

    window.addEventListener(
      "resize",
      () => {
        this.cancel();
      },
      { passive: true },
    );

    DomChangeListener.trigger();
  }

  #showPopover(button: HTMLButtonElement): void {
    const popover = this.#getPopover();

    UiAlignment.set(popover, button, {
      horizontal: "center",
      vertical: UiScreen.is("screen-xs") ? "bottom" : "top",
    });

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

    popover.querySelectorAll(".reactionTypeButton.active").forEach((element: HTMLElement) => {
      element.classList.remove("active");
    });

    if (parseInt(button.dataset.reactionTypeId!)) {
      const reactionTypeButton = popover.querySelector(
        `.reactionTypeButton[data-reaction-type-id="${button.dataset.reactionTypeId}"]`,
      ) as HTMLButtonElement;
      reactionTypeButton.classList.add("active");
      reactionTypeButton.hidden = false;
    }

    popover.classList.remove("forceHide");
    popover.classList.add("active");

    this.#getFocusTrap().activate();
  }

  #hidePopover(): void {
    const popover = this.#getPopover();
    popover.classList.remove("active");

    popover
      .querySelectorAll('.reactionTypeButton[data-is-assignable="0"]')
      .forEach((button: HTMLButtonElement) => (button.hidden = true));

    this.#getFocusTrap().deactivate();
  }

  #getSortedReactionTypes(): Reaction[] {
    return availableReactions.sort((a, b) => a.showOrder - b.showOrder);
  }

  #getPopover(): HTMLElement {
    if (this.#popover === undefined) {
      this.#createPopover();
    }

    return this.#popover!;
  }

  #getFocusTrap(): FocusTrap {
    if (this.#focusTrap === undefined) {
      this.#focusTrap = createFocusTrap(this.#getPopover(), {
        allowOutsideClick: true,
        escapeDeactivates: (): boolean => {
          this.#hidePopover();

          return false;
        },
        preventScroll: true,
      });
    }

    return this.#focusTrap;
  }
}

function updateReactionSummary(
  objectType: string,
  objectId: number,
  cachedReactions: Record<number, number>,
  selectedReaction?: number,
): void {
  const reactions = new Map<number, number>();
  Object.entries(cachedReactions).forEach(([key, value]) => {
    reactions.set(parseInt(key), value);
  });

  const component = document.querySelector(
    `woltlab-core-reaction-summary[object-type="${objectType}"][object-id="${objectId}"]`,
  ) as WoltlabCoreReactionSummaryElement;
  component?.setData(reactions, selectedReaction);
}

export function setup(): void {
  const reactionPopover = new ReactionPopover();

  wheneverFirstSeen("[data-reaction-object-type]", (button: HTMLButtonElement) => {
    let isOpen = false;
    button.addEventListener("click", (event) => {
      event.stopPropagation(); // Necessary so that `Ui/CloseOverlay` does not close the popover immediately

      if (isOpen) {
        reactionPopover.cancel();
        isOpen = false;
        return;
      }

      isOpen = true;
      void reactionPopover.open(button).then(async (result: Result) => {
        isOpen = false;

        if (!result.ok) {
          return;
        }

        const oldReactionTypeId = parseInt(button.dataset.reactionTypeId!);

        if (result.reactionTypeId == oldReactionTypeId) {
          const response = await revertReaction(button.dataset.reactionObjectType!, parseInt(button.dataset.objectId!));
          button.dataset.reactionTypeId = "0";
          button.classList.remove("active");

          updateReactionSummary(
            button.dataset.reactionObjectType!,
            parseInt(button.dataset.objectId!),
            response.unwrap().reactions,
            result.reactionTypeId,
          );
        } else {
          const response = await setReaction(
            button.dataset.reactionObjectType!,
            parseInt(button.dataset.objectId!),
            result.reactionTypeId,
          );
          button.dataset.reactionTypeId = result.reactionTypeId.toString();
          button.classList.add("active");

          updateReactionSummary(
            button.dataset.reactionObjectType!,
            parseInt(button.dataset.objectId!),
            response.unwrap().reactions,
            result.reactionTypeId,
          );
        }
      });
    });
  });
}
