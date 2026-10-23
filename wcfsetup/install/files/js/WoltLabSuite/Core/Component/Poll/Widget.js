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
define(["require", "exports", "tslib", "WoltLabSuite/Core/Api/Polls/Vote", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Dom/Util", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Helper/Selector"], function (require, exports, tslib_1, Vote_1, Dialog_1, Util_1, PromiseMutex_1, Selector_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    Util_1 = tslib_1.__importDefault(Util_1);
    class PollWidget {
        #element;
        #inputs;
        #maxVotes;
        #pollId;
        #voteButton;
        constructor(element) {
            this.#element = element;
            this.#inputs = Array.from(element.querySelectorAll(".pollWidget__input"));
            this.#maxVotes = parseInt(element.dataset.maxVotes);
            this.#pollId = parseInt(element.dataset.pollId);
            this.#voteButton = element.querySelector(".pollWidget__vote");
            this.#initEventListeners();
            this.#setState(element.dataset.state, false);
            this.#updateInputs();
        }
        #initEventListeners() {
            for (const input of this.#inputs) {
                input.addEventListener("change", () => this.#updateInputs());
            }
            // The inputs remain part of the rows while the results are displayed.
            this.#element.querySelector(".pollWidget__options").addEventListener("click", (event) => {
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
            this.#voteButton?.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => this.#submit()));
            this.#element.querySelector(".pollWidget__showParticipants")?.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => {
                return (0, Dialog_1.dialogFactory)()
                    .usingListView()
                    .fromPreset(this.#element.dataset.question, "wcf\\system\\listView\\user\\PollParticipantListView", new Map([["pollID", this.#pollId.toString()]]));
            }));
        }
        get #state() {
            return this.#element.dataset.state;
        }
        #setState(state, moveFocus = true) {
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
            }
            else {
                this.#getQuestion().focus();
            }
        }
        #updateInputs() {
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
        #getSelectedOptionIds() {
            return this.#inputs.filter((input) => input.checked).map((input) => parseInt(input.value));
        }
        #getQuestion() {
            return this.#element.querySelector(".pollWidget__question");
        }
        async #submit() {
            const optionIds = this.#getSelectedOptionIds();
            if (optionIds.length === 0) {
                return;
            }
            this.#voteButton.disabled = true;
            this.#element.setAttribute("aria-busy", "true");
            try {
                const { template } = await (0, Vote_1.vote)(this.#pollId, optionIds);
                const container = document.createElement("div");
                Util_1.default.setInnerHtml(container, template);
                const widget = container.querySelector(".pollWidget");
                if (widget === null) {
                    return;
                }
                // The new widget is set up by the selector observer.
                this.#element.replaceWith(widget);
                widget.querySelector(".pollWidget__question").focus();
            }
            finally {
                if (this.#element.isConnected) {
                    this.#element.removeAttribute("aria-busy");
                    this.#updateInputs();
                }
            }
        }
    }
    function setup() {
        (0, Selector_1.wheneverFirstSeen)(".pollWidget", (element) => {
            new PollWidget(element);
        });
    }
});
