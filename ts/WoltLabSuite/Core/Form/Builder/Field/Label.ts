/**
 * Data handler for a label form field in an Ajax form.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import Field from "./Field";
import { FormBuilderData } from "../Data";

class Label extends Field {
  protected _getData(): FormBuilderData {
    const labelPicker = this._field as WoltlabCoreLabelPickerElement;

    return {
      [this._fieldId]: labelPicker.multiple ? labelPicker.values : labelPicker.value,
    };
  }
}

export = Label;
