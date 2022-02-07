/**
 * Implementation for poll result views.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Poll/Manager/View/Results
 * @since   5.5
 */
define(["require", "exports", "tslib", "../Manager", "./Abstract"], function (require, exports, tslib_1, Manager_1, Abstract_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Results = void 0;
    Abstract_1 = (0, tslib_1.__importDefault)(Abstract_1);
    class Results extends Abstract_1.default {
        checkVisibility(view) {
            if (view !== Manager_1.PollViews.results) {
                this.showButton();
            }
            else {
                this.hideButton();
            }
        }
        getButtonSelector() {
            return ".showResultsButton";
        }
        getActionName() {
            return "getResult";
        }
        success(data) {
            this.pollManager.changeView(Manager_1.PollViews.results, data.template);
        }
    }
    exports.Results = Results;
    exports.default = Results;
});
