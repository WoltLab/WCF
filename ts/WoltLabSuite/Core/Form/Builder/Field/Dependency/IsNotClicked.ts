/**
 * Form field dependency implementation that requires that a button has not been clicked.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Dependency/IsNotClicked
 * @see module:WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract
 * @since 5.4
 */

import Abstract from "./Abstract";
import * as DependencyManager from "./Manager";

export class IsNotClicked extends Abstract {
  constructor(dependentElementId: string, fieldId: string) {
    super(dependentElementId, fieldId);

    // To check for clicks after they occurred, set `isClicked` in the field's data set and then
    // explicitly check the dependencies as the dependency manager itself does to listen to click
    // events.
    this._field.addEventListener("click", () => {
      this._field.dataset.isClicked = "1";

      DependencyManager.checkDependencies();
    });
  }

  checkDependency(): boolean {
    return this._field.dataset.isClicked !== "1";
  }
}

export default IsNotClicked;
