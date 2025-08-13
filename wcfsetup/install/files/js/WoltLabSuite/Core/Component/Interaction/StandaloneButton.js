/**
 * Represents a button that provides a context menu with interactions.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Api/GetObject", "WoltLabSuite/Core/Api/Interactions/GetContextMenuOptions", "WoltLabSuite/Core/Ui/Dropdown/Simple"], function (require, exports, tslib_1, GetObject_1, GetContextMenuOptions_1, Simple_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.StandaloneButton = void 0;
    Simple_1 = tslib_1.__importDefault(Simple_1);
    class StandaloneButton {
        #container;
        #providerClassName;
        #objectId;
        #redirectUrl;
        #reloadHeaderEndpoint;
        constructor(container, providerClassName, objectId, redirectUrl, reloadHeaderEndpoint) {
            this.#container = container;
            this.#providerClassName = providerClassName;
            this.#objectId = objectId;
            this.#redirectUrl = redirectUrl;
            this.#reloadHeaderEndpoint = reloadHeaderEndpoint;
            this.#initInteractions();
            this.#initEventListeners();
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
        async #refreshHeader() {
            if (!this.#reloadHeaderEndpoint) {
                return;
            }
            const header = document.querySelector(".contentHeaderTitle");
            if (!header) {
                return;
            }
            const result = await (0, GetObject_1.getObject)(`${window.WSC_RPC_API_URL}${this.#reloadHeaderEndpoint}`);
            if (!result.ok) {
                return;
            }
            header.outerHTML = result.value.template;
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
                    this.#container.dispatchEvent(new CustomEvent("interaction:execute", {
                        detail: element.dataset,
                        bubbles: true,
                    }));
                });
            });
        }
        #initEventListeners() {
            this.#container.addEventListener("interaction:invalidate", (event) => {
                if (event.detail._reloadPage === "true") {
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                }
                else {
                    void this.#refreshContextMenu();
                    void this.#refreshHeader();
                }
            });
            this.#container.addEventListener("interaction:invalidate-all", () => {
                void this.#refreshContextMenu();
                void this.#refreshHeader();
            });
            this.#container.addEventListener("interaction:remove", () => {
                setTimeout(() => {
                    window.location.href = this.#redirectUrl;
                }, 2000);
            });
        }
    }
    exports.StandaloneButton = StandaloneButton;
});
