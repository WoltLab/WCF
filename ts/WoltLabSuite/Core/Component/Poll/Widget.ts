/**
 * Handles the widget of standalone polls that have been embedded into a
 * message. The widget contains both the vote form and the results, switching
 * between them does not require a request.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { getWidget } from "WoltLabSuite/Core/Api/Polls/GetWidget";
import { vote } from "WoltLabSuite/Core/Api/Polls/Vote";
import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import { trigger as triggerDomChange } from "WoltLabSuite/Core/Dom/Change/Listener";
import DomUtil from "WoltLabSuite/Core/Dom/Util";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";
import UiDropdownSimple from "WoltLabSuite/Core/Ui/Dropdown/Simple";

type State = "vote" | "results";

class PollWidget {
  readonly #element: HTMLElement;
  readonly #inputs: HTMLInputElement[];
  readonly #maxVotes: number;
  readonly #requiredVotes: number;
  readonly #pollId: number;
  readonly #voteButton: HTMLButtonElement | null;

  constructor(element: HTMLElement) {
    this.#element = element;
    this.#inputs = Array.from(element.querySelectorAll<HTMLInputElement>(".pollWidget__input"));
    this.#maxVotes = parseInt(element.dataset.maxVotes!);
    this.#requiredVotes = parseInt(element.dataset.requiredVotes!);
    this.#pollId = parseInt(element.dataset.pollId!);
    this.#voteButton = element.querySelector<HTMLButtonElement>(".pollWidget__vote");

    this.#initEventListeners();
    this.#initInteractions();
    this.#setState(element.dataset.state as State, false);
    this.#updateInputs();
  }

  #initEventListeners(): void {
    for (const input of this.#inputs) {
      input.addEventListener("change", () => this.#updateInputs());
    }

    // The inputs remain part of the rows while the results are displayed.
    this.#element.querySelector(".pollWidget__options")!.addEventListener("click", (event) => {
      if (this.#state === "results") {
        event.preventDefault();
      }
    });

    this.#element.querySelector(".pollWidget__showResults")?.addEventListener("click", () => {
      this.#setState("results");
    });

    this.#element.querySelector(".pollWidget__showVote")?.addEventListener("click", () => {
      this.#setState("vote");
    });

    this.#voteButton?.addEventListener(
      "click",
      promiseMutex(() => this.#submit()),
    );

    this.#element.querySelector(".pollWidget__showParticipants")?.addEventListener(
      "click",
      promiseMutex(() => {
        return dialogFactory()
          .usingListView()
          .fromPreset(
            this.#element.dataset.question!,
            "wcf\\system\\listView\\user\\PollParticipantListView",
            new Map([["pollID", this.#pollId.toString()]]),
          );
      }),
    );
  }

  /**
   * The widget serves as the container of the interactions, which allow the
   * owner and moderators to edit or delete the poll.
   */
  #initInteractions(): void {
    const toggle = this.#element.querySelector<HTMLElement>(".pollWidget__interactions .dropdownToggle");
    if (toggle === null) {
      return;
    }

    let dropdown = UiDropdownSimple.getDropdownMenu(toggle.dataset.target!);
    dropdown ??= toggle.closest(".dropdown")!.querySelector<HTMLElement>(".dropdownMenu")!;

    dropdown.querySelectorAll<HTMLElement>("[data-interaction]").forEach((button) => {
      button.addEventListener("click", () => {
        // The interactions are set up on the widget itself, the event must not
        // reach the interactions of a surrounding container.
        this.#element.dispatchEvent(
          new CustomEvent("interaction:execute", {
            detail: button.dataset,
          }),
        );
      });
    });

    this.#element.addEventListener("interaction:invalidate", (event: CustomEvent<Record<string, string>>) => {
      event.stopPropagation();

      if (event.detail._reloadPage === "true") {
        window.location.reload();
      } else {
        void refreshWidgets(this.#pollId, this.#element);
      }
    });

    this.#element.addEventListener("interaction:remove", (event) => {
      event.stopPropagation();

      getWidgets(this.#pollId).forEach((element) => {
        destroyInteractions(element);
        element.remove();
      });
    });
  }

  get #state(): State {
    return this.#element.dataset.state as State;
  }

  #setState(state: State, moveFocus = true): void {
    this.#element.dataset.state = state;

    for (const input of this.#inputs) {
      input.inert = state === "results";
    }

    if (!moveFocus) {
      return;
    }

    if (state === "vote") {
      const input = this.#inputs.find((input) => input.checked) ?? this.#inputs.find((input) => !input.disabled);
      input?.focus();
    } else {
      this.#getQuestion().focus();
    }
  }

  #updateInputs(): void {
    if (this.#voteButton === null) {
      return;
    }

    const selectedCount = this.#getSelectedOptionIds().length;
    this.#voteButton.disabled = selectedCount < this.#requiredVotes;

    if (this.#maxVotes > 1) {
      for (const input of this.#inputs) {
        input.disabled = !input.checked && selectedCount >= this.#maxVotes;
      }
    }
  }

  #getSelectedOptionIds(): number[] {
    return this.#inputs.filter((input) => input.checked).map((input) => parseInt(input.value));
  }

  #getQuestion(): HTMLElement {
    return this.#element.querySelector<HTMLElement>(".pollWidget__question")!;
  }

  async #submit(): Promise<void> {
    const optionIds = this.#getSelectedOptionIds();
    if (optionIds.length === 0) {
      return;
    }

    this.#voteButton!.disabled = true;
    this.#element.setAttribute("aria-busy", "true");

    try {
      const { template } = await vote(this.#pollId, optionIds);

      replaceWidget(this.#element, template)?.querySelector<HTMLElement>(".pollWidget__question")!.focus();
    } finally {
      if (this.#element.isConnected) {
        this.#element.removeAttribute("aria-busy");
        this.#updateInputs();
      }
    }
  }
}

