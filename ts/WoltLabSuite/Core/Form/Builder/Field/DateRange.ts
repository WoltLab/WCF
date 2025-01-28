/**
 * Data handler for a date range form builder field in an Ajax form.
 *
 * @author    Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.2
 */
import Field from "./Field";
import { FormBuilderData } from "../Data";
import DatePicker from "../../../Date/Picker";

class DateRange extends Field {
  #fromField: HTMLElement | null;
  #toField: HTMLElement | null;

  constructor(fieldId: string) {
    super(fieldId);

    this.#fromField = document.getElementById(this._fieldId + "_from");
    if (this.#fromField === null) {
      throw new Error("Unknown field with id '" + this._fieldId + "'.");
    }

    this.#toField = document.getElementById(this._fieldId + "_to");
    if (this.#toField === null) {
      throw new Error("Unknown field with id '" + this._fieldId + "'.");
    }
  }

  protected _getData(): FormBuilderData {
    return {
      [this._fieldId]: {
        from: DatePicker.getValue(this.#fromField as HTMLInputElement),
        to: DatePicker.getValue(this.#toField as HTMLInputElement),
      },
    };
  }

  protected _readField(): void {}
}

export = DateRange;
