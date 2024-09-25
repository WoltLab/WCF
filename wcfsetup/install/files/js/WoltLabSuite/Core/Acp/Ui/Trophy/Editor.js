/**
 * Switches between trophy types, automatic awarding of
 * trophies and initialized the badge editor.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "./Badge"], function (require, exports, Badge_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    function setupTypeChange() {
        const badgeContainer = document.getElementById("badgeContainer");
        const imageContainer = document.getElementById("imageContainer");
        const typeSelection = document.querySelector("select[name=type]");
        typeSelection.addEventListener("change", () => {
            if (typeSelection.value === "1" /* TrophyType.Image */) {
                badgeContainer.hidden = true;
                imageContainer.hidden = false;
            }
            else if (typeSelection.value === "2" /* TrophyType.Badge */) {
                badgeContainer.hidden = false;
                imageContainer.hidden = true;
            }
        });
    }
    function setupAwardConditions() {
        const awardAutomatically = document.querySelector("input[name=awardAutomatically]");
        const revokeContainer = document.getElementById("revokeAutomaticallyDL");
        const revokeCheckbox = revokeContainer.querySelector("input");
        awardAutomatically.addEventListener("change", () => {
            document.querySelectorAll(".conditionSection").forEach((section) => {
                if (awardAutomatically.checked) {
                    section.hidden = false;
                }
                else {
                    section.hidden = true;
                }
            });
            if (awardAutomatically) {
                revokeContainer.classList.remove("disabled");
                revokeCheckbox.disabled = false;
            }
            else {
                revokeContainer.classList.add("disabled");
                revokeCheckbox.disabled = true;
                revokeCheckbox.checked = false;
            }
        });
    }
    function setup() {
        setupTypeChange();
        setupAwardConditions();
        (0, Badge_1.setup)();
    }
});
