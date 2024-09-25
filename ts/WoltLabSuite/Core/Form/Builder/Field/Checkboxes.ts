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
  protected _getData(): FormBuilderData {
    const values = this._fields
      .map((input) => {
        if (input.checked) {
          return input.value;
        }

        return null;
      })
      .filter((v) => v !== null);

    return {
      [this._fieldId]: values,
    };
  }

  protected _readField(): void {
    /* Does nothing. */
  }

  protected get _fields(): HTMLInputElement[] {
    return Array.from(document.querySelectorAll(`input[name="${escapeAttributeSelector(this._fieldId)}[]"]`));
  }
}

export = Checkboxes;
