/**
 * Data handler for a form builder field in an Ajax form represented by checkboxes.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Checkboxes
 * @since 5.2
 */

import Field from "./Field";
import { FormBuilderData } from "../Data";
import * as Core from "../../../Core";

class Checkboxes extends Field {
  protected _fields: HTMLInputElement[];

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
    this._fields = Array.from(document.querySelectorAll(`input[name="${this._fieldId}[]"]`));
  }
}

Core.enableLegacyInheritance(Checkboxes);

export = Checkboxes;
