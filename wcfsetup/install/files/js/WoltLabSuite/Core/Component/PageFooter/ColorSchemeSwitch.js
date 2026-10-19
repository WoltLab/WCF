/**
 * Switches the color scheme from the page footer. Members store the choice in
 * their account, guests in the browser.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "tslib", "../../User", "../../Controller/Style/ColorScheme", "../../Api/Users/SetColorScheme"], function (require, exports, tslib_1, User_1, ColorScheme_1, SetColorScheme_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    User_1 = tslib_1.__importDefault(User_1);
    let isSaving = false;
    let pendingColorScheme;
    // Saves one request at a time and skips intermediate values, so a late response
    // cannot overwrite a newer choice, e.g. while stepping through the radios.
    async function save(colorScheme) {
        pendingColorScheme = colorScheme;
        if (isSaving) {
            return;
        }
        isSaving = true;
        try {
            while (pendingColorScheme !== undefined) {
                const next = pendingColorScheme;
                pendingColorScheme = undefined;
                await (0, SetColorScheme_1.setColorScheme)(next);
            }
        }
        finally {
            isSaving = false;
        }
    }
    function setup() {
        const fieldset = document.querySelector(".pageFooterScheme");
        if (fieldset === null) {
            return;
        }
        // The server does not know the choice of a guest.
        if (User_1.default.userId === 0) {
            const colorScheme = (0, ColorScheme_1.getStoredColorScheme)() ?? "system";
            fieldset.querySelector(`input[value="${colorScheme}"]`).checked = true;
        }
        fieldset.addEventListener("change", (event) => {
            const colorScheme = event.target.value;
            (0, ColorScheme_1.setColorScheme)(colorScheme);
            if (User_1.default.userId === 0) {
                (0, ColorScheme_1.storeColorScheme)(colorScheme);
            }
            else {
                void save(colorScheme);
            }
        });
    }
});
