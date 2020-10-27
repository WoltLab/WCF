/**
 * Provides suggestions for users, optionally supporting groups.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/User/Search/Input
 * @see  module:WoltLabSuite/Core/Ui/Search/Input
 */
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    Object.defineProperty(o, k2, { enumerable: true, get: function() { return m[k]; } });
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
define(["require", "exports", "../../../Core", "../../Search/Input"], function (require, exports, Core, Input_1) {
    "use strict";
    Core = __importStar(Core);
    Input_1 = __importDefault(Input_1);
    class UiUserSearchInput extends Input_1.default {
        constructor(element, options) {
            const includeUserGroups = (Core.isPlainObject(options) && options.includeUserGroups === true);
            options = Core.extend({
                ajax: {
                    className: 'wcf\\data\\user\\UserAction',
                    parameters: {
                        data: {
                            includeUserGroups: (includeUserGroups ? 1 : 0),
                        },
                    },
                },
            }, options);
            super(element, options);
        }
        createListItem(item) {
            const listItem = super.createListItem(item);
            listItem.dataset.type = item.type;
            const box = document.createElement('div');
            box.className = 'box16';
            box.innerHTML = (item.type === 'group') ? '<span class="icon icon16 fa-users"></span>' : item.icon;
            box.appendChild(listItem.children[0]);
            listItem.appendChild(box);
            return listItem;
        }
    }
    return UiUserSearchInput;
});
