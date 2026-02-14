/**
 * Provides a selection dialog for FontAwesome icons with filter capabilities.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Language", "../ItemList/Filter", "WoltLabSuite/Core/Component/Dialog"], function (require, exports, tslib_1, Language_1, Filter_1, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.open = open;
    Filter_1 = tslib_1.__importDefault(Filter_1);
    function createIconList() {
        const ul = document.createElement("ul");
        ul.classList.add("fontAwesome__icons");
        ul.id = "fontAwesomeIcons";
        const icons = [];
        window.getFontAwesome7Metadata().forEach(([, hasRegular], name) => {
            if (hasRegular) {
                icons.push(`<li><button type="button" class="fontAwesome__icon"><fa-icon size="48" name="${name}" solid></fa-icon><small class="fontAwesome__icon__name">${name}</small></button></li>`);
            }
            icons.push(`<li><button type="button" class="fontAwesome__icon"><fa-icon size="48" name="${name}"></fa-icon><small class="fontAwesome__icon__name">${name}</small></button></li>`);
        });
        ul.innerHTML = icons.join("");
        return ul;
    }
    let content = undefined;
    function getContent() {
        if (content === undefined) {
            const iconList = createIconList();
            iconList.addEventListener("click", (event) => {
                event.preventDefault();
                const { target } = event;
                if (!(target instanceof HTMLButtonElement)) {
                    return;
                }
                const icon = target.querySelector("fa-icon");
                const selectedEvent = new CustomEvent("font-awesome:selected", {
                    bubbles: true,
                    detail: {
                        icon: icon.name,
                        forceSolid: icon.solid,
                    },
                });
                iconList.dispatchEvent(selectedEvent);
            });
            content = document.createElement("div");
            content.id = "fontAwesomeSelection";
            content.append(iconList);
        }
        return content;
    }
    let itemListFilter = undefined;
    function setupListeners() {
        if (itemListFilter === undefined) {
            itemListFilter = new Filter_1.default("fontAwesomeIcons", {
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
        }
        else {
            itemListFilter.reset();
        }
    }
    /**
     * Shows the FontAwesome selection dialog, supplied callback will be
     * invoked with the selection icon's name as the only argument.
     */
    function open(callback) {
        const dialog = (0, Dialog_1.dialogFactory)().fromElement(getContent()).asConfirmation();
        dialog.addEventListener("font-awesome:selected", (event) => {
            dialog.close();
            callback(event.detail.icon, event.detail.forceSolid);
        }, { once: true });
        dialog.show((0, Language_1.getPhrase)("wcf.global.fontAwesome.selectIcon"));
        setupListeners();
    }
});
