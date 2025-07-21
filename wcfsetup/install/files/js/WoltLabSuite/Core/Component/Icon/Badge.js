/**
 * Handles the display of an icon badge with customizable colors.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.IconBadge = void 0;
    class IconBadge {
        #iconContainer;
        #colorInput;
        #backgroundColorInput;
        constructor(iconFieldId, colorFieldId, backgroundColorFieldId) {
            this.#iconContainer = document.getElementById(`${iconFieldId}_icon`);
            this.#iconContainer.classList.add("iconBadge");
            const observer = new MutationObserver((mutationsList) => {
                for (const mutation of mutationsList) {
                    if (mutation.type === "attributes" && mutation.attributeName === "value") {
                        this.#updateIcon();
                    }
                }
            });
            if (colorFieldId) {
                this.#colorInput = document.getElementById(colorFieldId);
                observer.observe(this.#colorInput, {
                    attributes: true,
                    attributeFilter: ["value"],
                });
            }
            if (backgroundColorFieldId) {
                this.#backgroundColorInput = document.getElementById(backgroundColorFieldId);
                observer.observe(this.#backgroundColorInput, {
                    attributes: true,
                    attributeFilter: ["value"],
                });
            }
            this.#updateIcon();
        }
        #updateIcon() {
            this.#iconContainer.style.color = this.#colorInput?.value || "";
            this.#iconContainer.style.backgroundColor = this.#backgroundColorInput?.value || "";
        }
    }
    exports.IconBadge = IconBadge;
});
