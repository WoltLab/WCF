/**
 * Dynamically transforms menu-like structures to handle items exceeding the available width
 * by moving them into a separate dropdown.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/FlexibleMenu
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
define(["require", "exports", "../Dom/Change/Listener", "../Dom/Util", "../Dom/Traverse", "./Dropdown/Simple"], function (require, exports, Listener_1, Util_1, DomTraverse, Simple_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.rebuild = exports.rebuildAll = exports.registerTabMenus = exports.register = exports.setup = void 0;
    Listener_1 = __importDefault(Listener_1);
    Util_1 = __importDefault(Util_1);
    DomTraverse = __importStar(DomTraverse);
    Simple_1 = __importDefault(Simple_1);
    const _containers = new Map();
    const _dropdowns = new Map();
    const _dropdownMenus = new Map();
    const _itemLists = new Map();
    /**
     * Register default menus and set up event listeners.
     */
    function setup() {
        if (document.getElementById('mainMenu') !== null) {
            register('mainMenu');
        }
        const navigationHeader = document.querySelector('.navigationHeader');
        if (navigationHeader !== null) {
            register(Util_1.default.identify(navigationHeader));
        }
        window.addEventListener('resize', rebuildAll);
        Listener_1.default.add('WoltLabSuite/Core/Ui/FlexibleMenu', registerTabMenus);
    }
    exports.setup = setup;
    /**
     * Registers a menu by element id.
     */
    function register(containerId) {
        const container = document.getElementById(containerId);
        if (container === null) {
            throw "Expected a valid element id, '" + containerId + "' does not exist.";
        }
        if (_containers.has(containerId)) {
            return;
        }
        const list = DomTraverse.childByTag(container, 'UL');
        if (list === null) {
            throw "Expected an <ul> element as child of container '" + containerId + "'.";
        }
        _containers.set(containerId, container);
        _itemLists.set(containerId, list);
        rebuild(containerId);
    }
    exports.register = register;
    /**
     * Registers tab menus.
     */
    function registerTabMenus() {
        document.querySelectorAll('.tabMenuContainer:not(.jsFlexibleMenuEnabled), .messageTabMenu:not(.jsFlexibleMenuEnabled)').forEach(tabMenu => {
            const nav = DomTraverse.childByTag(tabMenu, 'NAV');
            if (nav !== null) {
                tabMenu.classList.add('jsFlexibleMenuEnabled');
                register(Util_1.default.identify(nav));
            }
        });
    }
    exports.registerTabMenus = registerTabMenus;
    /**
     * Rebuilds all menus, e.g. on window resize.
     */
    function rebuildAll() {
        _containers.forEach((container, containerId) => {
            rebuild(containerId);
        });
    }
    exports.rebuildAll = rebuildAll;
    /**
     * Rebuild the menu identified by given element id.
     */
    function rebuild(containerId) {
        const container = _containers.get(containerId);
        if (container === undefined) {
            throw "Expected a valid element id, '" + containerId + "' is unknown.";
        }
        const styles = window.getComputedStyle(container);
        const parent = container.parentNode;
        let availableWidth = parent.clientWidth;
        availableWidth -= Util_1.default.styleAsInt(styles, 'margin-left');
        availableWidth -= Util_1.default.styleAsInt(styles, 'margin-right');
        const list = _itemLists.get(containerId);
        const items = DomTraverse.childrenByTag(list, 'LI');
        let dropdown = _dropdowns.get(containerId);
        let dropdownWidth = 0;
        if (dropdown !== undefined) {
            // show all items for calculation
            for (let i = 0, length = items.length; i < length; i++) {
                const item = items[i];
                if (item.classList.contains('dropdown')) {
                    continue;
                }
                Util_1.default.show(item);
            }
            if (dropdown.parentNode !== null) {
                dropdownWidth = Util_1.default.outerWidth(dropdown);
            }
        }
        const currentWidth = list.scrollWidth - dropdownWidth;
        const hiddenItems = [];
        if (currentWidth > availableWidth) {
            // hide items starting with the last one
            for (let i = items.length - 1; i >= 0; i--) {
                const item = items[i];
                // ignore dropdown and active item
                if (item.classList.contains('dropdown') || item.classList.contains('active') || item.classList.contains('ui-state-active')) {
                    continue;
                }
                hiddenItems.push(item);
                Util_1.default.hide(item);
                if (list.scrollWidth < availableWidth) {
                    break;
                }
            }
        }
        if (hiddenItems.length) {
            let dropdownMenu;
            if (dropdown === undefined) {
                dropdown = document.createElement('li');
                dropdown.className = 'dropdown jsFlexibleMenuDropdown';
                const icon = document.createElement('a');
                icon.className = 'icon icon16 fa-list';
                dropdown.appendChild(icon);
                dropdownMenu = document.createElement('ul');
                dropdownMenu.classList.add('dropdownMenu');
                dropdown.appendChild(dropdownMenu);
                _dropdowns.set(containerId, dropdown);
                _dropdownMenus.set(containerId, dropdownMenu);
                Simple_1.default.init(icon);
            }
            else {
                dropdownMenu = _dropdownMenus.get(containerId);
            }
            if (dropdown.parentNode === null) {
                list.appendChild(dropdown);
            }
            // build dropdown menu
            const fragment = document.createDocumentFragment();
            hiddenItems.forEach(hiddenItem => {
                const item = document.createElement('li');
                item.innerHTML = hiddenItem.innerHTML;
                item.addEventListener('click', event => {
                    var _a;
                    event.preventDefault();
                    (_a = hiddenItem.querySelector('a')) === null || _a === void 0 ? void 0 : _a.click();
                    // force a rebuild to guarantee the active item being visible
                    setTimeout(() => {
                        rebuild(containerId);
                    }, 59);
                });
                fragment.appendChild(item);
            });
            dropdownMenu.innerHTML = '';
            dropdownMenu.appendChild(fragment);
        }
        else if (dropdown !== undefined && dropdown.parentNode !== null) {
            dropdown.remove();
        }
    }
    exports.rebuild = rebuild;
});
