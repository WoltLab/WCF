/**
 * Implementation for poll vote views.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.5
 * @deprecated 6.3 Use standalone polls that are embedded through the `[poll]` BBCode instead.
 */
define(["require", "exports", "WoltLabSuite/Core/Api/Polls/GetVoteForm", "../Poll"], function (require, exports, GetVoteForm_1, Poll_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Vote = void 0;
    class Vote {
        pollManager;
        button;
        constructor(manager) {
            this.pollManager = manager;
            const button = this.pollManager.getElement().querySelector(".showVoteFormButton");
            if (!button) {
                throw new Error(`Could not find button with selector ".showVoteFormButton" for poll "${this.pollManager.pollId}"`);
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
            const results = await (0, GetVoteForm_1.getVoteForm)(this.pollManager.pollId);
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
