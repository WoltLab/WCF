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

import Abstract from "./Abstract";
import * as Core from "../../../../../Core";
import * as DependencyManager from "../Manager";
import DomUtil from "../../../../../Dom/Util";

class Default extends Abstract {
  public checkContainer(): void {
    if (Core.stringToBool(this._container.dataset.ignoreDependencies || "")) {
      return;
    }

    // only consider containers that have not been hidden by their own dependencies
    if (DependencyManager.isHiddenByDependencies(this._container)) {
      return;
    }

    const containerIsVisible = !DomUtil.isHidden(this._container);
    const containerShouldBeVisible = Array.from(this._container.children).some((child: HTMLElement, index) => {
      // ignore container header for visibility considerations
      if (index === 0 && (child.tagName === "H2" || child.tagName === "HEADER")) {
        return false;
      }

      return !DomUtil.isHidden(child);
    });

    if (containerIsVisible !== containerShouldBeVisible) {
      if (containerShouldBeVisible) {
        DomUtil.show(this._container);
      } else {
        DomUtil.hide(this._container);
      }

      // check containers again to make sure parent containers can react to
      // changing the visibility of this container
      DependencyManager.checkContainers();
    }
  }
}

Core.enableLegacyInheritance(Default);

export = Default;
