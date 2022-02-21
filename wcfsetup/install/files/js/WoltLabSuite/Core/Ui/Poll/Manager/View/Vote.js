/**
 * Implementation for poll vote views.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Poll/Manager/View/Results
 * @since   5.5
 */
define(["require", "exports", "tslib", "../../../../Ajax", "../Poll"], function (require, exports, tslib_1, Ajax, Poll_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Vote = void 0;
    Ajax = (0, tslib_1.__importStar)(Ajax);
    class Vote {
        constructor(manager) {
            this.pollManager = manager;
            const button = this.pollManager.getElement().querySelector(".showVoteFormButton");
            if (!button) {
                throw new Error(`Could not find button with selector ".showVoteFormButton" for poll "${this.pollManager.pollID}"`);
            }
            this.button = button;
            this.button.addEventListener("click", async (event) => {
                if (event) {
                    event.preventDefault();
                }
                this.button.disabled = true;
                if (this.pollManager.hasView(Poll_1.PollViews.vote)) {
                    this.pollManager.displayView(Poll_1.PollViews.vote);
                }
                else {
                    await this.loadView();
                }
                this.button.disabled = false;
            });
        }
        async loadView() {
            const request = Ajax.dboAction("getVoteTemplate", "wcf\\data\\poll\\PollAction");
            request.objectIds([this.pollManager.pollID]);
            const results = (await request.dispatch());
            this.pollManager.addView(Poll_1.PollViews.vote, results.template);
            this.pollManager.displayView(Poll_1.PollViews.vote);
        }
        checkVisibility(view) {
            if (view === Poll_1.PollViews.vote || !this.pollManager.canVote) {
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
