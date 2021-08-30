/**
 * Manages the packages entered in a devtools project excluded package form field.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/ExcludedPackages
 * @see module:WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/AbstractPackageList
 * @since 5.2
 */

import AbstractPackageList from "./AbstractPackageList";
import * as Core from "../../../../../../Core";
import * as Language from "../../../../../../Language";
import { ExcludedPackageData } from "./Data";
import DomUtil from "../../../../../../Dom/Util";

class ExcludedPackages<
  TPackageData extends ExcludedPackageData = ExcludedPackageData,
> extends AbstractPackageList<TPackageData> {
  protected readonly version: HTMLInputElement;

  constructor(formFieldId: string, existingPackages: TPackageData[]) {
    super(formFieldId, existingPackages);

    this.version = document.getElementById(`${this.formFieldId}_version`) as HTMLInputElement;
    if (this.version === null) {
      throw new Error(`Cannot find version form field for packages field with id '${this.formFieldId}'.`);
    }
    this.version.addEventListener("keypress", (ev) => this.keyPress(ev));
  }

  protected createSubmitFields(listElement: HTMLLIElement, index: number): void {
    super.createSubmitFields(listElement, index);

    const version = document.createElement("input");
    version.type = "hidden";
    version.name = `${this.formFieldId}[${index}][version]`;
    version.value = listElement.dataset.version!;
    this.form.appendChild(version);
  }

  protected emptyInput(): void {
    super.emptyInput();

    this.version.value = "";
  }

  protected getInputData(): TPackageData {
    return Core.extend(super.getInputData(), {
      version: this.version.value,
    }) as TPackageData;
  }

  protected populateListItem(listItem: HTMLLIElement, packageData: TPackageData): void {
    super.populateListItem(listItem, packageData);

    listItem.dataset.version = packageData.version;

    listItem.innerHTML = ` ${Language.get("wcf.acp.devtools.project.excludedPackage.excludedPackage", {
      packageIdentifier: packageData.packageIdentifier,
      version: packageData.version,
    })}`;
  }

  protected validateInput(): boolean {
    return super.validateInput() && this.validateVersion(this.version);
  }

  protected validateVersion(versionElement: HTMLInputElement): boolean {
    const version = versionElement.value;

    if (version === "") {
      DomUtil.innerError(versionElement, Language.get("wcf.global.form.error.empty"));

      return false;
    } else if (version !== "*") {
      return super.validateVersion(versionElement);
    }

    return true;
  }
}

Core.enableLegacyInheritance(ExcludedPackages);

export = ExcludedPackages;
