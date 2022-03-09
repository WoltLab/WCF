/**
 * Wrapper class to provide color picker support. Constructing a new object does not
 * guarantee the picker to be ready at the time of call.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Color/Picker
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "../../Core"], function (require, exports, tslib_1, Core) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    let _marshal = (element, options) => {
        if (typeof window.WCF === "object" && typeof window.WCF.ColorPicker === "function") {
            _marshal = (element, options) => {
                const picker = new window.WCF.ColorPicker(element);
                if (typeof options.callbackSubmit === "function") {
                    picker.setCallbackSubmit(options.callbackSubmit);
                }
                return picker;
            };
            return _marshal(element, options);
        }
        else {
            if (_queue.length === 0) {
                window.__wcf_bc_colorPickerInit = () => {
                    _queue.forEach((data) => {
                        _marshal(data[0], data[1]);
                    });
                    window.__wcf_bc_colorPickerInit = undefined;
                    _queue = [];
                };
            }
            _queue.push([element, options]);
        }
    };
    let _queue = [];
    class UiColorPicker {
        /**
         * Initializes a new color picker instance. This is actually just a wrapper that does
         * not guarantee the picker to be ready at the time of call.
         */
        constructor(element, options) {
            if (!(element instanceof Element)) {
                throw new TypeError("Expected a valid DOM element, use `UiColorPicker.fromSelector()` if you want to use a CSS selector.");
            }
            options = Core.extend({
                callbackSubmit: null,
            }, options || {});
            _marshal(element, options);
        }
        /**
         * Initializes a color picker for all input elements matching the given selector.
         */
        static fromSelector(selector) {
            document.querySelectorAll(selector).forEach((element) => {
                new UiColorPicker(element);
            });
        }
    }
    Core.enableLegacyInheritance(UiColorPicker);
    return UiColorPicker;
});
