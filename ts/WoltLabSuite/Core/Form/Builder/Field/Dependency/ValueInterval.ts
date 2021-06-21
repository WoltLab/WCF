/**
 * Form field dependency implementation that requires the value of a field to be in the interval
 * [minimum, maximum].
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Dependency/ValueInterval
 * @see module:WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract
 * @since 5.5
 */

import Abstract from "./Abstract";
import * as DependencyManager from "./Manager";

export class ValueInterval extends Abstract {
  protected _maximum: number | null = null;
  protected _minimum: number | null = null;

  checkDependency(): boolean {
    if (this._field) {
      if (DependencyManager.isHiddenByDependencies(this._field)) {
        return false;
      }

      const value = parseFloat((this._field as HTMLInputElement).value);
      if (isNaN(value)) {
        return false;
      }

      if (this._minimum !== null && this._minimum > value) {
        return false;
      } else if (this._maximum !== null && this._maximum < value) {
        return false;
      }

      return true;
    } else {
      throw new Error("'ValueInterval' is only supported for individual fields.");
    }
  }

  /**
   * Sets the maximum value of the value interval or unsets the maximum value if `null` is given.
   */
  maximum(maximum: number | null): ValueInterval {
    this._maximum = maximum;

    return this;
  }

  /**
   * Sets the minimum value of the value interval or unsets the minimum value if `null` is given.
   */
  minimum(minimum: number | null): ValueInterval {
    this._minimum = minimum;

    return this;
  }
}

export default ValueInterval;
