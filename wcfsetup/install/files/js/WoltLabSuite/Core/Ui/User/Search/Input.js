/**
 * Provides suggestions for users, optionally supporting groups.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/User/Search/Input
 * @see         module:WoltLabSuite/Core/Ui/Search/Input
 */
define(['Core', 'WoltLabSuite/Core/Ui/Search/Input'], function (Core, UiSearchInput) {
    "use strict";
    /**
     * @param       {Element}       element         input element
     * @param       {Object=}       options         search options and settings
     * @constructor
     */
    function UiUserSearchInput(element, options) { this.init(element, options); }
    Core.inherit(UiUserSearchInput, UiSearchInput, {
        init: function (element, options) {
            var includeUserGroups = (Core.isPlainObject(options) && options.includeUserGroups === true);
            options = Core.extend({
                ajax: {
                    className: 'wcf\\data\\user\\UserAction',
                    parameters: {
                        data: {
                            includeUserGroups: (includeUserGroups ? 1 : 0)
                        }
                    }
                }
            }, options);
            UiUserSearchInput._super.prototype.init.call(this, element, options);
        },
        _createListItem: function (item) {
            var listItem = UiUserSearchInput._super.prototype._createListItem.call(this, item);
            elData(listItem, 'type', item.type);
            var box = elCreate('div');
            box.className = 'box16';
            box.innerHTML = (item.type === 'group') ? '<span class="icon icon16 fa-users"></span>' : item.icon;
            box.appendChild(listItem.children[0]);
            listItem.appendChild(box);
            return listItem;
        }
    });
    return UiUserSearchInput;
});
