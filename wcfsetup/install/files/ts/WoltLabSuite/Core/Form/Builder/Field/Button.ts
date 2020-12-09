/**
 * Data handler for a button form builder field in an Ajax form.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Value
 * @since 5.4
 */

import Field from "./Field";
import { FormBuilderData } from "../Data";

export default class Button extends Field {
  protected _getData(): FormBuilderData {
    const data = {};

    if (this._field!.dataset.isClicked === "1") {
      data[this._fieldId] = (this._field! as HTMLInputElement).value;
    }

    return data;
  }
}
