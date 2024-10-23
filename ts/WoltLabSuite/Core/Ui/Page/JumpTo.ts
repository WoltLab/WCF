/**
 * Utility class to provide a 'Jump To' overlay.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import { DialogCallbackObject, DialogCallbackSetup } from "../Dialog/Data";
import * as Language from "../../Language";
import UiDialog from "../Dialog";

class UiPageJumpTo implements DialogCallbackObject {
  private activeElement: HTMLElement;
  private description: HTMLElement;
  private elements = new Map<HTMLElement, Callback>();
  private input: HTMLInputElement;
  private submitButton: HTMLButtonElement;

  /**
   * Initializes a 'Jump To' element.
   */
  init(element: HTMLElement, callback?: Callback | null): void {
    if (!callback) {
      const redirectUrl = element.dataset.link;
      if (redirectUrl) {
        callback = (pageNo) => {
          window.location.href = redirectUrl.replace(/pageNo=%d/, `pageNo=${pageNo}`);
        };
      } else {
        callback = () => {
          // Do nothing.
        };
      }
    } else if (typeof callback !== "function") {
      throw new TypeError("Expected a valid function for parameter 'callback'.");
    }

    if (!this.elements.has(element)) {
      element.querySelectorAll(".jumpTo").forEach((jumpTo: HTMLElement) => {
        jumpTo.addEventListener("click", (ev) => this.click(element, ev));
        this.elements.set(element, callback);
      });
    }
  }

  /**
   * Handles clicks on the trigger element.
   */
  private click(element: HTMLElement, event: MouseEvent): void {
    event.preventDefault();

    this.activeElement = element;

    UiDialog.open(this);

    const pages = element.dataset.pages || "0";
    this.input.value = pages;
    this.input.max = pages;
    this.input.select();

    this.description.textContent = Language.get("wcf.page.jumpTo.description").replace(/#pages#/, pages);
  }

  /**
   * Handles changes to the page number input field.
   *
   * @param  {object}  event    event object
   */
  _keyUp(event: KeyboardEvent): void {
    if (event.key === "Enter" && !this.submitButton.disabled) {
      this.submit();
      return;
    }

    const pageNo = +this.input.value;
    this.submitButton.disabled = pageNo < 1 || pageNo > +this.input.max;
  }

  /**
   * Invokes the callback with the chosen page number as first argument.
   */
  private submit(): void {
    const callback = this.elements.get(this.activeElement) as Callback;
    callback(+this.input.value);

    UiDialog.close(this);
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    const source = `<dl>
        <dt><label for="jsPaginationPageNo">${Language.get("wcf.page.jumpTo")}</label></dt>
                <dd>
          <input type="number" id="jsPaginationPageNo" value="1" min="1" max="1" class="tiny">
          <small></small>
        </dd>
      </dl>
      <div class="formSubmit">
        <button type="button" class="button buttonPrimary">${Language.get("wcf.global.button.submit")}</button>
      </div>`;

    return {
      id: "paginationOverlay",
      options: {
        onSetup: (content) => {
          this.input = content.querySelector("input")!;
          this.input.addEventListener("keyup", (ev) => this._keyUp(ev));

          this.description = content.querySelector("small")!;

          this.submitButton = content.querySelector("button")!;
          this.submitButton.addEventListener("click", () => this.submit());
        },
        title: Language.get("wcf.global.page.pagination"),
      },
      source: source,
    };
  }
}

let jumpTo: UiPageJumpTo | null = null;

function getUiPageJumpTo(): UiPageJumpTo {
  if (jumpTo === null) {
    jumpTo = new UiPageJumpTo();
  }

  return jumpTo;
}

/**
 * Initializes a 'Jump To' element.
 */
export function init(element: HTMLElement, callback?: Callback | null): void {
  getUiPageJumpTo().init(element, callback);
}

type Callback = (pageNo: number) => void;
