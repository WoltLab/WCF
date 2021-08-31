/**
 * Manages the packages entered in a devtools project required package form field.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Acp/Builder/Field/Devtools/Project/RequiredPackages
 * @see module:WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/AbstractPackageList
 * @since 5.2
 */

import AbstractPackageList from "./AbstractPackageList";
import * as Core from "../../../../../../Core";
import * as Language from "../../../../../../Language";
import { RequiredPackageData } from "./Data";

class RequiredPackages<
  TPackageData extends RequiredPackageData = RequiredPackageData,
> extends AbstractPackageList<TPackageData> {
  protected readonly file: HTMLInputElement;
  protected readonly minVersion: HTMLInputElement;

  constructor(formFieldId: string, existingPackages: TPackageData[]) {
    super(formFieldId, existingPackages);

    this.minVersion = document.getElementById(`${this.formFieldId}_minVersion`) as HTMLInputElement;
    if (this.minVersion === null) {
      throw new Error(`Cannot find minimum version form field for packages field with id '${this.formFieldId}'.`);
    }
    this.minVersion.addEventListener("keypress", (ev) => this.keyPress(ev));

    this.file = document.getElementById(`${this.formFieldId}_file`) as HTMLInputElement;
    if (this.file === null) {
      throw new Error(`Cannot find file form field for required field with id '${this.formFieldId}'.`);
    }
  }

  protected createSubmitFields(listElement: HTMLLIElement, index: number): void {
    super.createSubmitFields(listElement, index);

    ["minVersion", "file"].forEach((property) => {
      const element = document.createElement("input");
      element.type = "hidden";
      element.name = `${this.formFieldId}[${index}][${property}]`;
      element.value = listElement.dataset[property]!;
      this.form.appendChild(element);
    });
  }

  protected emptyInput(): void {
    super.emptyInput();

    this.minVersion.value = "";
    this.file.checked = false;
  }

  protected getInputData(): TPackageData {
    return Core.extend(super.getInputData(), {
      file: this.file.checked,
      minVersion: this.minVersion.value,
    }) as TPackageData;
  }

  protected populateListItem(listItem: HTMLLIElement, packageData: TPackageData): void {
    super.populateListItem(listItem, packageData);

    listItem.dataset.minVersion = packageData.minVersion;
    listItem.dataset.file = packageData.file ? "1" : "0";

    listItem.innerHTML = ` ${Language.get("wcf.acp.devtools.project.requiredPackage.requiredPackage", {
      file: packageData.file,
      minVersion: packageData.minVersion,
      packageIdentifier: packageData.packageIdentifier,
    })}`;
  }

  protected validateInput(): boolean {
    return super.validateInput() && this.validateVersion(this.minVersion);
  }
}

Core.enableLegacyInheritance(RequiredPackages);

export = RequiredPackages;
