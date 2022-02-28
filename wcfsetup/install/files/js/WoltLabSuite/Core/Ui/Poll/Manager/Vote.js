/**
 * Handles the poll voting.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Poll/Manager/Vote
 * @since   5.5
 */
define(["require", "exports", "tslib", "./Poll", "../../../Ajax"], function (require, exports, tslib_1, Poll_1, Ajax) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Vote = void 0;
    Ajax = (0, tslib_1.__importStar)(Ajax);
    class Vote {
        constructor(manager) {
            this.pollManager = manager;
            const button = this.pollManager.getElement().querySelector(".votePollButton");
            if (!button) {
                throw new Error(`Could not find vote button for poll "${this.pollManager.pollId}".`);
            }
            this.button = button;
            this.button.addEventListener("click", () => this.submit());
            this.initSelects();
        }
        initSelects() {
            if (this.pollManager.hasView(Poll_1.PollViews.vote)) {
                const container = this.pollManager.getView(Poll_1.PollViews.vote);
                this.inputs = Array.from(container.querySelectorAll("input"));
                this.inputs.forEach((input) => {
                    input.addEventListener("change", () => this.checkInputs());
                });
                this.checkInputs();
            }
        }
        checkInputs() {
            let selectedInputCount = 0;
            this.inputs.forEach((input) => {
                if (input.checked) {
                    selectedInputCount++;
                }
                if (this.pollManager.maxVotes > 1) {
                    input.disabled = false;
                }
            });
            if (selectedInputCount === 0) {
                this.button.disabled = true;
            }
            else {
                if (selectedInputCount >= this.pollManager.maxVotes && this.pollManager.maxVotes > 1) {
                    this.inputs.forEach((input) => {
                        if (!input.checked) {
                            input.disabled = true;
                        }
                    });
                }
                this.button.disabled = false;
            }
        }
        getSelectedOptions() {
            return this.inputs.filter((input) => input.checked).map((input) => parseInt(input.value, 10));
        }
        async submit() {
            this.button.disabled = true;
            const optionIDs = this.getSelectedOptions();
            const request = Ajax.dboAction("vote", "wcf\\data\\poll\\PollAction");
            request.objectIds([this.pollManager.pollId]);
            request.payload({
                optionIDs,
            });
            const results = (await request.dispatch());
            this.pollManager.canVote = !!results.changeableVote;
            this.pollManager.canViewResults = true;
            this.pollManager.addView(Poll_1.PollViews.results, results.template);
            this.pollManager.displayView(Poll_1.PollViews.results);
            this.pollManager.changeTotalVotes(results.totalVotes, results.totalVotesTooltip);
            this.button.disabled = false;
        }
        checkVisibility(view) {
            this.button.hidden = view !== Poll_1.PollViews.vote;
        }
    }
    exports.Vote = Vote;
    exports.default = Vote;
});
