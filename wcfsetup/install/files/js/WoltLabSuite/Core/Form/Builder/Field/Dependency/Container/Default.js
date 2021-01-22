/**
 * Default implementation for a container visibility handler due to the dependencies of its
 * children that only considers the visibility of all of its children.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Dependency/Container/Default
 * @see module:WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract
 * @since 5.2
 */
define(["require", "exports", "tslib", "./Abstract", "../../../../../Core", "../Manager", "../../../../../Dom/Util"], function (require, exports, tslib_1, Abstract_1, Core, DependencyManager, Util_1) {
    "use strict";
    Abstract_1 = tslib_1.__importDefault(Abstract_1);
    Core = tslib_1.__importStar(Core);
    DependencyManager = tslib_1.__importStar(DependencyManager);
    Util_1 = tslib_1.__importDefault(Util_1);
    class Default extends Abstract_1.default {
        checkContainer() {
            if (Core.stringToBool(this._container.dataset.ignoreDependencies || "")) {
                return;
            }
            // only consider containers that have not been hidden by their own dependencies
            if (DependencyManager.isHiddenByDependencies(this._container)) {
                return;
            }
            const containerIsVisible = !Util_1.default.isHidden(this._container);
            const containerShouldBeVisible = Array.from(this._container.children).some((child, index) => {
                // ignore container header for visibility considerations
                if (index === 0 && (child.tagName === "H2" || child.tagName === "HEADER")) {
                    return false;
                }
                return !Util_1.default.isHidden(child);
            });
            if (containerIsVisible !== containerShouldBeVisible) {
                if (containerShouldBeVisible) {
                    Util_1.default.show(this._container);
                }
                else {
                    Util_1.default.hide(this._container);
                }
                // check containers again to make sure parent containers can react to
                // changing the visibility of this container
                DependencyManager.checkContainers();
            }
        }
    }
    Core.enableLegacyInheritance(Default);
    return Default;
});
