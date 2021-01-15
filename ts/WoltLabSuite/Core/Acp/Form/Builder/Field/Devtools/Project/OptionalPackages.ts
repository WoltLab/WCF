/**
 * Manages the packages entered in a devtools project optional package form field.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/OptionalPackages
 * @see module:WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/AbstractPackageList
 * @since 5.2
 */

import AbstractPackageList from "./AbstractPackageList";
import * as Core from "../../../../../../Core";
import * as Language from "../../../../../../Language";
import { PackageData } from "./Data";

class OptionalPackages extends AbstractPackageList {
  protected populateListItem(listItem: HTMLLIElement, packageData: PackageData): void {
    super.populateListItem(listItem, packageData);

    listItem.innerHTML = ` ${Language.get("wcf.acp.devtools.project.optionalPackage.optionalPackage", {
      packageIdentifier: packageData.packageIdentifier,
    })}`;
  }
}

Core.enableLegacyInheritance(OptionalPackages);

export = OptionalPackages;
