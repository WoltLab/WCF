/**
 * Represents a button that provides a context menu with interactions.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Api/Interactions/GetContextMenuOptions", "WoltLabSuite/Core/Ui/Dropdown/Simple"], function (require, exports, tslib_1, GetContextMenuOptions_1, Simple_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.StandaloneButton = void 0;
    Simple_1 = tslib_1.__importDefault(Simple_1);
    class StandaloneButton {
        #container;
        #providerClassName;
        #objectId;
        #redirectUrl;
        constructor(container, providerClassName, objectId, redirectUrl) {
            this.#container = container;
            this.#providerClassName = providerClassName;
            this.#objectId = objectId;
            this.#redirectUrl = redirectUrl;
            this.#initInteractions();
            this.#initEventListeners();
        }
        async #refreshContextMenu() {
            const response = (await (0, GetContextMenuOptions_1.getContextMenuOptions)(this.#providerClassName, this.#objectId)).unwrap();
            const dropdown = this.#getDropdownMenu();
            if (!dropdown) {
                return;
            }
            dropdown.innerHTML = response.template;
            this.#initInteractions();
        }
        #getDropdownMenu() {
            const button = this.#container.querySelector(".dropdownToggle");
            if (!button) {
                return undefined;
            }
            let dropdown = Simple_1.default.getDropdownMenu(button.dataset.target);
            if (!dropdown) {
                dropdown = button.closest(".dropdown").querySelector(".dropdownMenu");
            }
            return dropdown;
        }
        #initInteractions() {
            this.#getDropdownMenu()
                ?.querySelectorAll("[data-interaction]")
                .forEach((element) => {
                element.addEventListener("click", () => {
                    this.#container.dispatchEvent(new CustomEvent("interaction", {
                        detail: element.dataset,
                        bubbles: true,
                    }));
                });
            });
        }
        #initEventListeners() {
            this.#container.addEventListener("refresh", () => {
                void this.#refreshContextMenu();
            });
            this.#container.addEventListener("remove", () => {
                window.location.href = this.#redirectUrl;
            });
        }
    }
    exports.StandaloneButton = StandaloneButton;
});
