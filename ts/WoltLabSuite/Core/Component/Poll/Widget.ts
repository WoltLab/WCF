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
import DomUtil from "WoltLabSuite/Core/Dom/Util";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";

type State = "vote" | "results";

class PollWidget {
  readonly #element: HTMLElement;
  readonly #inputs: HTMLInputElement[];
  readonly #maxVotes: number;
  readonly #pollId: number;
  readonly #voteButton: HTMLButtonElement | null;

  constructor(element: HTMLElement) {
    this.#element = element;
    this.#inputs = Array.from(element.querySelectorAll<HTMLInputElement>(".pollWidget__input"));
    this.#maxVotes = parseInt(element.dataset.maxVotes!);
    this.#pollId = parseInt(element.dataset.pollId!);
    this.#voteButton = element.querySelector<HTMLButtonElement>(".pollWidget__vote");

    this.#initEventListeners();
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
    this.#voteButton.disabled = selectedCount === 0;

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
      await vote(this.#pollId, optionIds);
      const { template } = await getWidget(this.#pollId);

      const container = document.createElement("div");
      DomUtil.setInnerHtml(container, template);

      const widget = container.querySelector<HTMLElement>(".pollWidget");
      if (widget === null) {
        return;
      }

      // The new widget is set up by the selector observer.
      this.#element.replaceWith(widget);
      widget.querySelector<HTMLElement>(".pollWidget__question")!.focus();
    } finally {
      if (this.#element.isConnected) {
        this.#element.removeAttribute("aria-busy");
        this.#updateInputs();
      }
    }
  }
}

export function setup(): void {
  wheneverFirstSeen(".pollWidget", (element) => {
    new PollWidget(element);
  });
}
