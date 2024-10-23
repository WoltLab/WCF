/**
 * Provides the interface logic to add and edit boxes.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../../../../Ajax", "../../../../Dom/Util", "../../../../Event/Handler"], function (require, exports, tslib_1, Ajax, Util_1, EventHandler) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    Ajax = tslib_1.__importStar(Ajax);
    Util_1 = tslib_1.__importDefault(Util_1);
    EventHandler = tslib_1.__importStar(EventHandler);
    class AcpUiBoxControllerHandler {
        boxConditions;
        boxController;
        boxControllerContainer;
        constructor(initialObjectTypeId) {
            this.boxControllerContainer = document.getElementById("boxControllerContainer");
            this.boxController = document.getElementById("boxControllerID");
            this.boxConditions = document.getElementById("boxConditions");
            this.boxController.addEventListener("change", () => this.updateConditions());
            Util_1.default.show(this.boxControllerContainer);
            if (initialObjectTypeId === undefined) {
                this.updateConditions();
            }
        }
        /**
         * Sets up ajax request object.
         */
        _ajaxSetup() {
            return {
                data: {
                    actionName: "getBoxConditionsTemplate",
                    className: "wcf\\data\\box\\BoxAction",
                },
            };
        }
        /**
         * Handles successful AJAX requests.
         */
        _ajaxSuccess(data) {
            Util_1.default.setInnerHtml(this.boxConditions, data.returnValues.template);
        }
        /**
         * Updates the displayed box conditions based on the selected dynamic box controller.
         */
        updateConditions() {
            EventHandler.fire("com.woltlab.wcf.boxControllerHandler", "updateConditions");
            Ajax.api(this, {
                parameters: {
                    objectTypeID: ~~this.boxController.value,
                },
            });
        }
    }
    let acpUiBoxControllerHandler;
    function init(initialObjectTypeId) {
        if (!acpUiBoxControllerHandler) {
            acpUiBoxControllerHandler = new AcpUiBoxControllerHandler(initialObjectTypeId);
        }
    }
});
