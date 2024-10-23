/**
 * Reacts to objects being deleted.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "./Handler"], function (require, exports, tslib_1, Handler_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    Handler_1 = tslib_1.__importDefault(Handler_1);
    function deleteObject(data) {
        const actionElement = data.objectElement.querySelector('.jsObjectAction[data-object-action="delete"]');
        if (!actionElement || actionElement.dataset.objectActionHandler) {
            return;
        }
        const childContainer = data.objectElement.querySelector(".jsObjectActionObjectChildren");
        if (childContainer) {
            Array.from(childContainer.children).forEach((child) => {
                data.objectElement.insertAdjacentElement("beforebegin", child);
            });
        }
        data.objectElement.remove();
    }
    function setup() {
        new Handler_1.default("delete", ["delete"], deleteObject);
    }
});
