/**
 * Data handler for a form builder field in an Ajax form represented by checkboxes.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.2
 */

import Field from "./Field";
import { FormBuilderData } from "../Data";
import { escapeAttributeSelector } from "WoltLabSuite/Core/Dom/Util";

class Checkboxes extends Field {
  protected _fields: HTMLInputElement[];

  constructor(fieldId: string) {
    super(fieldId);

    this._fields = Array.from(document.querySelectorAll(`input[name="${escapeAttributeSelector(this._fieldId)}[]"]`));
  }

  protected _getData(): FormBuilderData {
    const values = this._fields
      .map((input) => {
        if (input.checked) {
          return input.value;
        }

        return null;
      })
      .filter((v) => v !== null) as string[];

    return {
      [this._fieldId]: values,
    };
  }

  protected _readField(): void {
    /* Does nothing. */
  }
}

export = Checkboxes;
