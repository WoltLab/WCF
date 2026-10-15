/**
 * Enables editing of the icon, the icon color and the background color of a
 * badge trophy.
 *
 * @author    Alexander Ebert, Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.3
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "../../../../Ui/Style/FontAwesome", "../../../../Ui/Color/Picker"], function (require, exports, tslib_1, FontAwesome_1, Picker_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    Picker_1 = tslib_1.__importDefault(Picker_1);
    function getInput(fieldId, name) {
        const input = document.getElementById(`${fieldId}_${name}`);
        if (!(input instanceof HTMLInputElement)) {
            throw new Error(`Unknown input field '${name}' of the field '${fieldId}'.`);
        }
        return input;
    }
    function getButton(container, value) {
        return container.querySelector(`button[data-value="${value}"]`);
    }
    function renderNativePreview(preview, icon, forceSolid) {
        let previewIcon = preview.querySelector("fa-icon");
        if (!(previewIcon instanceof HTMLElement) || previewIcon.parentElement !== preview) {
            preview.replaceChildren();
            previewIcon = document.createElement("fa-icon");
            previewIcon.size = 64;
            preview.append(previewIcon);
        }
        previewIcon.setIcon(icon, forceSolid);
    }
    function setupChangeIcon(container, preview, input) {
        getButton(container, "icon").addEventListener("click", () => {
            (0, FontAwesome_1.open)((icon, forceSolid, value, previewHtml) => {
                input.value = value ?? `${icon};${String(forceSolid)}`;
                if (typeof previewHtml === "string") {
                    preview.innerHTML = previewHtml;
                }
                else {
                    renderNativePreview(preview, icon, forceSolid);
                }
            });
        });
    }
    function setupColorPicker(button, preview, input, property) {
        button.dataset.store = input.id;
        new Picker_1.default(button, {
            callbackSubmit() {
                preview.style.setProperty(property, input.value);
            },
        });
    }
    function setup(fieldId) {
        const container = document.getElementById(`${fieldId}_container`);
        if (container === null) {
            throw new Error(`Unknown field with id '${fieldId}'.`);
        }
        const preview = container.querySelector(".trophyIcon");
        setupChangeIcon(container, preview, getInput(fieldId, "iconName"));
        setupColorPicker(getButton(container, "color"), preview, getInput(fieldId, "iconColor"), "color");
        setupColorPicker(getButton(container, "background-color"), preview, getInput(fieldId, "badgeColor"), "background-color");
    }
});
