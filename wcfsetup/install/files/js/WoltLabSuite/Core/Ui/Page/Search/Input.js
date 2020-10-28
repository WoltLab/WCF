/**
 * Suggestions for page object ids with external response data processing.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Page/Search/Input
 */
define(["require", "exports", "tslib", "../../../Core", "../../Search/Input"], function (require, exports, tslib_1, Core, Input_1) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    Input_1 = tslib_1.__importDefault(Input_1);
    class UiPageSearchInput extends Input_1.default {
        constructor(element, options) {
            if (typeof options.callbackSuccess !== 'function') {
                throw new Error("Expected a valid callback function for 'callbackSuccess'.");
            }
            options = Core.extend({
                ajax: {
                    className: 'wcf\\data\\page\\PageAction',
                },
            }, options);
            super(element, options);
            this.callbackSuccess = options.callbackSuccess;
            this.pageId = 0;
        }
        /**
         * Sets the target page id.
         */
        setPageId(pageId) {
            this.pageId = pageId;
        }
        getParameters(value) {
            const data = super.getParameters(value);
            data.objectIDs = [this.pageId];
            return data;
        }
        _ajaxSuccess(data) {
            this.callbackSuccess(data);
        }
    }
    return UiPageSearchInput;
});
