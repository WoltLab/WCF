/**
 * Handles a user ban.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action/Handler
 * @since       5.5
 */
define(["require", "exports", "tslib", "../../../../../Ajax", "./Ban/Dialog"], function (require, exports, tslib_1, Ajax, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.BanHandler = void 0;
    Ajax = (0, tslib_1.__importStar)(Ajax);
    Dialog_1 = (0, tslib_1.__importDefault)(Dialog_1);
    class BanHandler {
        constructor(userIDs) {
            this.userIDs = userIDs;
        }
        ban(callback) {
            Dialog_1.default.open(this.userIDs, callback);
        }
        unban(callback) {
            Ajax.api({
                _ajaxSetup: () => {
                    return {
                        data: {
                            actionName: "unban",
                            className: "wcf\\data\\user\\UserAction",
                            objectIDs: this.userIDs,
                        },
                    };
                },
                _ajaxSuccess: () => callback(),
            });
        }
    }
    exports.BanHandler = BanHandler;
    exports.default = BanHandler;
});
