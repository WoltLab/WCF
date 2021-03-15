/**
 * Reacts to objects being toggled.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Object/Action/Toggle
 */
define(["require", "exports", "tslib", "../../../Language", "./Handler"], function (require, exports, tslib_1, Language, Handler_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    Language = tslib_1.__importStar(Language);
    Handler_1 = tslib_1.__importDefault(Handler_1);
    function toggleObject(data, objectElement) {
        const toggleButton = objectElement.querySelector('.jsObjectAction[data-object-action="toggle"]');
        if (toggleButton.classList.contains("fa-square-o")) {
            toggleButton.classList.replace("fa-square-o", "fa-check-square-o");
            const newTitle = toggleButton.dataset.disableTitle || Language.get("wcf.global.button.disable");
            toggleButton.title = newTitle;
        }
        else {
            toggleButton.classList.replace("fa-check-square-o", "fa-square-o");
            const newTitle = toggleButton.dataset.enableTitle || Language.get("wcf.global.button.enable");
            toggleButton.title = newTitle;
        }
    }
    function setup() {
        new Handler_1.default("toggle", ["enable", "disable"], toggleObject);
    }
    exports.setup = setup;
});
