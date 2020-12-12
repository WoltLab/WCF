/**
 * Data handler for a radio button form builder field in an Ajax form.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/RadioButton
 * @since 5.2
 */

import Field from "./Field";
import { FormBuilderData } from "../Data";
import * as Core from "../../../Core";

class RadioButton extends Field {
  protected _fields: HTMLInputElement[];

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
    this._fields = Array.from(document.querySelectorAll("input[name=" + this._fieldId + "]"));
  }
}

Core.enableLegacyInheritance(RadioButton);

export = RadioButton;
