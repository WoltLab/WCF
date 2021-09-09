/**
 * Container visibility handler implementation for a tab menu tab that, in addition to the
 * tab itself, also handles the visibility of the tab menu list item.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Dependency/Container/Tab
 * @see module:WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract
 * @since 5.2
 */
define(["require", "exports", "tslib", "./Abstract", "../Manager", "../../../../../Dom/Util", "../../../../../Ui/TabMenu", "../../../../../Core"], function (require, exports, tslib_1, Abstract_1, DependencyManager, DomUtil, UiTabMenu, Core) {
    "use strict";
    Abstract_1 = (0, tslib_1.__importDefault)(Abstract_1);
    DependencyManager = (0, tslib_1.__importStar)(DependencyManager);
    DomUtil = (0, tslib_1.__importStar)(DomUtil);
    UiTabMenu = (0, tslib_1.__importStar)(UiTabMenu);
    Core = (0, tslib_1.__importStar)(Core);
    class Tab extends Abstract_1.default {
        checkContainer() {
            // only consider containers that have not been hidden by their own dependencies
            if (DependencyManager.isHiddenByDependencies(this._container)) {
                return;
            }
            const containerIsVisible = !DomUtil.isHidden(this._container);
            const containerShouldBeVisible = Array.from(this._container.children).some((child) => !DomUtil.isHidden(child));
            if (containerIsVisible !== containerShouldBeVisible) {
                const tabMenuListItem = this._container.parentNode.parentNode.querySelector("#" +
                    DomUtil.identify(this._container.parentNode) +
                    " > nav > ul > li[data-name=" +
                    this._container.id +
                    "]");
                if (tabMenuListItem === null) {
                    throw new Error("Cannot find tab menu entry for tab '" + this._container.id + "'.");
                }
                if (containerShouldBeVisible) {
                    DomUtil.show(this._container);
                    DomUtil.show(tabMenuListItem);
                }
                else {
                    DomUtil.hide(this._container);
                    DomUtil.hide(tabMenuListItem);
                    const tabMenu = UiTabMenu.getTabMenu(DomUtil.identify(tabMenuListItem.closest(".tabMenuContainer")));
                    // check if currently active tab will be hidden
                    if (tabMenu.getActiveTab() === tabMenuListItem) {
                        tabMenu.selectFirstVisible();
                    }
                }
                // Check containers again to make sure parent containers can react to changing the visibility
                // of this container.
                DependencyManager.checkContainers();
            }
        }
    }
    Core.enableLegacyInheritance(Tab);
    return Tab;
});
