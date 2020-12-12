/**
 * Handles the JavaScript part of the rating form field.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Controller/Rating
 * @since 5.2
 */
define(["require", "exports", "tslib", "../../../../Core", "../../../../Environment"], function (require, exports, tslib_1, Core, Environment) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    Environment = tslib_1.__importStar(Environment);
    class Rating {
        constructor(fieldId, value, activeCssClasses, defaultCssClasses) {
            this._field = document.getElementById(fieldId + "Container");
            if (this._field === null) {
                throw new Error("Unknown field with id '" + fieldId + "'");
            }
            this._input = document.createElement("input");
            this._input.id = fieldId;
            this._input.name = fieldId;
            this._input.type = "hidden";
            this._input.value = value;
            this._field.appendChild(this._input);
            this._activeCssClasses = activeCssClasses;
            this._defaultCssClasses = defaultCssClasses;
            this._ratingElements = new Map();
            const ratingList = this._field.querySelector(".ratingList");
            ratingList.addEventListener("mouseleave", () => this._restoreRating());
            ratingList.querySelectorAll("li").forEach((listItem) => {
                if (listItem.classList.contains("ratingMetaButton")) {
                    listItem.addEventListener("click", (ev) => this._metaButtonClick(ev));
                    listItem.addEventListener("mouseenter", () => this._restoreRating());
                }
                else {
                    this._ratingElements.set(listItem.dataset.rating, listItem);
                    listItem.addEventListener("click", (ev) => this._listItemClick(ev));
                    listItem.addEventListener("mouseenter", (ev) => this._listItemMouseEnter(ev));
                    listItem.addEventListener("mouseleave", () => this._listItemMouseLeave());
                }
            });
        }
        /**
         * Saves the rating associated with the clicked rating element.
         */
        _listItemClick(event) {
            const target = event.currentTarget;
            this._input.value = target.dataset.rating;
            if (Environment.platform() !== "desktop") {
                this._restoreRating();
            }
        }
        /**
         * Updates the rating UI when hovering over a rating element.
         */
        _listItemMouseEnter(event) {
            const target = event.currentTarget;
            const currentRating = target.dataset.rating;
            this._ratingElements.forEach((ratingElement, rating) => {
                const icon = ratingElement.getElementsByClassName("icon")[0];
                this._toggleIcon(icon, ~~rating <= ~~currentRating);
            });
        }
        /**
         * Updates the rating UI when leaving a rating element by changing all rating elements
         * to their default state.
         */
        _listItemMouseLeave() {
            this._ratingElements.forEach((ratingElement) => {
                const icon = ratingElement.getElementsByClassName("icon")[0];
                this._toggleIcon(icon, false);
            });
        }
        /**
         * Handles clicks on meta buttons.
         */
        _metaButtonClick(event) {
            const target = event.currentTarget;
            if (target.dataset.action === "removeRating") {
                this._input.value = "";
                this._listItemMouseLeave();
            }
        }
        /**
         * Updates the rating UI by changing the rating elements to the stored rating state.
         */
        _restoreRating() {
            this._ratingElements.forEach((ratingElement, rating) => {
                const icon = ratingElement.getElementsByClassName("icon")[0];
                this._toggleIcon(icon, ~~rating <= ~~this._input.value);
            });
        }
        /**
         * Toggles the state of the given icon based on the given state parameter.
         */
        _toggleIcon(icon, active = false) {
            if (active) {
                icon.classList.remove(...this._defaultCssClasses);
                icon.classList.add(...this._activeCssClasses);
            }
            else {
                icon.classList.remove(...this._activeCssClasses);
                icon.classList.add(...this._defaultCssClasses);
            }
        }
    }
    Core.enableLegacyInheritance(Rating);
    return Rating;
});
