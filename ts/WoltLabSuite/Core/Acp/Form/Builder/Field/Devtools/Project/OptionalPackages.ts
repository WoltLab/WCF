/**
 * Manages the packages entered in a devtools project optional package form field.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @see module:WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/AbstractPackageList
 * @since 5.2
 */

import AbstractPackageList from "./AbstractPackageList";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { PackageData } from "./Data";

class OptionalPackages extends AbstractPackageList {
  protected populateListItem(listItem: HTMLLIElement, packageData: PackageData): void {
    super.populateListItem(listItem, packageData);

    listItem.innerHTML = ` ${getPhrase("wcf.acp.devtools.project.optionalPackage.optionalPackage", {
      packageIdentifier: packageData.packageIdentifier,
    })}`;
  }
}

export = OptionalPackages;
