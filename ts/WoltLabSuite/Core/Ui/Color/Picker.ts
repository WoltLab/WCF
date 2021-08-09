/**
 * Wrapper class to provide color picker support. Constructing a new object does not
 * guarantee the picker to be ready at the time of call.
 *
 * @author      Alexander Ebert, Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Color/Picker
 * @woltlabExcludeBundle all
 */

import * as Core from "../../Core";
import UiDialog from "../Dialog";
import { DialogCallbackObject, DialogCallbackSetup } from "../Dialog/Data";
import DomUtil from "../../Dom/Util";
import * as Language from "../../Language";
import * as ColorUtil from "../../ColorUtil";

type CallbackSubmit = (data: ColorUtil.RGBA) => void;

interface ColorPickerOptions {
  callbackSubmit: CallbackSubmit;
}

class UiColorPicker implements DialogCallbackObject {
  protected alphaInput: HTMLInputElement | null = null;
  protected colorInput: HTMLInputElement | null = null;
  protected colorTextInput: HTMLInputElement | null = null;
  protected readonly element: HTMLElement;
  protected readonly input: HTMLInputElement;
  protected newColor: HTMLSpanElement | null = null;
  protected oldColor: HTMLSpanElement | null = null;
  protected readonly options: ColorPickerOptions;

  /**
   * Initializes a new color picker instance.
   */
  constructor(element: HTMLElement, options?: Partial<ColorPickerOptions>) {
    if (!(element instanceof Element)) {
      throw new TypeError(
        "Expected a valid DOM element, use `UiColorPicker.fromSelector()` if you want to use a CSS selector.",
      );
    }

    this.element = element;
    this.input = document.getElementById(element.dataset.store!) as HTMLInputElement;
    if (!this.input) {
      throw new Error(`Cannot find input element for color picker ${DomUtil.identify(element)}.`);
    }

    this.options = Core.extend(
      {
        callbackSubmit: null,
      },
      options || {},
    ) as ColorPickerOptions;

    element.addEventListener("click", () => this.openPicker());
  }

