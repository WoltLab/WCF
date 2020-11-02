/**
 * Sortable lists with optimized handling per device sizes.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Sortable/List
 */
define(["require", "exports", "tslib", "../../Core", "../Screen"], function (require, exports, tslib_1, Core, UiScreen) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    UiScreen = tslib_1.__importStar(UiScreen);
    class UiSortableList {
        /**
         * Initializes the sortable list controller.
         */
        constructor(opts) {
            this._options = Core.extend({
                containerId: "",
                className: "",
                offset: 0,
                options: {},
                isSimpleSorting: false,
                additionalParameters: {},
            }, opts);
            UiScreen.on("screen-sm-md", {
                match: () => this._enable(true),
                unmatch: () => this._disable(),
                setup: () => this._enable(true),
            });
            UiScreen.on("screen-lg", {
                match: () => this._enable(false),
                unmatch: () => this._disable(),
                setup: () => this._enable(false),
            });
        }
        /**
         * Enables sorting with an optional sort handle.
         */
        _enable(hasHandle) {
            const options = this._options.options;
            if (hasHandle) {
                options.handle = ".sortableNodeHandle";
            }
            new window.WCF.Sortable.List(this._options.containerId, this._options.className, this._options.offset, options, this._options.isSimpleSorting, this._options.additionalParameters);
        }
        /**
         * Disables sorting for registered containers.
         */
        _disable() {
            window
                .jQuery(`#${this._options.containerId} .sortableList`)[this._options.isSimpleSorting ? "sortable" : "nestedSortable"]("destroy");
        }
    }
    Core.enableLegacyInheritance(UiSortableList);
    return UiSortableList;
});
