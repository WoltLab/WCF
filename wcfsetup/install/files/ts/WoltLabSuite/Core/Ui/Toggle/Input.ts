/**
 * Provides a simple toggle to show or hide certain elements when the
 * target element is checked.
 *
 * Be aware that the list of elements to show or hide accepts selectors
 * which will be passed to `elBySel()`, causing only the first matched
 * element to be used. If you require a whole list of elements identified
 * by a single selector to be handled, please provide the actual list of
 * elements instead.
 *
 * Usage:
 *
 * new UiToggleInput('input[name="foo"][value="bar"]', {
 *      show: ['#showThisContainer', '.makeThisVisibleToo'],
 *      hide: ['.notRelevantStuff', document.getElementById('fooBar')]
 * });
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Toggle/Input
 */

import DomUtil from "../../Dom/Util";

class UiToggleInput {
  private readonly element: HTMLInputElement;
  private readonly hide: HTMLElement[];
  private readonly show: HTMLElement[];

  /**
   * Initializes a new input toggle.
   */
  constructor(elementSelector: string, options: Partial<ToggleOptions>) {
    const element = document.querySelector(elementSelector) as HTMLInputElement;
    if (element === null) {
      throw new Error("Unable to find element by selector '" + elementSelector + "'.");
    }

    const type = element.nodeName === "INPUT" ? element.type : "";
    if (type !== "checkbox" && type !== "radio") {
      throw new Error("Illegal element, expected input[type='checkbox'] or input[type='radio'].");
    }

    this.element = element;

    this.hide = this.getElements("hide", Array.isArray(options.hide) ? options.hide : []);
    this.hide = this.getElements("show", Array.isArray(options.show) ? options.show : []);

    this.element.addEventListener("change", (ev) => this.change(ev));

    this.updateVisibility(this.show, this.element.checked);
    this.updateVisibility(this.hide, !this.element.checked);
  }

  private getElements(type: string, items: ElementOrSelector[]): HTMLElement[] {
    const elements: HTMLElement[] = [];
    items.forEach((item) => {
      let element: HTMLElement | null = null;
      if (typeof item === "string") {
        element = document.querySelector(item);
        if (element === null) {
          throw new Error(`Unable to find an element with the selector '${item}'.`);
        }
      } else if (item instanceof HTMLElement) {
        element = item;
      } else {
        throw new TypeError(`The array '${type}' may only contain string selectors or DOM elements.`);
      }

      elements.push(element);
    });

    return elements;
  }

  /**
   * Triggered when element is checked / unchecked.
   */
  private change(event: Event): void {
    const target = event.currentTarget as HTMLInputElement;
    const showElements = target.checked;

    this.updateVisibility(this.show, showElements);
    this.updateVisibility(this.hide, !showElements);
  }

  /**
   * Loops through the target elements and shows / hides them.
   */
  private updateVisibility(elements: HTMLElement[], showElement: boolean) {
    elements.forEach((element) => {
      DomUtil[showElement ? "show" : "hide"](element);
    });
  }
}

export = UiToggleInput;

type ElementOrSelector = Element | string;

interface ToggleOptions {
  show: ElementOrSelector[];
  hide: ElementOrSelector[];
}
