/**
 * Wrapper class to provide color picker support. Constructing a new object does not
 * guarantee the picker to be ready at the time of call.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Color/Picker
 * @woltlabExcludeBundle all
 */

import * as Core from "../../Core";

let _marshal = (element: HTMLElement, options: ColorPickerOptions) => {
  if (typeof window.WCF === "object" && typeof window.WCF.ColorPicker === "function") {
    _marshal = (element, options) => {
      const picker = new window.WCF.ColorPicker(element);

      if (typeof options.callbackSubmit === "function") {
        picker.setCallbackSubmit(options.callbackSubmit);
      }

      return picker;
    };

    return _marshal(element, options);
  } else {
    if (_queue.length === 0) {
      window.__wcf_bc_colorPickerInit = () => {
        _queue.forEach((data) => {
          _marshal(data[0], data[1]);
        });

        window.__wcf_bc_colorPickerInit = undefined;
        _queue = [];
      };
    }

    _queue.push([element, options]);
  }
};

type QueueItem = [HTMLElement, ColorPickerOptions];

let _queue: QueueItem[] = [];

interface CallbackSubmitPayload {
  r: number;
  g: number;
  b: number;
  a: number;
}

interface ColorPickerOptions {
  callbackSubmit: (data: CallbackSubmitPayload) => void;
}

class UiColorPicker {
  /**
   * Initializes a new color picker instance. This is actually just a wrapper that does
   * not guarantee the picker to be ready at the time of call.
   */
  constructor(element: HTMLElement, options?: Partial<ColorPickerOptions>) {
    if (!(element instanceof Element)) {
      throw new TypeError(
        "Expected a valid DOM element, use `UiColorPicker.fromSelector()` if you want to use a CSS selector.",
      );
    }

    options = Core.extend(
      {
        callbackSubmit: null,
      },
      options || {},
    );

    _marshal(element, options as ColorPickerOptions);
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
