/**
 * Data handler for a form builder field in an Ajax form that stores its value in an input's value
 * attribute.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Value
 * @since 5.2
 */

import Field from "./Field";
import { FormBuilderData } from "../Data";
import * as Core from "../../../Core";

class Value extends Field {
  protected _getData(): FormBuilderData {
    return {
      [this._fieldId]: (this._field as HTMLInputElement).value,
    };
  }
}

Core.enableLegacyInheritance(Value);

export = Value;
