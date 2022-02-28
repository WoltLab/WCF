/**
 * Implementation for poll result views.
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
    exports.Results = void 0;
    Ajax = (0, tslib_1.__importStar)(Ajax);
    class Results {
        constructor(manager) {
            this.pollManager = manager;
            const button = this.pollManager.getElement().querySelector(".showResultsButton");
            if (!button) {
                throw new Error(`Could not find button with selector ".showResultsButton" for poll "${this.pollManager.pollId}"`);
            }
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
            const request = Ajax.dboAction("getResultTemplate", "wcf\\data\\poll\\PollAction");
            request.objectIds([this.pollManager.pollId]);
            const results = (await request.dispatch());
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
