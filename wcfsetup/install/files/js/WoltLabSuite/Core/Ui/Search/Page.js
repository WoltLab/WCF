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
define(["require", "exports", "../../Core", "../../Dom/Traverse", "../../Dom/Util", "../Dropdown/Simple", "../Screen", "./Input"], function (require, exports, Core, DomTraverse, Util_1, Simple_1, UiScreen, Input_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = void 0;
    Core = __importStar(Core);
    DomTraverse = __importStar(DomTraverse);
    Util_1 = __importDefault(Util_1);
    Simple_1 = __importDefault(Simple_1);
    UiScreen = __importStar(UiScreen);
    Input_1 = __importDefault(Input_1);
    function click(event) {
        event.preventDefault();
        const pageHeader = document.getElementById('pageHeader');
        pageHeader.classList.add('searchBarForceOpen');
        window.setTimeout(() => {
            pageHeader.classList.remove('searchBarForceOpen');
        }, 10);
        const target = event.currentTarget;
        const objectType = target.dataset.objectType;
        const container = document.getElementById('pageHeaderSearchParameters');
        container.innerHTML = '';
        const extendedLink = target.dataset.extendedLink;
        if (extendedLink) {
            const link = document.querySelector('.pageHeaderSearchExtendedLink');
            link.href = extendedLink;
        }
        const parameters = new Map();
        try {
            const data = JSON.parse(target.dataset.parameters || '');
            if (Core.isPlainObject(data)) {
                Object.keys(data).forEach(key => {
                    parameters.set(key, data[key]);
                });
            }
        }
        catch (e) {
        }
        if (objectType) {
            parameters.set('types[]', objectType);
        }
        parameters.forEach((value, key) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            container.appendChild(input);
        });
        // update label
        const inputContainer = document.getElementById('pageHeaderSearchInputContainer');
        const button = inputContainer.querySelector('.pageHeaderSearchType > .button > .pageHeaderSearchTypeLabel');
        button.textContent = target.textContent;
    }
    function init(objectType) {
        const searchInput = document.getElementById('pageHeaderSearchInput');
        new Input_1.default(searchInput, {
            ajax: {
                className: 'wcf\\data\\search\\keyword\\SearchKeywordAction',
            },
            autoFocus: false,
            callbackDropdownInit(dropdownMenu) {
                dropdownMenu.classList.add('dropdownMenuPageSearch');
                if (UiScreen.is('screen-lg')) {
                    dropdownMenu.dataset.dropdownAlignmentHorizontal = 'right';
                    const minWidth = searchInput.clientWidth;
                    dropdownMenu.style.setProperty('min-width', minWidth + 'px', '');
                    // calculate offset to ignore the width caused by the submit button
                    const parent = searchInput.parentElement;
                    const offsetRight = (Util_1.default.offset(parent).left + parent.clientWidth) - (Util_1.default.offset(searchInput).left + minWidth);
                    const offsetTop = Util_1.default.styleAsInt(window.getComputedStyle(parent), 'padding-bottom');
                    dropdownMenu.style.setProperty('transform', 'translateX(-' + Math.ceil(offsetRight) + 'px) translateY(-' + offsetTop + 'px)', '');
                }
            },
            callbackSelect() {
                setTimeout(() => {
                    const form = DomTraverse.parentByTag(searchInput, 'FORM');
                    form.submit();
                }, 1);
                return true;
            },
        });
        const searchType = document.querySelector('.pageHeaderSearchType');
        const dropdownMenu = Simple_1.default.getDropdownMenu(Util_1.default.identify(searchType));
        dropdownMenu.querySelectorAll('a[data-object-type]').forEach(link => {
            link.addEventListener('click', click);
        });
        // trigger click on init
        const link = dropdownMenu.querySelector('a[data-object-type="' + objectType + '"]');
        link.click();
    }
    exports.init = init;
});
