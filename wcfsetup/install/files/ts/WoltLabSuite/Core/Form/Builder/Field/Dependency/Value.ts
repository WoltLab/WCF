/**
 * Form field dependency implementation that requires a field to have a certain value.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Dependency/Value
 * @see module:WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract
 * @since 5.2
 */

import Abstract from "./Abstract";
import * as DependencyManager from "./Manager";
import * as Core from "../../../../Core";

class Value extends Abstract {
  protected _isNegated = false;
  protected _values?: string[];

  checkDependency(): boolean {
    if (!this._values) {
      throw new Error("Values have not been set.");
    }

    let values: string[] = [];
    if (this._field) {
      if (DependencyManager.isHiddenByDependencies(this._field)) {
        return false;
      }

      values.push((this._field as HTMLInputElement).value);
    } else {
      let hasCheckedField = true;
      this._fields.forEach((field: HTMLInputElement) => {
        if (field.checked) {
          if (DependencyManager.isHiddenByDependencies(field)) {
            hasCheckedField = false;
            return false;
          }

          values.push(field.value);
        }
      });

      if (!hasCheckedField) {
        return false;
      }
    }

    let foundMatch = false;
    this._values.forEach((value) => {
      values.forEach((selectedValue) => {
        if (value == selectedValue) {
          foundMatch = true;
        }
      });
    });

    if (foundMatch) {
      return !this._isNegated;
    }

    return this._isNegated;
  }

  /**
   * Sets if the field value may not have any of the set values.
   */
  negate(negate: boolean): Value {
    this._isNegated = negate;

    return this;
  }

  /**
   * Sets the possible values the field may have for the dependency to be met.
   */
  values(values: string[]): Value {
    this._values = values;

    return this;
  }
}

Core.enableLegacyInheritance(Value);

export = Value;
