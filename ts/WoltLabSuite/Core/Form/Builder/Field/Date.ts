/**
 * Data handler for a date form builder field in an Ajax form.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Date
 * @since 5.2
 */
import Field from "./Field";
import { FormBuilderData } from "../Data";
import DatePicker from "../../../Date/Picker";
import * as Core from "../../../Core";

class Date extends Field {
  protected _getData(): FormBuilderData {
    return {
      [this._fieldId]: DatePicker.getValue(this._field as HTMLInputElement),
    };
  }
}

Core.enableLegacyInheritance(Date);

export = Date;
