/**
 * Implementation for poll result views.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.5
 * @deprecated 6.3 Use standalone polls that are embedded through the `[poll]` BBCode instead.
 */
define(["require", "exports", "WoltLabSuite/Core/Api/Polls/GetResults", "../Poll"], function (require, exports, GetResults_1, Poll_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Results = void 0;
    class Results {
        pollManager;
        button;
        constructor(manager, button) {
            this.pollManager = manager;
            this.button = button;
            this.button.addEventListener("click", async (event) => {
                if (event) {
                    event.preventDefault();
                }
                this.button.disabled = true;
                if (this.pollManager.hasView(Poll_1.PollViews.results)) {
                    this.pollManager.displayView(Poll_1.PollViews.results);
                }
                else {
                    await this.loadView();
                }
                this.button.disabled = false;
            });
        }
        async loadView() {
            const results = await (0, GetResults_1.getResults)(this.pollManager.pollId);
            this.pollManager.addView(Poll_1.PollViews.results, results.template);
            this.pollManager.displayView(Poll_1.PollViews.results);
        }
        checkVisibility(view) {
            if (view === Poll_1.PollViews.results) {
                this.button.hidden = true;
            }
            else {
                this.button.hidden = false;
            }
        }
    }
    exports.Results = Results;
    exports.default = Results;
});
