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
define(["require", "exports", "tslib", "./Abstract", "./Manager", "../../../../Core"], function (require, exports, tslib_1, Abstract_1, Manager_1, Core) {
    "use strict";
    Abstract_1 = tslib_1.__importDefault(Abstract_1);
    Manager_1 = tslib_1.__importDefault(Manager_1);
    Core = tslib_1.__importStar(Core);
    class IsNotClicked extends Abstract_1.default {
        constructor(dependentElementId, fieldId) {
            super(dependentElementId, fieldId);
            // To check for clicks after they occured, set `isClicked` in the field's data set and then
            // explicitly check the dependencies as the dependency manager itself does to listen to click
            // events.
            this._field.addEventListener("click", () => {
                this._field.dataset.isClicked = "1";
                Manager_1.default.checkDependencies();
            });
        }
        checkDependency() {
            return this._field.dataset.isClicked !== "1";
        }
    }
    Core.enableLegacyInheritance(IsNotClicked);
    return IsNotClicked;
});
