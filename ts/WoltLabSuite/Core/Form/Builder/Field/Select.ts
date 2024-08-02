/**
 * Data handler for a form builder field in an Ajax form represented by select element that allows multiple selections.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */

import Field from "./Field";
import { FormBuilderData } from "../Data";

export default class Select extends Field {
  protected _getData(): FormBuilderData {
    const values = Array.from(this._field!.querySelectorAll<HTMLOptionElement>(`option`))
      .filter((input) => input.selected)
      .map((input) => input.value);

    return {
      [this._fieldId]: values,
    };
  }
}
