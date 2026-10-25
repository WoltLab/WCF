/**
 * Handles the interactions of the context menu inside a popover. The dropdown
 * menu is moved into the global container, the clicks on the interactions are
 * therefore forwarded to the popover content that serves as the container.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Api/Interactions/GetContextMenuOptions", "WoltLabSuite/Core/Ui/Dropdown/Simple", "../Popover"], function (require, exports, tslib_1, GetContextMenuOptions_1, Simple_1, Popover_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    Simple_1 = tslib_1.__importDefault(Simple_1);
    class PopoverInteractions {
        #container;
        #objectId;
        #popoverIdentifier;
        #providerClassName;
        constructor(container, popoverIdentifier, objectId, providerClassName) {
            this.#container = container;
            this.#popoverIdentifier = popoverIdentifier;
            this.#objectId = objectId;
            this.#providerClassName = providerClassName;
            this.#initInteractions();
            this.#initEventListeners();
        }
        #getToggle() {
            return this.#container.querySelector(".dropdownToggle");
        }
        #getDropdownMenu() {
            const toggle = this.#getToggle();
            if (toggle === null) {
                return undefined;
            }
            let dropdown = Simple_1.default.getDropdownMenu(toggle.dataset.target);
            dropdown ??= toggle.closest(".dropdown").querySelector(".dropdownMenu");
            return dropdown;
        }
        #initInteractions() {
            this.#getDropdownMenu()
                ?.querySelectorAll("[data-interaction]")
                .forEach((button) => {
                button.addEventListener("click", () => {
                    this.#container.dispatchEvent(new CustomEvent("interaction:execute", {
                        detail: button.dataset,
                    }));
                });
            });
        }
        async #refreshContextMenu() {
            const { template } = await (0, GetContextMenuOptions_1.getContextMenuOptions)(this.#providerClassName, this.#objectId);
            const dropdown = this.#getDropdownMenu();
            if (!dropdown) {
                return;
            }
            dropdown.innerHTML = template;
            this.#initInteractions();
        }
        #initEventListeners() {
            this.#container.addEventListener("interaction:invalidate", (event) => {
                event.stopPropagation();
                (0, Popover_1.resetCache)(this.#popoverIdentifier, this.#objectId);
                if (event.detail._reloadPage === "true") {
                    window.location.reload();
                }
                else {
                    void this.#refreshContextMenu();
                }
            });
            this.#container.addEventListener("interaction:invalidate-all", (event) => {
                event.stopPropagation();
                (0, Popover_1.resetCache)(this.#popoverIdentifier, this.#objectId);
                void this.#refreshContextMenu();
            });
            this.#container.addEventListener("interaction:remove", (event) => {
                event.stopPropagation();
                (0, Popover_1.resetCache)(this.#popoverIdentifier, this.#objectId);
                (0, Popover_1.destroyDropdowns)(this.#container);
                this.#container.remove();
            });
        }
    }
    function setup(container, popoverIdentifier, objectId, providerClassName) {
        new PopoverInteractions(container, popoverIdentifier, objectId, providerClassName);
    }
});
