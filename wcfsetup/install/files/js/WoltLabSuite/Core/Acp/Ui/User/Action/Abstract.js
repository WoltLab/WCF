/**
 * An abstract action, to handle user actions.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action
 * @since       5.5
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.AbstractUserAction = void 0;
    class AbstractUserAction {
        constructor(button, userId, userDataElement) {
            this.button = button;
            this.userId = userId;
            this.userDataElement = userDataElement;
        }
    }
    exports.AbstractUserAction = AbstractUserAction;
    exports.default = AbstractUserAction;
});
