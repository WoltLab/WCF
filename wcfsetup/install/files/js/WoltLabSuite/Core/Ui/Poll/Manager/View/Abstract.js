/**
 * Abstract implementation for poll views.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Poll/Manager/View/Abstract
 * @since   5.5
 */
define(["require", "exports", "tslib", "../../../../Ajax"], function (require, exports, tslib_1, Ajax) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Abstract = void 0;
    Ajax = (0, tslib_1.__importStar)(Ajax);
    class Abstract {
        constructor(manager) {
            this.templateCache = undefined;
            this.pollManager = manager;
            this.initButton();
        }
        async apiCall(actionName) {
            const request = Ajax.dboAction(actionName, "wcf\\data\\poll\\PollAction");
            request.objectIds([this.pollManager.pollID]);
            const results = (await request.dispatch());
            this.templateCache = results.template;
            await this.loadView();
        }
        initButton() {
            const button = this.pollManager.getElement().querySelector(this.getButtonSelector());
            if (!button) {
                throw new Error(`Could not find button with selector "${this.getButtonSelector()}" for poll "${this.pollManager.pollID}"`);
            }
            this.button = button;
            this.button.addEventListener("click", async (event) => {
                if (event) {
                    event.preventDefault();
                }
                this.button.disabled = true;
                await this.loadView();
            });
        }
        async loadView() {
            if (this.templateCache === undefined) {
                await this.apiCall(this.getActionName());
            }
            else {
                this.pollManager.changeView(this.getViewName(), this.templateCache);
            }
            this.button.disabled = false;
        }
        changeView(view, html) {
            this.pollManager.changeView(view, html);
        }
        hideButton() {
            this.button.hidden = true;
        }
        showButton() {
            this.button.hidden = false;
        }
    }
    exports.Abstract = Abstract;
    exports.default = Abstract;
});
