/**
 * Generic interface for drag and Drop file uploads.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/DragAndDrop
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../Core", "../Event/Handler", "./Redactor/DragAndDrop"], function (require, exports, tslib_1, Core, EventHandler, DragAndDrop_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.register = void 0;
    Core = tslib_1.__importStar(Core);
    EventHandler = tslib_1.__importStar(EventHandler);
    function register(options) {
        const uuid = Core.getUuid();
        options = Core.extend({
            element: null,
            elementId: "",
            onDrop: function (_data) {
                /* data: { file: File } */
            },
            onGlobalDrop: function (_data) {
                /* data: { cancelDrop: boolean, event: DragEvent } */
            },
        });
        EventHandler.add("com.woltlab.wcf.redactor2", `dragAndDrop_${options.elementId}`, options.onDrop);
        EventHandler.add("com.woltlab.wcf.redactor2", `dragAndDrop_globalDrop_${options.elementId}`, options.onGlobalDrop);
        DragAndDrop_1.init({
            uuid: uuid,
            $editor: [options.element],
            $element: [{ id: options.elementId }],
        });
    }
    exports.register = register;
});
