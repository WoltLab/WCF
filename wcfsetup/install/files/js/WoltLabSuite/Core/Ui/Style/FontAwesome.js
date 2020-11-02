/**
 * Provides a selection dialog for FontAwesome icons with filter capabilities.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Style/FontAwesome
 */
define(["require", "exports", "tslib", "../../Language", "../Dialog", "../ItemList/Filter"], function (require, exports, tslib_1, Language, Dialog_1, Filter_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.open = exports.setup = void 0;
    Language = tslib_1.__importStar(Language);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    Filter_1 = tslib_1.__importDefault(Filter_1);
    class UiStyleFontAwesome {
        constructor(icons) {
            this.callback = undefined;
            this.iconList = undefined;
            this.itemListFilter = undefined;
            this.icons = icons;
        }
        open(callback) {
            this.callback = callback;
            Dialog_1.default.open(this);
        }
        /**
         * Selects an icon, notifies the callback and closes the dialog.
         */
        click(event) {
            event.preventDefault();
            const target = event.target;
            const item = target.closest("li");
            const icon = item.querySelector("small").textContent.trim();
            Dialog_1.default.close(this);
            this.callback(icon);
        }
        _dialogSetup() {
            return {
                id: "fontAwesomeSelection",
                options: {
                    onSetup: () => {
                        this.iconList = document.getElementById("fontAwesomeIcons");
                        // build icons
                        this.iconList.innerHTML = this.icons
                            .map((icon) => `<li><span class="icon icon48 fa-${icon}"></span><small>${icon}</small></li>`)
                            .join("");
                        this.iconList.addEventListener("click", (ev) => this.click(ev));
                        this.itemListFilter = new Filter_1.default("fontAwesomeIcons", {
                            callbackPrepareItem: (item) => {
                                const small = item.querySelector("small");
                                const text = small.textContent.trim();
                                return {
                                    item,
                                    span: small,
                                    text,
                                };
                            },
                            enableVisibilityFilter: false,
                            filterPosition: "top",
                        });
                    },
                    onShow: () => {
                        this.itemListFilter.reset();
                    },
                    title: Language.get("wcf.global.fontAwesome.selectIcon"),
                },
                source: '<ul class="fontAwesomeIcons" id="fontAwesomeIcons"></ul>',
            };
        }
    }
    let uiStyleFontAwesome;
    /**
     * Sets the list of available icons, must be invoked prior to any call
     * to the `open()` method.
     */
    function setup(icons) {
        if (!uiStyleFontAwesome) {
            uiStyleFontAwesome = new UiStyleFontAwesome(icons);
        }
    }
    exports.setup = setup;
    /**
     * Shows the FontAwesome selection dialog, supplied callback will be
     * invoked with the selection icon's name as the only argument.
     */
    function open(callback) {
        if (!uiStyleFontAwesome) {
            throw new Error("Missing icon data, please include the template before calling this method using `{include file='fontAwesomeJavaScript'}`.");
        }
        uiStyleFontAwesome.open(callback);
    }
    exports.open = open;
});
