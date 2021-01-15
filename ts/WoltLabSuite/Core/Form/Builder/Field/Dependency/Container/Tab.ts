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

import Abstract from "./Abstract";
import * as DependencyManager from "../Manager";
import * as DomUtil from "../../../../../Dom/Util";
import * as UiTabMenu from "../../../../../Ui/TabMenu";
import * as Core from "../../../../../Core";

class Tab extends Abstract {
  public checkContainer(): void {
    // only consider containers that have not been hidden by their own dependencies
    if (DependencyManager.isHiddenByDependencies(this._container)) {
      return;
    }

    const containerIsVisible = !DomUtil.isHidden(this._container);
    const containerShouldBeVisible = Array.from(this._container.children).some(
      (child: HTMLElement) => !DomUtil.isHidden(child),
    );

    if (containerIsVisible !== containerShouldBeVisible) {
      const tabMenuListItem = this._container.parentNode!.parentNode!.querySelector(
        "#" +
          DomUtil.identify(this._container.parentNode! as HTMLElement) +
          " > nav > ul > li[data-name=" +
          this._container.id +
          "]",
      )! as HTMLElement;
      if (tabMenuListItem === null) {
        throw new Error("Cannot find tab menu entry for tab '" + this._container.id + "'.");
      }

      if (containerShouldBeVisible) {
        DomUtil.show(this._container);
        DomUtil.show(tabMenuListItem);
      } else {
        DomUtil.hide(this._container);
        DomUtil.hide(tabMenuListItem);

        const tabMenu = UiTabMenu.getTabMenu(
          DomUtil.identify(tabMenuListItem.closest(".tabMenuContainer") as HTMLElement),
        )!;

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

export = Tab;
