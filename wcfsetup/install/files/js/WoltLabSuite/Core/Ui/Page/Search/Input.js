/**
 * Suggestions for page object ids with external response data processing.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Page/Search/Input
 * @extends     module:WoltLabSuite/Core/Ui/Search/Input
 */
define(['Core', 'WoltLabSuite/Core/Ui/Search/Input'], function (Core, UiSearchInput) {
    "use strict";
    /**
     * @param       {Element}       element         input element
     * @param       {Object=}       options         search options and settings
     * @constructor
     */
    function UiPageSearchInput(element, options) { this.init(element, options); }
    Core.inherit(UiPageSearchInput, UiSearchInput, {
        init: function (element, options) {
            options = Core.extend({
                ajax: {
                    className: 'wcf\\data\\page\\PageAction'
                },
                callbackSuccess: null
            }, options);
            if (typeof options.callbackSuccess !== 'function') {
                throw new Error("Expected a valid callback function for 'callbackSuccess'.");
            }
            UiPageSearchInput._super.prototype.init.call(this, element, options);
            this._pageId = 0;
        },
        /**
         * Sets the target page id.
         *
         * @param       {int}   pageId  target page id
         */
        setPageId: function (pageId) {
            this._pageId = pageId;
        },
        _getParameters: function (value) {
            var data = UiPageSearchInput._super.prototype._getParameters.call(this, value);
            data.objectIDs = [this._pageId];
            return data;
        },
        _ajaxSuccess: function (data) {
            this._options.callbackSuccess(data);
        }
    });
    return UiPageSearchInput;
});
