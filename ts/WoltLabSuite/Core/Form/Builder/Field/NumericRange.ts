/**
 * Data handler for a numeric range form builder field in an Ajax form.
 *
 * @author    Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.2
 */
import Field from "./Field";
import { FormBuilderData } from "../Data";

class NumericRange extends Field {
  #fromField: HTMLInputElement | null;
  #toField: HTMLInputElement | null;

  constructor(fieldId: string) {
    super(fieldId);

    this.#fromField = document.getElementById(this._fieldId + "_from") as HTMLInputElement;
    if (this.#fromField === null) {
      throw new Error("Unknown field with id '" + this._fieldId + "'.");
    }

    this.#toField = document.getElementById(this._fieldId + "_to") as HTMLInputElement;
    if (this.#toField === null) {
      throw new Error("Unknown field with id '" + this._fieldId + "'.");
    }
  }

  protected _getData(): FormBuilderData {
    return {
      [this._fieldId]: {
        from: this.#fromField!.value,
        to: this.#toField!.value,
      },
    };
  }

  protected _readField(): void {}
}

export = NumericRange;
