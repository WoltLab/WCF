/**
 * Handles the JavaScript part of the rating form field.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Controller/Rating
 * @since 5.2
 */

import * as Core from "../../../../Core";
import * as Environment from "../../../../Environment";

class Rating {
  protected readonly _activeCssClasses: string[];
  protected readonly _defaultCssClasses: string[];
  protected readonly _field: HTMLElement;
  protected readonly _input: HTMLInputElement;
  protected readonly _ratingElements: Map<string, HTMLElement>;

  constructor(fieldId: string, value: string, activeCssClasses: string[], defaultCssClasses: string[]) {
    this._field = document.getElementById(fieldId + "Container")!;
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

    const ratingList = this._field.querySelector(".ratingList")!;
    ratingList.addEventListener("mouseleave", () => this._restoreRating());

    ratingList.querySelectorAll("li").forEach((listItem) => {
      if (listItem.classList.contains("ratingMetaButton")) {
        listItem.addEventListener("click", (ev) => this._metaButtonClick(ev));
        listItem.addEventListener("mouseenter", () => this._restoreRating());
      } else {
        this._ratingElements.set(listItem.dataset.rating!, listItem);

        listItem.addEventListener("click", (ev) => this._listItemClick(ev));
        listItem.addEventListener("mouseenter", (ev) => this._listItemMouseEnter(ev));
        listItem.addEventListener("mouseleave", () => this._listItemMouseLeave());
      }
    });
  }

  /**
   * Saves the rating associated with the clicked rating element.
   */
  protected _listItemClick(event: Event): void {
    const target = event.currentTarget as HTMLElement;
    this._input.value = target.dataset.rating!;

    if (Environment.platform() !== "desktop") {
      this._restoreRating();
    }
  }

  /**
   * Updates the rating UI when hovering over a rating element.
   */
  protected _listItemMouseEnter(event: Event): void {
    const target = event.currentTarget as HTMLElement;
    const currentRating = target.dataset.rating!;

    this._ratingElements.forEach((ratingElement, rating) => {
      const icon = ratingElement.getElementsByClassName("icon")[0]! as HTMLElement;

      this._toggleIcon(icon, ~~rating <= ~~currentRating);
    });
  }

  /**
   * Updates the rating UI when leaving a rating element by changing all rating elements
   * to their default state.
   */
  protected _listItemMouseLeave(): void {
    this._ratingElements.forEach((ratingElement) => {
      const icon = ratingElement.getElementsByClassName("icon")[0]! as HTMLElement;

      this._toggleIcon(icon, false);
    });
  }

  /**
   * Handles clicks on meta buttons.
   */
  protected _metaButtonClick(event: Event): void {
    const target = event.currentTarget as HTMLElement;
    if (target.dataset.action === "removeRating") {
      this._input.value = "";

      this._listItemMouseLeave();
    }
  }

  /**
   * Updates the rating UI by changing the rating elements to the stored rating state.
   */
  protected _restoreRating(): void {
    this._ratingElements.forEach((ratingElement, rating) => {
      const icon = ratingElement.getElementsByClassName("icon")[0]! as HTMLElement;

      this._toggleIcon(icon, ~~rating <= ~~this._input.value);
    });
  }

  /**
   * Toggles the state of the given icon based on the given state parameter.
   */
  protected _toggleIcon(icon: HTMLElement, active = false): void {
    if (active) {
      icon.classList.remove(...this._defaultCssClasses);
      icon.classList.add(...this._activeCssClasses);
    } else {
      icon.classList.remove(...this._activeCssClasses);
      icon.classList.add(...this._defaultCssClasses);
    }
  }
}

Core.enableLegacyInheritance(Rating);

export = Rating;
