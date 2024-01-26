/**
 * Provides a selection dialog for FontAwesome icons with filter capabilities.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../../Language", "../Dialog", "../ItemList/Filter"], function (require, exports, tslib_1, Language, Dialog_1, Filter_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.open = exports.setup = void 0;
    Language = tslib_1.__importStar(Language);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    Filter_1 = tslib_1.__importDefault(Filter_1);
    class UiStyleFontAwesome {
        callback = undefined;
        iconList = undefined;
        itemListFilter = undefined;
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
            const icon = item.querySelector("fa-icon");
            Dialog_1.default.close(this);
            this.callback(icon.name, icon.solid);
        }
        _dialogSetup() {
            return {
                id: "fontAwesomeSelection",
                options: {
                    onSetup: () => {
                        this.iconList = document.getElementById("fontAwesomeIcons");
                        const icons = [];
                        window.getFontAwesome6Metadata().forEach(([, hasRegular], name) => {
                            if (hasRegular) {
                                icons.push(`<li><fa-icon size="48" name="${name}" solid></fa-icon><small>${name}</small></li>`);
                            }
                            icons.push(`<li><fa-icon size="48" name="${name}"></fa-icon><small>${name}</small></li>`);
                        });
                        // build icons
                        this.iconList.innerHTML = icons.join("");
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
    function setup() {
        if (!uiStyleFontAwesome) {
            uiStyleFontAwesome = new UiStyleFontAwesome();
        }
    }
    exports.setup = setup;
    /**
     * Shows the FontAwesome selection dialog, supplied callback will be
     * invoked with the selection icon's name as the only argument.
     */
    function open(callback) {
        if (!uiStyleFontAwesome) {
          throw new Error(
			"Missing icon data, please include the template before calling this method using `{include file='shared_fontAwesomeJavaScript'}`.",
		);
        }
        uiStyleFontAwesome.open(callback);
    }
    exports.open = open;
});
