/**
 * Manages the packages entered in a devtools project suggested package form field.
 *
 * @author  Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @see module:WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/AbstractPackageList
 * @since 6.3
 */

import AbstractPackageList from "./AbstractPackageList";
import { PackageData } from "./Data";

export class SuggestedPackages extends AbstractPackageList {
  protected populateListItem(listItem: HTMLLIElement, packageData: PackageData): void {
    super.populateListItem(listItem, packageData);

    listItem.textContent = ` ${packageData.packageIdentifier}`;
  }
}
