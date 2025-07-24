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
    exports.setup = setup;
    function setup(iconFieldId, colorFieldId, backgroundColorFieldId) {
        const container = document.getElementById(`${iconFieldId}_icon`);
        container.classList.add("iconBadge");
        if (colorFieldId !== "") {
            const colorInput = document.getElementById(colorFieldId);
            colorInput.addEventListener("color-picker:submit", () => {
                container.style.setProperty("color", colorInput.value);
            });
            container.style.setProperty("color", colorInput.value);
        }
        if (backgroundColorFieldId !== "") {
            const backgroundColorInput = document.getElementById(backgroundColorFieldId);
            backgroundColorInput.addEventListener("color-picker:submit", () => {
                container.style.setProperty("background-color", backgroundColorInput.value);
            });
            container.style.setProperty("background-color", backgroundColorInput.value);
        }
    }
});
