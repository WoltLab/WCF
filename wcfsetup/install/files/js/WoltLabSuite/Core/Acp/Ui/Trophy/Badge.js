/**
 * Enables editing of the badge icon, color and
 * background-color.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../../../Ui/Style/FontAwesome", "../../../Ui/Color/Picker", "../../../Dom/Util"], function (require, exports, tslib_1, FontAwesome_1, Picker_1, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    Picker_1 = tslib_1.__importDefault(Picker_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    const badgeContainer = document.getElementById("badgeContainer");
    const previewWrapper = badgeContainer.querySelector(".trophyIcon");
    const previewIcon = previewWrapper.querySelector("fa-icon");
    function setupChangeIcon() {
        const button = badgeContainer.querySelector('.trophyIconEditButton[data-value="icon"]');
        const input = badgeContainer.querySelector('input[name="iconName"]');
        button.addEventListener("click", () => {
            (0, FontAwesome_1.open)((icon, forceSolid) => {
                previewIcon.setIcon(icon, forceSolid);
                input.value = `${icon};${String(forceSolid)}`;
            });
        });
    }
    function setupChangeColor() {
        const button = badgeContainer.querySelector('.trophyIconEditButton[data-value="color"]');
        const input = badgeContainer.querySelector('input[name="iconColor"]');
        button.dataset.store = Util_1.default.identify(input);
        new Picker_1.default(button, {
            callbackSubmit() {
                previewWrapper.style.setProperty("color", input.value);
            },
        });
    }
    function setupChangeBackgroundColor() {
        const button = badgeContainer.querySelector('.trophyIconEditButton[data-value="background-color"]');
        const input = badgeContainer.querySelector('input[name="badgeColor"]');
        button.dataset.store = Util_1.default.identify(input);
        new Picker_1.default(button, {
            callbackSubmit() {
                previewWrapper.style.setProperty("background-color", input.value);
            },
        });
    }
    function setup() {
        setupChangeIcon();
        setupChangeColor();
        setupChangeBackgroundColor();
    }
});
