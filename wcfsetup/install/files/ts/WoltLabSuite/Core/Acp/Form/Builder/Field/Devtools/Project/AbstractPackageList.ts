/**
 * Abstract implementation of the JavaScript component of a form field handling a list of packages.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/AbstractPackageList
 * @since 5.2
 */

import * as Core from "../../../../../../Core";
import * as Language from "../../../../../../Language";
import * as DomTraverse from "../../../../../../Dom/Traverse";
import DomChangeListener from "../../../../../../Dom/Change/Listener";
import DomUtil from "../../../../../../Dom/Util";
import { PackageData } from "./Data";

abstract class AbstractPackageList<TPackageData extends PackageData = PackageData> {
  protected readonly addButton: HTMLAnchorElement;
  protected readonly form: HTMLFormElement;
  protected readonly formFieldId: string;
  protected readonly packageList: HTMLOListElement;
  protected readonly packageIdentifier: HTMLInputElement;

  // see `wcf\data\package\Package::isValidPackageName()`
  protected static packageIdentifierRegExp = new RegExp(/^[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/);

  // see `wcf\data\package\Package::isValidVersion()`
  protected static versionRegExp = new RegExp(
    /^([0-9]+).([0-9]+)\.([0-9]+)( (a|alpha|b|beta|d|dev|rc|pl) ([0-9]+))?$/i,
  );

  constructor(formFieldId: string, existingPackages: TPackageData[]) {
    this.formFieldId = formFieldId;

    this.packageList = document.getElementById(`${this.formFieldId}_packageList`) as HTMLOListElement;
    if (this.packageList === null) {
      throw new Error(`Cannot find package list for packages field with id '${this.formFieldId}'.`);
    }

    this.packageIdentifier = document.getElementById(`${this.formFieldId}_packageIdentifier`) as HTMLInputElement;
    if (this.packageIdentifier === null) {
      throw new Error(`Cannot find package identifier form field for packages field with id '${this.formFieldId}'.`);
    }
    this.packageIdentifier.addEventListener("keypress", (ev) => this.keyPress(ev));

    this.addButton = document.getElementById(`${this.formFieldId}_addButton`) as HTMLAnchorElement;
    if (this.addButton === null) {
      throw new Error(`Cannot find add button for packages field with id '${this.formFieldId}'.`);
    }
    this.addButton.addEventListener("click", (ev) => this.addPackage(ev));

    this.form = this.packageList.closest("form") as HTMLFormElement;
    if (this.form === null) {
      throw new Error(`Cannot find form element for packages field with id '${this.formFieldId}'.`);
    }
    this.form.addEventListener("submit", () => this.submit());

    existingPackages.forEach((data) => this.addPackageByData(data));
  }

  /**
   * Adds a package to the package list as a consequence of the given event.
   *
   * If the package data is invalid, an error message is shown and no package is added.
   */
  protected addPackage(event: Event): void {
    event.preventDefault();
    event.stopPropagation();

    // validate data
    if (!this.validateInput()) {
      return;
    }

    this.addPackageByData(this.getInputData());

    // empty fields
    this.emptyInput();

    this.packageIdentifier.focus();
  }

  /**
   * Adds a package to the package list using the given package data.
   */
  protected addPackageByData(packageData: TPackageData): void {
    // add package to list
    const listItem = document.createElement("li");
    this.populateListItem(listItem, packageData);

    // add delete button
    const deleteButton = document.createElement("span");
    deleteButton.className = "icon icon16 fa-times pointer jsTooltip";
    deleteButton.title = Language.get("wcf.global.button.delete");
    deleteButton.addEventListener("click", (ev) => this.removePackage(ev));
    listItem.insertAdjacentElement("afterbegin", deleteButton);

    this.packageList.appendChild(listItem);

    DomChangeListener.trigger();
  }

  /**
   * Creates the hidden fields when the form is submitted.
   */
  protected createSubmitFields(listElement: HTMLLIElement, index: number): void {
    const packageIdentifier = document.createElement("input");
    packageIdentifier.type = "hidden";
    packageIdentifier.name = `${this.formFieldId}[${index}][packageIdentifier]`;
    packageIdentifier.value = listElement.dataset.packageIdentifier!;
    this.form.appendChild(packageIdentifier);
  }

  /**
   * Empties the input fields.
   */
  protected emptyInput(): void {
    this.packageIdentifier.value = "";
  }

  /**
   * Returns the current data of the input fields to add a new package.
   */
  protected getInputData(): TPackageData {
    return {
      packageIdentifier: this.packageIdentifier.value,
    } as TPackageData;
  }

  /**
   * Adds a package to the package list after pressing ENTER in a text field.
   */
  protected keyPress(event: KeyboardEvent): void {
    if (event.key === "Enter") {
      this.addPackage(event);
    }
  }

  /**
   * Adds all necessary package-relavant data to the given list item.
   */
  protected populateListItem(listItem: HTMLLIElement, packageData: TPackageData): void {
    listItem.dataset.packageIdentifier = packageData.packageIdentifier;
  }

  /**
   * Removes a package by clicking on its delete button.
   */
  protected removePackage(event: Event): void {
    (event.currentTarget as HTMLElement).closest("li")!.remove();

    // remove field errors if the last package has been deleted
    DomUtil.innerError(this.packageList, "");
  }

  /**
   * Adds all necessary (hidden) form fields to the form when submitting the form.
   */
  protected submit(): void {
    DomTraverse.childrenByTag(this.packageList, "LI").forEach((listItem, index) =>
      this.createSubmitFields(listItem, index),
    );
  }

  /**
   * Returns `true` if the currently entered package data is valid. Otherwise `false` is returned and relevant error
   * messages are shown.
   */
  protected validateInput(): boolean {
    return this.validatePackageIdentifier();
  }

  /**
   * Returns `true` if the currently entered package identifier is valid. Otherwise `false` is returned and an error
   * message is shown.
   */
  protected validatePackageIdentifier(): boolean {
    const packageIdentifier = this.packageIdentifier.value;

    if (packageIdentifier === "") {
      DomUtil.innerError(this.packageIdentifier, Language.get("wcf.global.form.error.empty"));

      return false;
    }

    if (packageIdentifier.length < 3) {
      DomUtil.innerError(
        this.packageIdentifier,
        Language.get("wcf.acp.devtools.project.packageIdentifier.error.minimumLength"),
      );

      return false;
    } else if (packageIdentifier.length > 191) {
      DomUtil.innerError(
        this.packageIdentifier,
        Language.get("wcf.acp.devtools.project.packageIdentifier.error.maximumLength"),
      );

      return false;
    }

    if (!AbstractPackageList.packageIdentifierRegExp.test(packageIdentifier)) {
      DomUtil.innerError(
        this.packageIdentifier,
        Language.get("wcf.acp.devtools.project.packageIdentifier.error.format"),
      );

      return false;
    }

    // check if package has already been added
    const duplicate = DomTraverse.childrenByTag(this.packageList, "LI").some(
      (listItem) => listItem.dataset.packageIdentifier === packageIdentifier,
    );

    if (duplicate) {
      DomUtil.innerError(
        this.packageIdentifier,
        Language.get("wcf.acp.devtools.project.packageIdentifier.error.duplicate"),
      );

      return false;
    }

    // remove outdated errors
    DomUtil.innerError(this.packageIdentifier, "");

    return true;
  }

  /**
   * Returns `true` if the given version is valid. Otherwise `false` is returned and an error message is shown.
   */
  protected validateVersion(versionElement: HTMLInputElement): boolean {
    const version = versionElement.value;

    // see `wcf\data\package\Package::isValidVersion()`
    // the version is no a required attribute
    if (version !== "") {
      if (version.length > 255) {
        DomUtil.innerError(versionElement, Language.get("wcf.acp.devtools.project.packageVersion.error.maximumLength"));

        return false;
      }

      if (!AbstractPackageList.versionRegExp.test(version)) {
        DomUtil.innerError(versionElement, Language.get("wcf.acp.devtools.project.packageVersion.error.format"));

        return false;
      }
    }

    // remove outdated errors
    DomUtil.innerError(versionElement, "");

    return true;
  }
}

Core.enableLegacyInheritance(AbstractPackageList);

export = AbstractPackageList;
