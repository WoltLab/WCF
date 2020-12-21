/**
 * Container visibility handler implementation for a tab menu that checks visibility
 * based on the visibility of its tab menu list items.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Dependency/Container/TabMenu
 * @see module:WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract
 * @since	5.2
 */
define(["require", "exports", "tslib", "./Abstract", "../Manager", "../../../../../Dom/Util", "../../../../../Ui/TabMenu", "../../../../../Core"], function (require, exports, tslib_1, Abstract_1, DependencyManager, DomUtil, UiTabMenu, Core) {
    "use strict";
    Abstract_1 = tslib_1.__importDefault(Abstract_1);
    DependencyManager = tslib_1.__importStar(DependencyManager);
    DomUtil = tslib_1.__importStar(DomUtil);
    UiTabMenu = tslib_1.__importStar(UiTabMenu);
    Core = tslib_1.__importStar(Core);
    class TabMenu extends Abstract_1.default {
        checkContainer() {
            // only consider containers that have not been hidden by their own dependencies
            if (DependencyManager.isHiddenByDependencies(this._container)) {
                return;
            }
            const containerIsVisible = this._container.style.display !== "none";
            const listItems = this._container.parentNode.querySelectorAll("#" + DomUtil.identify(this._container) + " > nav > ul > li");
            const containerShouldBeVisible = Array.from(listItems).some((child) => {
                return child.style.display !== "none";
            });
            if (containerIsVisible !== containerShouldBeVisible) {
                if (containerShouldBeVisible) {
                    this._container.style.display = "block";
                    UiTabMenu.getTabMenu(DomUtil.identify(this._container)).selectFirstVisible();
                }
                else {
                    this._container.style.display = "none";
                }
                // check containers again to make sure parent containers can react to
                // changing the visibility of this container
                DependencyManager.checkContainers();
            }
        }
    }
    Core.enableLegacyInheritance(TabMenu);
    return TabMenu;
});
