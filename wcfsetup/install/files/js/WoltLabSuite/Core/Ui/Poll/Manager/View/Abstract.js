/**
 * Abstract implementation for poll views.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Poll/Manager/View/Abstract
 * @since   5.5
 */
define(["require", "exports", "tslib", "../../../../Core", "../../../../Ajax/Request"], function (require, exports, tslib_1, Core, Request_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Abstract = void 0;
    Core = (0, tslib_1.__importStar)(Core);
    Request_1 = (0, tslib_1.__importDefault)(Request_1);
    class Abstract {
        constructor(manager) {
            this.pollManager = manager;
            this.initButton();
        }
        apiCall(actionName, data) {
            const request = new Request_1.default({
                url: `index.php?poll/&t=${Core.getXsrfToken()}`,
                data: Core.extend({
                    actionName,
                    pollID: this.pollManager.pollID,
                }, data ? data : {}),
                success: (data) => {
                    this.button.disabled = false;
                    this.success(data);
                },
            });
            request.sendRequest();
        }
        initButton() {
            const button = this.pollManager.getPollContainer().querySelector(this.getButtonSelector()) || null;
            if (!button) {
                throw new Error(`Could not find button with selector "${this.getButtonSelector()}" for poll "${this.pollManager.pollID}"`);
            }
            this.button = button;
            this.button.addEventListener("click", (event) => {
                if (event) {
                    event.preventDefault();
                }
                this.apiCall(this.getActionName(), this.getData());
                this.button.disabled = true;
            });
        }
        getData() {
            return undefined;
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
