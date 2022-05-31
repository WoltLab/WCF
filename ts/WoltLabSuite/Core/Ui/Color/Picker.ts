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

const enum Channel {
  R = "r",
  G = "g",
  B = "b",
  A = "a",
}

const enum HSL {
  Hue = "hue",
  Saturation = "saturation",
  Lightness = "lightness",
}

const enum ColorSource {
  HEX = "hex",
  HSL = "hsl",
  RGBA = "rgba",
  Setup = "setup",
}

interface ColorPickerOptions {
  callbackSubmit: CallbackSubmit;
}

class UiColorPicker implements DialogCallbackObject {
  private readonly channels = new Map<Channel, HTMLInputElement>();
  protected colorInput: HTMLInputElement | null = null;
  protected colorTextInput: HTMLInputElement | null = null;
  protected readonly element: HTMLElement;
  private readonly hsl = new Map<HSL, HTMLInputElement>();
  private hslContainer?: HTMLElement = undefined;
  protected readonly input: HTMLInputElement;
  protected newColor?: HTMLElement = undefined;
  protected oldColor?: HTMLElement = undefined;
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
  <div class="colorPickerHsvContainer" style="--hue: 0; --saturation: 0%; --lightness: 0%">
    <dl>
        <dt>${Language.get("wcf.style.colorPicker.hue")}</dt>
        <dd>
          <input type="range" min="0" max="359" class="colorPickerHslRange" data-coordinate="hue">
        </dd>
    </dl>
    <dl>
        <dt>${Language.get("wcf.style.colorPicker.saturation")}</dt>
        <dd>
          <input type="range" min="0" max="100" class="colorPickerHslRange" data-coordinate="saturation">
        </dd>
    </dl>
    <dl>
        <dt>${Language.get("wcf.style.colorPicker.lightness")}</dt>
        <dd>
          <input type="range" min="0" max="100" class="colorPickerHslRange" data-coordinate="lightness">
        </dd>
    </dl>
  </div>
  <div class="colorPickerValueContainer">
    <div>
      <dl>
        <dt>${Language.get("wcf.style.colorPicker.color")}</dt>
        <dd class="colorPickerChannels">
          rgba(
          <input type="number" min="0" max="255" size="3" class="colorPickerChannel" data-channel="r">
          <input type="number" min="0" max="255" size="3" class="colorPickerChannel" data-channel="g">
          <input type="number" min="0" max="255" size="3" class="colorPickerChannel" data-channel="b">
          /
          <input type="number" min="0" max="1" step="0.01" size="3" class="colorPickerChannel" data-channel="a">
          )
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
    <div class="colorPickerComparison">
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
    <button class="button buttonPrimary">${Language.get("wcf.style.colorPicker.button.apply")}</button>
  </div>
</div>`,
      options: {
        onSetup: (content) => {
          this.channels.set(Channel.R, content.querySelector('input[data-channel="r"]') as HTMLInputElement);
          this.channels.set(Channel.G, content.querySelector('input[data-channel="g"]') as HTMLInputElement);
          this.channels.set(Channel.B, content.querySelector('input[data-channel="b"]') as HTMLInputElement);
          this.channels.set(Channel.A, content.querySelector('input[data-channel="a"]') as HTMLInputElement);
          this.channels.forEach((input) => {
            input.addEventListener("input", () => this.updateColor(ColorSource.RGBA));
          });

          this.hslContainer = content.querySelector(".colorPickerHsvContainer") as HTMLElement;
          this.hsl.set(HSL.Hue, content.querySelector('input[data-coordinate="hue"]') as HTMLInputElement);
          this.hsl.set(
            HSL.Saturation,
            content.querySelector('input[data-coordinate="saturation"]') as HTMLInputElement,
          );
          this.hsl.set(HSL.Lightness, content.querySelector('input[data-coordinate="lightness"]') as HTMLInputElement);
          this.hsl.forEach((input) => {
            input.addEventListener("input", () => this.updateColor(ColorSource.HSL));
          });

          this.newColor = content.querySelector(".colorPickerColorNew > span") as HTMLElement;
          this.oldColor = content.querySelector(".colorPickerColorOld > span") as HTMLElement;

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
  protected updateColor(source: ColorSource): void {
    this.setColor(this.getColor(source), source);
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

    this.setColor(color, ColorSource.HEX);
  }

  /**
   * Returns the current RGBA color set via the color and alpha input.
   *
   * @since 5.5
   */
  protected getColor(source: ColorSource): ColorUtil.RGBA {
    const a = parseFloat(this.channels.get(Channel.A)!.value);

    if (source === ColorSource.HSL) {
      const rgb = ColorUtil.hslToRgb(
        parseInt(this.hsl.get(HSL.Hue)!.value, 10),
        parseInt(this.hsl.get(HSL.Saturation)!.value, 10),
        parseInt(this.hsl.get(HSL.Lightness)!.value, 10),
      );

      return {
        ...rgb,
        a,
      };
    }

    return {
      r: parseInt(this.channels.get(Channel.R)!.value, 10),
      g: parseInt(this.channels.get(Channel.G)!.value, 10),
      b: parseInt(this.channels.get(Channel.B)!.value, 10),
      a,
    };
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
  protected setColor(color: ColorUtil.RGBA | string, source: ColorSource): void {
    if (typeof color === "string") {
      color = ColorUtil.stringToRgba(color);
    }

    const { r, g, b, a } = color;
    const { h, s, l } = ColorUtil.rgbToHsl(r, g, b);

    if (source !== ColorSource.HSL) {
      this.hsl.get(HSL.Hue)!.value = h.toString();
      this.hsl.get(HSL.Saturation)!.value = s.toString();
      this.hsl.get(HSL.Lightness)!.value = l.toString();
    }

    this.hslContainer!.style.setProperty(`--${HSL.Hue}`, `${h}`);
    this.hslContainer!.style.setProperty(`--${HSL.Saturation}`, `${s}%`);
    this.hslContainer!.style.setProperty(`--${HSL.Lightness}`, `${l}%`);

    if (source !== ColorSource.RGBA) {
      this.channels.get(Channel.R)!.value = r.toString();
      this.channels.get(Channel.G)!.value = g.toString();
      this.channels.get(Channel.B)!.value = b.toString();
      this.channels.get(Channel.A)!.value = a.toString();
    }

    this.newColor!.style.backgroundColor = ColorUtil.rgbaToString(color);

    if (source !== ColorSource.HEX) {
      this.colorTextInput!.value = ColorUtil.rgbaToHex(color);
    }
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

    this.setColor(color, ColorSource.Setup);

    this.oldColor!.style.backgroundColor = ColorUtil.rgbaToString(color);
  }

  /**
   * Closes the color picker and updates the stored value.
   *
   * @since 5.5
   */
  protected submitDialog(): void {
    const color = this.getColor(ColorSource.RGBA);
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
