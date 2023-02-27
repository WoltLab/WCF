/**
 * Data handler for a radio button form builder field in an Ajax form.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.2
 */

import Field from "./Field";
import { FormBuilderData } from "../Data";
import { escapeAttributeSelector } from "WoltLabSuite/Core/Dom/Util";

class RadioButton extends Field {
  protected _fields: HTMLInputElement[];

  constructor(fieldId: string) {
    super(fieldId);

    this._fields = Array.from(document.querySelectorAll(`input[name="${escapeAttributeSelector(this._fieldId)}"]`));
  }

  protected _getData(): FormBuilderData {
    const data = {};

    this._fields.some((input) => {
      if (input.checked) {
        data[this._fieldId] = input.value;
        return true;
      }

      return false;
    });

    return data;
  }

  protected _readField(): void {
    /* Does nothing. */
  }
}

export = RadioButton;
