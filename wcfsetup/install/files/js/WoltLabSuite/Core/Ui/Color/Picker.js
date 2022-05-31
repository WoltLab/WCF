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
define(["require", "exports", "tslib", "../../Core", "../Dialog", "../../Dom/Util", "../../Language", "../../ColorUtil"], function (require, exports, tslib_1, Core, Dialog_1, Util_1, Language, ColorUtil) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    Language = tslib_1.__importStar(Language);
    ColorUtil = tslib_1.__importStar(ColorUtil);
    class UiColorPicker {
        /**
         * Initializes a new color picker instance.
         */
        constructor(element, options) {
            this.channels = new Map();
            this.colorInput = null;
            this.colorTextInput = null;
            this.hsl = new Map();
            this.hslContainer = undefined;
            this.newColor = undefined;
            this.oldColor = undefined;
            if (!(element instanceof Element)) {
                throw new TypeError("Expected a valid DOM element, use `UiColorPicker.fromSelector()` if you want to use a CSS selector.");
            }
            this.element = element;
            this.input = document.getElementById(element.dataset.store);
            if (!this.input) {
                throw new Error(`Cannot find input element for color picker ${Util_1.default.identify(element)}.`);
            }
            this.options = Core.extend({
                callbackSubmit: null,
            }, options || {});
            element.addEventListener("click", () => this.openPicker());
        }
        _dialogSetup() {
            return {
                id: `${Util_1.default.identify(this.element)}_colorPickerDialog`,
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
                        this.channels.set("r" /* R */, content.querySelector('input[data-channel="r"]'));
                        this.channels.set("g" /* G */, content.querySelector('input[data-channel="g"]'));
                        this.channels.set("b" /* B */, content.querySelector('input[data-channel="b"]'));
                        this.channels.set("a" /* A */, content.querySelector('input[data-channel="a"]'));
                        this.channels.forEach((input) => {
                            input.addEventListener("input", () => this.updateColor("rgba" /* RGBA */));
                        });
                        this.hslContainer = content.querySelector(".colorPickerHsvContainer");
                        this.hsl.set("hue" /* Hue */, content.querySelector('input[data-coordinate="hue"]'));
                        this.hsl.set("saturation" /* Saturation */, content.querySelector('input[data-coordinate="saturation"]'));
                        this.hsl.set("lightness" /* Lightness */, content.querySelector('input[data-coordinate="lightness"]'));
                        this.hsl.forEach((input) => {
                            input.addEventListener("input", () => this.updateColor("hsl" /* HSL */));
                        });
                        this.newColor = content.querySelector(".colorPickerColorNew > span");
                        this.oldColor = content.querySelector(".colorPickerColorOld > span");
                        this.colorTextInput = content.querySelector("input[type=text]");
                        this.colorTextInput.addEventListener("blur", (ev) => this.updateColorFromHex(ev));
                        this.colorTextInput.addEventListener("keypress", (ev) => this.updateColorFromHex(ev));
                        content.querySelector(".formSubmit > .buttonPrimary").addEventListener("click", () => this.submitDialog());
                        if (ColorUtil.isValidColor(this.input.value)) {
                            this.setInitialColor(this.input.value);
                        }
                        else if (this.element.dataset.color && ColorUtil.isValidColor(this.element.dataset.color)) {
                            this.setInitialColor(this.element.dataset.color);
                        }
                        else {
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
        setCallbackSubmit(callbackSubmit) {
            this.options.callbackSubmit = callbackSubmit;
        }
        /**
         * Updates the current color after the color or alpha input changes its value.
         *
         * @since 5.5
         */
        updateColor(source) {
            this.setColor(this.getColor(source), source);
        }
        /**
         * Updates the current color after the hex input changes its value.
         *
         * @since 5.5
         */
        updateColorFromHex(event) {
            if (event instanceof KeyboardEvent && event.key !== "Enter") {
                return;
            }
            const colorTextInput = this.colorTextInput;
            let color = colorTextInput.value;
            Util_1.default.innerError(colorTextInput, null);
            if (!ColorUtil.isValidColor(color)) {
                if (ColorUtil.isValidColor(`#${color}`)) {
                    color = `#${color}`;
                }
                else {
                    Util_1.default.innerError(colorTextInput, Language.get("wcf.style.colorPicker.error.invalidColor"));
                    return;
                }
            }
            this.setColor(color, "hex" /* HEX */);
        }
        /**
         * Returns the current RGBA color set via the color and alpha input.
         *
         * @since 5.5
         */
        getColor(source) {
            const a = parseFloat(this.channels.get("a" /* A */).value);
            if (source === "hsl" /* HSL */) {
                const rgb = ColorUtil.hslToRgb(parseInt(this.hsl.get("hue" /* Hue */).value, 10), parseInt(this.hsl.get("saturation" /* Saturation */).value, 10), parseInt(this.hsl.get("lightness" /* Lightness */).value, 10));
                return {
                    ...rgb,
                    a,
                };
            }
            return {
                r: parseInt(this.channels.get("r" /* R */).value, 10),
                g: parseInt(this.channels.get("g" /* G */).value, 10),
                b: parseInt(this.channels.get("b" /* B */).value, 10),
                a,
            };
        }
        /**
         * Opens the color picker after clicking on the picker button.
         *
         * @since 5.5
         */
        openPicker() {
            Dialog_1.default.open(this);
        }
        /**
         * Updates the UI to show the given color.
         *
         * @since 5.5
         */
        setColor(color, source) {
            if (typeof color === "string") {
                color = ColorUtil.stringToRgba(color);
            }
            const { r, g, b, a } = color;
            const { h, s, l } = ColorUtil.rgbToHsl(r, g, b);
            if (source !== "hsl" /* HSL */) {
                this.hsl.get("hue" /* Hue */).value = h.toString();
                this.hsl.get("saturation" /* Saturation */).value = s.toString();
                this.hsl.get("lightness" /* Lightness */).value = l.toString();
            }
            this.hslContainer.style.setProperty(`--${"hue" /* Hue */}`, `${h}`);
            this.hslContainer.style.setProperty(`--${"saturation" /* Saturation */}`, `${s}%`);
            this.hslContainer.style.setProperty(`--${"lightness" /* Lightness */}`, `${l}%`);
            if (source !== "rgba" /* RGBA */) {
                this.channels.get("r" /* R */).value = r.toString();
                this.channels.get("g" /* G */).value = g.toString();
                this.channels.get("b" /* B */).value = b.toString();
                this.channels.get("a" /* A */).value = a.toString();
            }
            this.newColor.style.backgroundColor = ColorUtil.rgbaToString(color);
            if (source !== "hex" /* HEX */) {
                this.colorTextInput.value = ColorUtil.rgbaToHex(color);
            }
        }
        /**
         * Updates the UI to show the given color as the initial color.
         *
         * @since 5.5
         */
        setInitialColor(color) {
            if (typeof color === "string") {
                color = ColorUtil.stringToRgba(color);
            }
            this.setColor(color, "setup" /* Setup */);
            this.oldColor.style.backgroundColor = ColorUtil.rgbaToString(color);
        }
        /**
         * Closes the color picker and updates the stored value.
         *
         * @since 5.5
         */
        submitDialog() {
            const color = this.getColor("rgba" /* RGBA */);
            const colorString = ColorUtil.rgbaToString(color);
            this.oldColor.style.backgroundColor = colorString;
            this.input.value = colorString;
            const span = this.element.querySelector("span");
            if (span) {
                span.style.backgroundColor = colorString;
            }
            else {
                this.element.style.backgroundColor = colorString;
            }
            Dialog_1.default.close(this);
            if (typeof this.options.callbackSubmit === "function") {
                this.options.callbackSubmit(color);
            }
        }
        /**
         * Initializes a color picker for all input elements matching the given selector.
         */
        static fromSelector(selector) {
            document.querySelectorAll(selector).forEach((element) => {
                new UiColorPicker(element);
            });
        }
    }
    Core.enableLegacyInheritance(UiColorPicker);
    return UiColorPicker;
});
