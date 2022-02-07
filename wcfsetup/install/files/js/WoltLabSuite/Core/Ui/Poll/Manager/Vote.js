/**
 * Handles the poll voting.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Poll/Manager/Vote
 * @since   5.5
 */
define(["require", "exports", "tslib", "../../../Ajax/Request", "./Manager", "../../../Core"], function (require, exports, tslib_1, Request_1, Manager_1, Core) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Vote = void 0;
    Request_1 = (0, tslib_1.__importDefault)(Request_1);
    Core = (0, tslib_1.__importStar)(Core);
    class Vote {
        constructor(manager) {
            this.pollManager = manager;
            this.initButton();
            this.initSelects();
        }
        initButton() {
            const button = this.pollManager.getPollContainer().querySelector(".votePollButton") || null;
            if (!button) {
                throw new Error(`Could not find vote button for poll "${this.pollManager.pollID}".`);
            }
            this.button = button;
            this.button.addEventListener("click", () => this.submit());
        }
        initSelects() {
            const container = this.pollManager.getPollContainer().querySelector(".pollVoteContainer");
            if (container) {
                this.inputs = container.querySelectorAll("input");
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
            const numbers = new Array();
            this.inputs.forEach((input) => {
                if (input.checked) {
                    numbers.push(parseInt(input.value, 10));
                }
            });
            return numbers;
        }
        submit() {
            this.button.disabled = true;
            this.apiCall();
        }
        apiCall() {
            const optionIDs = this.getSelectedOptions();
            const request = new Request_1.default({
                url: `index.php?poll/&t=${Core.getXsrfToken()}`,
                data: Core.extend({
                    actionName: "vote",
                    pollID: this.pollManager.pollID,
                    optionIDs,
                }),
                success: (data) => {
                    this.button.disabled = false;
                    this.pollManager.changeView(Manager_1.PollViews.results, data.template);
                },
            });
            request.sendRequest();
        }
        checkVisibility(view) {
            if (view !== Manager_1.PollViews.vote) {
                this.button.hidden = true;
            }
            else {
                this.button.hidden = false;
            }
        }
    }
    exports.Vote = Vote;
    exports.default = Vote;
});
