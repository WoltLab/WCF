/**
 * Inserts smilies into a WYSIWYG editor instance, with WAI-ARIA keyboard support.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Smiley/Insert
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../../Core", "../../Event/Handler"], function (require, exports, tslib_1, Core, EventHandler) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    EventHandler = tslib_1.__importStar(EventHandler);
    class UiSmileyInsert {
        constructor(editorId) {
            this.editorId = editorId;
            let container = document.getElementById("smilies-" + this.editorId);
            if (!container) {
                // form builder
                container = document.getElementById(this.editorId + "SmiliesTabContainer");
                if (!container) {
                    throw new Error("Unable to find the message tab menu container containing the smilies.");
                }
            }
            this.container = container;
            this.container.addEventListener("keydown", (ev) => this.keydown(ev));
            this.container.addEventListener("mousedown", (ev) => this.mousedown(ev));
        }
        keydown(event) {
            const activeButton = document.activeElement;
            if (!activeButton.classList.contains("jsSmiley")) {
                return;
            }
            if (["ArrowLeft", "ArrowRight", "End", "Home"].includes(event.key)) {
                event.preventDefault();
                const target = event.currentTarget;
                const smilies = Array.from(target.querySelectorAll(".jsSmiley"));
                if (event.key === "ArrowLeft") {
                    smilies.reverse();
                }
                let index = smilies.indexOf(activeButton);
                if (event.key === "Home") {
                    index = 0;
                }
                else if (event.key === "End") {
                    index = smilies.length - 1;
                }
                else {
                    index = index + 1;
                    if (index === smilies.length) {
                        index = 0;
                    }
                }
                smilies[index].focus();
            }
            else if (event.key === "Enter" || event.key === "Space") {
                event.preventDefault();
                const image = activeButton.querySelector("img");
                this.insert(image);
            }
        }
        mousedown(event) {
            const target = event.target;
            // Clicks may occur on a few different elements, but we are only looking for the image.
            const listItem = target.closest("li");
            if (listItem && this.container.contains(listItem)) {
                event.preventDefault();
                const img = listItem.querySelector("img");
                if (img) {
                    this.insert(img);
                }
            }
        }
        insert(img) {
            EventHandler.fire("com.woltlab.wcf.redactor2", "insertSmiley_" + this.editorId, {
                img,
            });
        }
    }
    Core.enableLegacyInheritance(UiSmileyInsert);
    return UiSmileyInsert;
});
