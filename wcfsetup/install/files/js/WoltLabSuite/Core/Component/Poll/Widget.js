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
define(["require", "exports", "tslib", "WoltLabSuite/Core/Api/Polls/GetWidget", "WoltLabSuite/Core/Api/Polls/Vote", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Dom/Change/Listener", "WoltLabSuite/Core/Dom/Util", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Helper/Selector", "WoltLabSuite/Core/Ui/Dropdown/Simple"], function (require, exports, tslib_1, GetWidget_1, Vote_1, Dialog_1, Listener_1, Util_1, PromiseMutex_1, Selector_1, Simple_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    Util_1 = tslib_1.__importDefault(Util_1);
    Simple_1 = tslib_1.__importDefault(Simple_1);
    class PollWidget {
        #element;
        #inputs;
        #maxVotes;
        #requiredVotes;
        #pollId;
        #voteButton;
        constructor(element) {
            this.#element = element;
            this.#inputs = Array.from(element.querySelectorAll(".pollWidget__input"));
            this.#maxVotes = parseInt(element.dataset.maxVotes);
            this.#requiredVotes = parseInt(element.dataset.requiredVotes);
            this.#pollId = parseInt(element.dataset.pollId);
            this.#voteButton = element.querySelector(".pollWidget__vote");
            this.#initEventListeners();
            this.#initInteractions();
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
        /**
         * The widget serves as the container of the interactions, which allow the
         * owner and moderators to edit or delete the poll.
         */
        #initInteractions() {
            const toggle = this.#element.querySelector(".pollWidget__interactions .dropdownToggle");
            if (toggle === null) {
                return;
            }
            let dropdown = Simple_1.default.getDropdownMenu(toggle.dataset.target);
            dropdown ??= toggle.closest(".dropdown").querySelector(".dropdownMenu");
            dropdown.querySelectorAll("[data-interaction]").forEach((button) => {
                button.addEventListener("click", () => {
                    // The interactions are set up on the widget itself, the event must not
                    // reach the interactions of a surrounding container.
                    this.#element.dispatchEvent(new CustomEvent("interaction:execute", {
                        detail: button.dataset,
                    }));
                });
            });
            this.#element.addEventListener("interaction:invalidate", (event) => {
                event.stopPropagation();
                if (event.detail._reloadPage === "true") {
                    window.location.reload();
                }
                else {
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
            this.#voteButton.disabled = selectedCount < this.#requiredVotes;
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
                replaceWidget(this.#element, template)?.querySelector(".pollWidget__question").focus();
            }
            finally {
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
    function getWidgets(pollId) {
        return Array.from(document.querySelectorAll(`.pollWidget[data-poll-id="${pollId}"]`));
    }
    /**
     * The dropdown menu of the interactions has been moved into the global
     * container and must be removed together with the widget.
     */
    function destroyInteractions(element) {
        const toggle = element.querySelector(".pollWidget__interactions .dropdownToggle");
        if (toggle?.dataset.target) {
            Simple_1.default.destroy(toggle.dataset.target);
        }
    }
    /**
     * Replaces the widget with the rendered template and returns the new widget,
     * which is set up by the selector observer.
     */
    function replaceWidget(element, template) {
        const fragment = Util_1.default.createFragmentFromHtml(template);
        const widget = fragment.querySelector(".pollWidget");
        if (widget === null) {
            return null;
        }
        // The scripts initialize the interactions and are executed on insertion.
        const scripts = Array.from(fragment.querySelectorAll("script"));
        destroyInteractions(element);
        element.replaceWith(fragment);
        scripts.forEach((script) => script.remove());
        (0, Listener_1.trigger)();
        return widget;
    }
    async function refreshWidgets(pollId, source) {
        await Promise.all(getWidgets(pollId).map(async (element) => {
            // Every instance requires its own rendering because of the unique ids.
            const { template } = await (0, GetWidget_1.getWidget)(pollId);
            if (!element.isConnected) {
                return;
            }
            const widget = replaceWidget(element, template);
            if (element === source) {
                widget?.querySelector(".pollWidget__question").focus();
            }
        }));
    }
    function setup() {
        (0, Selector_1.wheneverFirstSeen)(".pollWidget", (element) => {
            new PollWidget(element);
        });
    }
});
