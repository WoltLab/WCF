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

import Abstract from "./Abstract";
import DependencyManager from "../Manager";
import * as DomUtil from "../../../../../Dom/Util";
import * as UiTabMenu from "../../../../../Ui/TabMenu";
import * as Core from "../../../../../Core";

class TabMenu extends Abstract {
  public checkContainer(): void {
    // only consider containers that have not been hidden by their own dependencies
    if (DependencyManager.isHiddenByDependencies(this._container)) {
      return;
    }

    const containerIsVisible = this._container.style.display !== "none";
    const listItems = this._container.parentNode!.querySelectorAll(
      "#" + DomUtil.identify(this._container) + " > nav > ul > li",
    );
    const containerShouldBeVisible = Array.from(listItems).some((child: HTMLElement) => {
      return child.style.display !== "none";
    });

    if (containerIsVisible !== containerShouldBeVisible) {
      if (containerShouldBeVisible) {
        this._container.style.display = "block";

        UiTabMenu.getTabMenu(DomUtil.identify(this._container))!.selectFirstVisible();
      } else {
        this._container.style.display = "none";
      }

      // check containers again to make sure parent containers can react to
      // changing the visibility of this container
      DependencyManager.checkContainers();
    }
  }
}

Core.enableLegacyInheritance(TabMenu);

export = TabMenu;