  public _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: `${DomUtil.identify(this.element)}_colorPickerDialog`,
      source: `
<div class="colorPickerDialog">
  <div class="row rowColGap formGrid">
    <div class="col-xs-12 col-md-8">
      <dl>
        <dt>${Language.get("wcf.style.colorPicker.color")}</dt>
        <dd>
          <input type="color">
        </dd>
      </dl>
      <dl>
        <dt>${Language.get("wcf.style.colorPicker.alpha")}</dt>
        <dd>
          <input type="range" min="0" max="1" step="0.01">
        </dd>
      </dl>
      <dl>
        <dt>${Language.get("wcf.style.colorPicker.hexAlpha")}</dt>
        <dd>
          <div class="inputAddon">
            <span class="inputPrefix">#</span>
            <input type="text" class="medium">
          </div>
        </dd>
      </dl>
    </div>
    <div class="col-xs-12 col-md-4 colorPickerComparison">
      <small>${Language.get("wcf.style.colorPicker.new")}</small>
      <div class="colorPickerColorNew">
        <span style="background-color: ${this.input.value}"></span>
      </div>
      <div class="colorPickerColorOld">
        <span style="background-color: ${this.input.value}"></span>
      </div>
      <small>${Language.get("wcf.style.colorPicker.current")}</small>
    </div>
  </div>
  <div class="formSubmit">
    <button class="buttonPrimary">${Language.get("wcf.style.colorPicker.button.apply")}</button>
  </div>
</div>`,
      options: {
        onSetup: (content) => {
          this.colorInput = content.querySelector("input[type=color]") as HTMLInputElement;
          this.colorInput.addEventListener("input", () => this.updateColor());
          this.alphaInput = content.querySelector("input[type=range]") as HTMLInputElement;
          this.alphaInput.addEventListener("input", () => this.updateColor());

          this.newColor = content.querySelector(".colorPickerColorNew > span") as HTMLSpanElement;
          this.oldColor = content.querySelector(".colorPickerColorOld > span") as HTMLSpanElement;

          this.colorTextInput = content.querySelector("input[type=text]") as HTMLInputElement;
          this.colorTextInput.addEventListener("blur", (ev) => this.updateColorFromHex(ev));
          this.colorTextInput.addEventListener("keypress", (ev) => this.updateColorFromHex(ev));

          content.querySelector(".formSubmit > .buttonPrimary")!.addEventListener("click", () => this.submitDialog());

          if (ColorUtil.isValidColor(this.input.value)) {
            this.setInitialColor(this.input.value);
          } else if (this.element.dataset.color && ColorUtil.isValidColor(this.element.dataset.color)) {
            this.setInitialColor(this.element.dataset.color);
          } else {
            this.setInitialColor("#FFF0");
          }
        },
        title: Language.get("wcf.style.colorPicker"),
      },
    };
  }

  /**
   * Sets the callback called after submitting the dialog.
   *
   * @deprecated  5.5, only exists for backward compatibility with the old `WCF.ColorPicker`;
   *              use the constructor options instead
   */
  public setCallbackSubmit(callbackSubmit: CallbackSubmit): void {
    this.options.callbackSubmit = callbackSubmit;
  }

  /**
   * Updates the current color after the color or alpha input changes its value.
   *
   * @since 5.5
   */
  protected updateColor(): void {
    this.setColor(this.getColor());
  }

  /**
   * Updates the current color after the hex input changes its value.
   *
   * @since 5.5
   */
  protected updateColorFromHex(event: Event): void {
    if (event instanceof KeyboardEvent && event.key !== "Enter") {
      return;
    }

    const colorTextInput = this.colorTextInput!;
    let color = colorTextInput.value;

    DomUtil.innerError(colorTextInput, null);
    if (!ColorUtil.isValidColor(color)) {
      if (ColorUtil.isValidColor(`#${color}`)) {
        color = `#${color}`;
      } else {
        DomUtil.innerError(colorTextInput, Language.get("wcf.style.colorPicker.error.invalidColor"));
        return;
      }
    }

    this.setColor(color);
  }

  /**
   * Returns the current RGBA color set via the color and alpha input.
   *
   * @since 5.5
   */
  protected getColor(): ColorUtil.RGBA {
    const color = this.colorInput!.value;
    const alpha = this.alphaInput!.value;

    return { ...(ColorUtil.hexToRgb(color) as ColorUtil.RGB), a: +alpha };
  }

  /**
   * Opens the color picker after clicking on the picker button.
   *
   * @since 5.5
   */
  protected openPicker(): void {
    UiDialog.open(this);
  }

  /**
   * Updates the UI to show the given color.
   *
   * @since 5.5
   */
  protected setColor(color: ColorUtil.RGBA | string): void {
    if (typeof color === "string") {
      color = ColorUtil.stringToRgba(color);
    }

    this.colorInput!.value = `#${ColorUtil.rgbToHex(color.r, color.g, color.b)}`;
    this.alphaInput!.value = color.a.toString();

    this.newColor!.style.backgroundColor = ColorUtil.rgbaToString(color);
    this.colorTextInput!.value = ColorUtil.rgbaToHex(color);
  }

  /**
   * Updates the UI to show the given color as the initial color.
   *
   * @since 5.5
   */
  protected setInitialColor(color: ColorUtil.RGBA | string): void {
    if (typeof color === "string") {
      color = ColorUtil.stringToRgba(color);
    }

    this.setColor(color);

    this.oldColor!.style.backgroundColor = ColorUtil.rgbaToString(color);
  }

  /**
   * Closes the color picker and updates the stored value.
   *
   * @since 5.5
   */
  protected submitDialog(): void {
    const color = this.getColor();
    const colorString = ColorUtil.rgbaToString(color);

    this.oldColor!.style.backgroundColor = colorString;
    this.input.value = colorString;

    const span = this.element.querySelector("span");
    if (span) {
      span.style.backgroundColor = colorString;
    } else {
      this.element.style.backgroundColor = colorString;
    }

    UiDialog.close(this);

    if (typeof this.options.callbackSubmit === "function") {
      this.options.callbackSubmit(color);
    }
  }

  /**
   * Initializes a color picker for all input elements matching the given selector.
   */
  static fromSelector(selector: string): void {
    document.querySelectorAll(selector).forEach((element: HTMLElement) => {
      new UiColorPicker(element);
    });
  }
}

Core.enableLegacyInheritance(UiColorPicker);

export = UiColorPicker;
