/**
 * Data handler for an i18n form builder field in an Ajax form that stores its value in an input's
 * value attribute.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/ValueI18n
 * @since 5.2
 */

import Field from "./Field";
import { FormBuilderData } from "../Data";
import * as LanguageInput from "../../../Language/Input";
import * as Core from "../../../Core";

class ValueI18n extends Field {
  protected _getData(): FormBuilderData {
    const data = {};

    const values = LanguageInput.getValues(this._fieldId);
    if (values.size > 1) {
      values.forEach((value, key) => {
        data[this._fieldId + "_i18n"][key] = value;
      });
    } else {
      data[this._fieldId] = values.get(0);
    }

    return data;
  }

  destroy(): void {
    LanguageInput.unregister(this._fieldId);
  }
}

Core.enableLegacyInheritance(ValueI18n);

export = ValueI18n;