/**
 * The same poll can be embedded multiple times on a page.
 */
function getWidgets(pollId: number): HTMLElement[] {
  return Array.from(document.querySelectorAll<HTMLElement>(`.pollWidget[data-poll-id="${pollId}"]`));
}

/**
 * The dropdown menu of the interactions has been moved into the global
 * container and must be removed together with the widget.
 */
function destroyInteractions(element: HTMLElement): void {
  const toggle = element.querySelector<HTMLElement>(".pollWidget__interactions .dropdownToggle");
  if (toggle?.dataset.target) {
    UiDropdownSimple.destroy(toggle.dataset.target);
  }
}

/**
 * Replaces the widget with the rendered template and returns the new widget,
 * which is set up by the selector observer.
 */
function replaceWidget(element: HTMLElement, template: string): HTMLElement | null {
  const fragment = DomUtil.createFragmentFromHtml(template);
  const widget = fragment.querySelector<HTMLElement>(".pollWidget");
  if (widget === null) {
    return null;
  }

  // The scripts initialize the interactions and are executed on insertion.
  const scripts = Array.from(fragment.querySelectorAll("script"));
  destroyInteractions(element);
  element.replaceWith(fragment);
  scripts.forEach((script) => script.remove());

  triggerDomChange();

  return widget;
}

async function refreshWidgets(pollId: number, source: HTMLElement): Promise<void> {
  await Promise.all(
    getWidgets(pollId).map(async (element) => {
      // Every instance requires its own rendering because of the unique ids.
      const { template } = await getWidget(pollId);
      if (!element.isConnected) {
        return;
      }

      const widget = replaceWidget(element, template);
      if (element === source) {
        widget?.querySelector<HTMLElement>(".pollWidget__question")!.focus();
      }
    }),
  );
}

export function setup(): void {
  wheneverFirstSeen(".pollWidget", (element) => {
    new PollWidget(element);
  });
}
