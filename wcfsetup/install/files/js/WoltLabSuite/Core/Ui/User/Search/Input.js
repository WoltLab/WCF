/**
 * Provides suggestions for users, optionally supporting groups.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/User/Search/Input
 * @see  module:WoltLabSuite/Core/Ui/Search/Input
 */
define(["require", "exports", "tslib", "../../../Core", "../../Search/Input"], function (require, exports, tslib_1, Core, Input_1) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    Input_1 = tslib_1.__importDefault(Input_1);
    class UiUserSearchInput extends Input_1.default {
        constructor(element, options) {
            const includeUserGroups = Core.isPlainObject(options) && options.includeUserGroups === true;
            options = Core.extend({
                ajax: {
                    className: "wcf\\data\\user\\UserAction",
                    parameters: {
                        data: {
                            includeUserGroups: includeUserGroups ? 1 : 0,
                        },
                    },
                },
            }, options);
            super(element, options);
        }
        createListItem(item) {
            const listItem = super.createListItem(item);
            listItem.dataset.type = item.type;
            const box = document.createElement("div");
            box.className = "box16";
            box.innerHTML = item.type === "group" ? `<span class="icon icon16 fa-users"></span>` : item.icon;
            box.appendChild(listItem.children[0]);
            listItem.appendChild(box);
            return listItem;
        }
    }
    Core.enableLegacyInheritance(UiUserSearchInput);
    return UiUserSearchInput;
});
